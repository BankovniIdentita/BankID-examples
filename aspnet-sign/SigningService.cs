using System.IdentityModel.Tokens.Jwt;
using System.Net;
using System.Net.Http.Headers;
using System.Security.Claims;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using Jose;
using Microsoft.AspNetCore.Authentication.OpenIdConnect;
using Microsoft.Extensions.Options;
using Microsoft.IdentityModel.Logging;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;
using Microsoft.IdentityModel.Tokens;

namespace aspnet_sign;

public class SigningService
{
    private readonly IHttpClientFactory _httpClientFactory;
    private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager;
    private readonly X509Certificate2 _cert;
    private readonly IOptionsMonitor<OpenIdConnectOptions> _options;

    public SigningService(IHttpClientFactory httpClientFactory, IConfigurationManager<OpenIdConnectConfiguration> configurationManager, X509Certificate2 cert, IOptionsMonitor<OpenIdConnectOptions> options)
    {
        _httpClientFactory = httpClientFactory;
        _configurationManager = configurationManager;
        _cert = cert;
        _options = options;
    }


    private async Task<IDictionary<string, object>> GenerateDocumentClaims(DocumentDescriptor document)
    {
        using var sha = SHA512Managed.Create();
        await using var ms = new MemoryStream(document.Contents);
        var id = "asdasdasd";

        var hash = await sha.ComputeHashAsync(ms);
        
        return new Dictionary<string, object>
        {
            {"document_id", id},
            {"document_hash", BitConverter.ToString(hash).Replace("-", "")},
            {"hash_alg", "2.16.840.1.101.3.4.2.3"}, // SHA512 https://oidref.com/2.16.840.1.101.3.4.2.3
            {"document_title", "Smlouva o smlouvě"},
            {"document_subject", "Smlouva s společností ACME"},
            {"document_language", "cs.CZ"},
            {"document_created", "2020-06-24T08:54:11+00:00"},
            {"document_author", "Example"},
            {"document_size", document.Contents.Length.ToString()},
            {"document_uri", $"http://localhost:3000/documents?document_id={id}"}, // Document must be available to preview on this URL
            {"document_read_by_enduser", "true"}
        };
    }
    
    private async Task<IDictionary<string, object>> GenerateClaims(Guid txId, SigningRequest request)
    {
        var structuredScoreClaims = new Dictionary<string, object>();

        if (request.DocumentObject != null)
        {
            structuredScoreClaims["documentObject"] = await GenerateDocumentClaims(request.DocumentObject.Document);
        }
        
        if (request.DocumentObjects != null)
        {
            structuredScoreClaims["documentObjects"] = new Dictionary<string, object>
            {
                {"envelope_name", request.DocumentObjects.EnvelopeName},
                {"documents", await Task.WhenAll(request.DocumentObjects.Documents.Select(async x => await GenerateDocumentClaims(x)))}
            };
        }
        
        return new Dictionary<string, object>
        {
            {"txn", txId.ToString()},
            {"client_id", _options.CurrentValue.ClientId},
            {"nonce", txId.ToString()},
            {"state", txId.ToString()},
            {"max_age", "3600"},
            {"structured_scope", structuredScoreClaims},
        };
    }
    
    private async Task<string> GenerateROSToken(Guid txId, SigningRequest request)
    {
        var claims = await GenerateClaims(txId, request);
        
        var handler = new JwtSecurityTokenHandler();
        
        var tokenDescriptor = new SecurityTokenDescriptor
        {
            Claims = claims,
            EncryptingCredentials = new X509EncryptingCredentials(_cert, "RSA-OAEP", SecurityAlgorithms.Aes256Gcm),
            // EncryptingCredentials = new X509EncryptingCredentials(_cert, "ECDH-ES", SecurityAlgorithms.Aes256Gcm),
            // SigningCredentials = new X509SigningCredentials(_cert, SecurityAlgorithms.EcdsaSha512),
        };
        
        IdentityModelEventSource.ShowPII = true;

        // string token = handler.CreateEncodedJwt(tokenDescriptor);
        string token = Jose.JWT.Encode(claims, _cert.GetRSAPrivateKey(), JweAlgorithm.RSA_OAEP, JweEncryption.A256GCM);

        return token;
    }

    private async Task<TResp> CallJWE<TResp>(Uri url, string token, CancellationToken ct = default)
    {
        using var client = _httpClientFactory.CreateClient();
        var request = new HttpRequestMessage();
        request.Headers.Add("Content-Type", "application/jwe");
        request.Method = HttpMethod.Post;
        request.RequestUri = url;
        request.Content = new StringContent(token);

        var resp = await client.SendAsync(request, ct);
        resp.EnsureSuccessStatusCode();

        var data = await resp.Content.ReadFromJsonAsync<TResp>(cancellationToken: ct);

        return data;
    }

    private async Task UploadFiles(Uri url, string property, IEnumerable<byte[]> files, CancellationToken ct = default)
    {
        using var client = _httpClientFactory.CreateClient();
        var request = new HttpRequestMessage();
        request.Method = HttpMethod.Post;
        request.RequestUri = url;

        var form = new MultipartFormDataContent();
        foreach (var file in files)
        {
            form.Add(new ByteArrayContent(file), property);
        }
        
        request.Content = form;

        var resp = await client.SendAsync(request, ct);
        resp.EnsureSuccessStatusCode();
    }
    
    public async Task RegisterSigningObject(SigningRequest request, CancellationToken ct = default)
    {
        var txId = Guid.NewGuid();
        
        var config = await _configurationManager.GetConfigurationAsync(ct);
        var token = await GenerateROSToken(txId, request);
        var rosEp = new Uri(config.AdditionalData["ros_endpoint"].ToString());

        var ros = await CallJWE<RosResponse>(rosEp, token, ct);
    }
}

public record RosResponse(string RequestUri, string UploadUri, DateTime Exp);
public record DocumentDescriptor(byte[] Contents);

public record DocumentObjects(DocumentDescriptor[] Documents, string EnvelopeName);

public record DocumentObject(DocumentDescriptor Document);

public record SigningRequest(DocumentObjects? DocumentObjects, DocumentObject? DocumentObject);
