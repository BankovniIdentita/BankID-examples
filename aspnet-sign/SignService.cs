using System.Net.Http.Headers;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Text.Json.Serialization;
using IronPdf;
using Jose;
using Microsoft.AspNetCore.WebUtilities;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace aspnet_sign;

public class SignService
{
    private readonly IHttpClientFactory _httpClientFactory;
    private readonly IConfiguration _configuration;
    private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager;
    private readonly X509Certificate2 _cert;
    private readonly ILogger<SignService> _logger;

    public SignService(IHttpClientFactory httpClientFactory, IConfiguration configuration,
        IConfigurationManager<OpenIdConnectConfiguration> configurationManager,
        X509Certificate2 cert, ILogger<SignService> logger)
    {
        _httpClientFactory = httpClientFactory;
        _configuration = configuration;
        _configurationManager = configurationManager;
        _cert = cert;
        _logger = logger;
    }

    private async Task<IDictionary<string, object>> PrepareDocumentClaims(DocumentDescriptor document, int priority)
    {
        using var sha = SHA256Managed.Create();
        await using var ms = new MemoryStream(document.Contents);

        var hash = await sha.ComputeHashAsync(ms);

        return new Dictionary<string, object>
        {
            { "priority", priority },
            { "document_id", document.Id },
            { "document_hash", BitConverter.ToString(hash).Replace("-", "").ToLower() },
            { "hash_alg", "2.16.840.1.101.3.4.2.1" }, // SHA256 https://oidref.com/2.16.840.1.101.3.4.2.1
            { "document_title", document.Title },
            { "document_subject", document.Subject },
            { "document_language", "cs.CZ" },
            { "document_created", document.CreationTime },
            { "document_author", document.Author },
            { "document_pages", document.Pages },
            { "document_size", document.Contents.Length },
            { "document_read_by_enduser", true },
            {
                "sign_area", new Dictionary<string, object>
                {
                    { "page", 0 },
                    { "x-coordinate", 350 },
                    { "y-coordinate", 150 },
                    { "x-dist", 140 },
                    { "y-dist", 50 }
                }
            }
        };
    }

    public async Task<IDictionary<string, object>> PrepareRequestObject(Guid txId, SigningRequest request)
    {
        var structuredScopeClaims = new Dictionary<string, object>
        {
            {
                "signObject", new Dictionary<string, object>
                {
                    { "fields", new List<object>() }
                }
            }
        };

        if (request.DocumentObject != null)
        {
            structuredScopeClaims["documentObject"] = await PrepareDocumentClaims(request.DocumentObject.Document, 0);
        }

        if (request.DocumentObjects != null)
        {
            structuredScopeClaims["documentObjects"] = new Dictionary<string, object>
            {
                { "envelope_name", request.DocumentObjects.EnvelopeName },
                { "documents", await Task.WhenAll(request.DocumentObjects.Documents.Select(async (x, i) => await PrepareDocumentClaims(x, i + 1))) }
            };
        }

        return new Dictionary<string, object>
        {
            { "txn", txId.ToString() },
            { "client_id", _configuration["BankID:ClientID"] },
            { "nonce", txId.ToString() },
            { "state", txId.ToString() },
            { "max_age", 3600 },
            { "response_type", "code" },
            { "scope", "openid offline_access" },
            { "structured_scope", structuredScopeClaims },
        };
    }

    public async Task<JwkSet> DownloadJWKS(OpenIdConnectConfiguration config, CancellationToken ct = default)
    {
        _logger.LogDebug("Downloading JWKS from {JwksUri}", config.JwksUri);

        using var client = _httpClientFactory.CreateClient();
        var resp = await client.GetStringAsync(config.JwksUri, ct);

        return JwkSet.FromJson(resp, new JsonMapper());
    }

    internal Jwk GetSigningJwkPublic()
    {
        Jwk key = new Jwk(_cert.GetECDsaPublicKey(), isPrivate: false);
        key.KeyId = "key1";

        return key;
    }

    internal ECDsa GetSigningKeyPrivate()
    {
        return _cert.GetECDsaPrivateKey();
    }

    /// <summary>
    /// Transforms request object into encrypted JWT
    /// </summary>
    /// <param name="jwks">BankID encryption keys, get those from DownloadJWKS</param>
    /// <param name="requestObject">Request object claims, get these from PrepareRequestObject</param>
    /// <returns>JWE request object</returns>
    public string EncryptRequestObjectIntoJWE(JwkSet jwks, IDictionary<string, object> requestObject)
    {
        _logger.LogInformation("Creating JWE from request object");

        var signKey = GetSigningKeyPrivate();
        string signedToken = Jose.JWT.Encode(requestObject, signKey, JwsAlgorithm.ES256);

        var encJwk = jwks.Keys.First(x => x.Use == "enc");
        string encryptedToken = Jose.JWT.EncodeBytes(Encoding.ASCII.GetBytes(signedToken), encJwk, JweAlgorithm.RSA_OAEP_256, JweEncryption.A256GCM,
            extraHeaders: new
                Dictionary<string,
                    object>
                {
                    { "kid", encJwk.KeyId }
                });

        return encryptedToken;
    }

    /// <summary>
    /// Calls BankID ROS EndPoint with a request object encrypted as JWE
    /// </summary>
    /// <param name="jwe">Encrypted request object. Get this from EncryptRequestObjectIntoJWE</param>
    /// <returns>Returns ROS EndPoint response</returns>
    public async Task<RosResponse> RegisterRequestObject(string jwe, OpenIdConnectConfiguration config, CancellationToken ct = default)
    {
        var url = config.AdditionalData["ros_endpoint"].ToString();

        _logger.LogInformation("Calling ROS at {Url}", url);

        using var client = _httpClientFactory.CreateClient();

        var content = new StringContent(jwe, Encoding.Default, "application/jwe");

        var resp = await client.PostAsync(url, content, ct);
        resp.EnsureSuccessStatusCode();

        var data = await resp.Content.ReadFromJsonAsync<RosResponse>(cancellationToken: ct);

        return data;
    }

    public async Task UploadFile(Uri url, byte[] file, CancellationToken ct = default)
    {
        _logger.LogInformation("Uploading file to {Url}", url);

        using var client = _httpClientFactory.CreateClient();

        var form = new MultipartFormDataContent();
        form.Add(new ByteArrayContent(file)
        {
            Headers = { ContentType = new MediaTypeHeaderValue("application/pdf") }
        }, "file", "doc.pdf");

        var resp = await client.PostAsync(url, form, ct);
        resp.EnsureSuccessStatusCode();
    }

    public async Task UploadFiles(SigningRequest request, RosResponse ros, CancellationToken ct = default)
    {
        if (request.DocumentObject != null)
        {
            if (ros.UploadUri == null)
            {
                throw new InvalidOperationException();
            }

            await UploadFile(new Uri(ros.UploadUri), request.DocumentObject.Document.Contents, ct);
        }

        if (request.DocumentObjects != null)
        {
            if (ros.UploadUris == null)
            {
                throw new InvalidOperationException();
            }

            foreach (var doc in request.DocumentObjects.Documents)
            {
                var uri = ros.UploadUris[doc.Id];

                if (uri == null)
                {
                    throw new InvalidOperationException();
                }

                await UploadFile(new Uri(uri), doc.Contents, ct);
            }
        }
    }

    public async Task<Tokens> ExchangeCodeForTokens(string code, string originalRedirectUri, OpenIdConnectConfiguration config, CancellationToken ct = default)
    {
        using var client = _httpClientFactory.CreateClient();

        var content = new FormUrlEncodedContent(new Dictionary<string, string>
        {
            { "code", code },
            { "grant_type", "authorization_code" },
            { "client_id", _configuration["BankID:ClientID"] },
            { "client_secret", _configuration["BankID:ClientSecret"] },
            { "redirect_uri", originalRedirectUri },
        }.ToList());

        var resp = await client.PostAsync(config.TokenEndpoint, content, ct);
        resp.EnsureSuccessStatusCode();

        var data = await resp.Content.ReadFromJsonAsync<Tokens>(cancellationToken: ct);

        return data;
    }

    public async Task<string> GetAuthorizeRedirectUri(string requestUri, string callbackUri, CancellationToken ct = default)
    {
        var config = await _configurationManager.GetConfigurationAsync(ct);

        var oidcParams = new Dictionary<string, string?>
        {
            { "request_uri", requestUri },
            { "redirect_uri", callbackUri },
        };

        return QueryHelpers.AddQueryString(config.AdditionalData["authorize_endpoint"].ToString(), oidcParams);
    }
}

public class Tokens
{
    [JsonPropertyName("id_token")] public string IdToken { get; init; }

    [JsonPropertyName("access_token")] public string AccessToken { get; init; }
}

public class RosResponse
{
    [JsonPropertyName("request_uri")] public string RequestUri { get; init; }

    [JsonPropertyName("upload_uri")] public string? UploadUri { get; init; }

    [JsonPropertyName("upload_uris")] public Dictionary<string, string>? UploadUris { get; init; }

    [JsonPropertyName("exp")] public long? Exp { get; init; }
}

public class DocumentDescriptor
{
    public DocumentDescriptor(FileInfo path)
    {
        Contents = File.ReadAllBytes(path.FullName);

        var pdf = PdfDocument.FromFile(path.FullName);

        Id = pdf.MetaData.CustomProperties["documentId"];

        if (string.IsNullOrEmpty(Id))
        {
            throw new InvalidOperationException($"Unable to read documentId metadata from pdf at {path.FullName}");
        }

        Title = pdf.MetaData.Title;
        Subject = pdf.MetaData.Subject;
        CreationTime = pdf.MetaData.CreationDate;
        Author = pdf.MetaData.Author;
        Pages = pdf.PageCount;
    }

    public byte[] Contents { get; }

    public string Id { get; }

    public string Title { get; }

    public string Subject { get; }

    public string Author { get; }

    public int Pages { get;  }

    public DateTime CreationTime { get; }
}

public record DocumentObjects(DocumentDescriptor[] Documents, string EnvelopeName);

public record DocumentObject(DocumentDescriptor Document);

public record SigningRequest(DocumentObjects? DocumentObjects, DocumentObject? DocumentObject);
