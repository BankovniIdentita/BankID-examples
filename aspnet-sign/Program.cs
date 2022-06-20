using System.IdentityModel.Tokens.Jwt;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using aspnet_sign;
using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.Authentication.OpenIdConnect;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

var builder = WebApplication.CreateBuilder(args);

// Add HTTP client for fetching userinfo and profile
builder.Services.AddHttpClient();

// We need this to access OIDC configuration later to extract Profile endpoint address
var discoveryUri = new Uri(new Uri(builder.Configuration["BankID:Issuer"]), "/.well-known/openid-configuration");
builder.Services.AddSingleton<IConfigurationManager<OpenIdConnectConfiguration>>(svc =>
    new ConfigurationManager<OpenIdConnectConfiguration>(
        discoveryUri.AbsoluteUri,
        new OpenIdConnectConfigurationRetriever(),
        new HttpDocumentRetriever()));

// Configure authentication
builder.Services
    .AddAuthentication(options =>
    {
        options.DefaultScheme = CookieAuthenticationDefaults.AuthenticationScheme;
        options.DefaultChallengeScheme = OpenIdConnectDefaults.AuthenticationScheme;
    })
    .AddCookie(CookieAuthenticationDefaults.AuthenticationScheme,
        config => { config.ExpireTimeSpan = TimeSpan.FromHours(1); })
    .AddOpenIdConnect(OpenIdConnectDefaults.AuthenticationScheme, config =>
    {
        // Make sure to update appsettings.json to update credentials
        config.Authority = builder.Configuration["BankID:Issuer"];
        config.ClientId = builder.Configuration["BankID:ClientID"];
        config.ClientSecret = builder.Configuration["BankID:ClientSecret"];

        // Configure requested scopes
        config.Scope.Clear();
        config.Scope.Add("openid");
        config.Scope.Add("profile.email");
        config.Scope.Add("profile.addresses");

        config.CallbackPath = "/callback";
        config.SaveTokens = true;
        config.SignInScheme = CookieAuthenticationDefaults.AuthenticationScheme;
        config.ResponseType = OpenIdConnectResponseType.Code;

        config.ProtocolValidator.RequireNonce = false;

        config.CorrelationCookie = new CookieBuilder
        {
            HttpOnly = true,
            SameSite = SameSiteMode.Lax,
            SecurePolicy = CookieSecurePolicy.SameAsRequest,
            Expiration = TimeSpan.FromMinutes(10)
        };
    });

builder.Services.AddScoped<SigningService>();

// We inject private keys for JWE from our "secure infrastructure"
// Refer to readme section about JWE keys
// builder.Services.AddScoped((_) =>
// {
//     var key = ECDsa.Create(ECCurve.NamedCurves.nistP256);
//     var ecParams = new ECParameters();
//     ecParams.Curve = ECCurve.CreateFromOid(ECCurve.NamedCurves.nistP256.Oid);
//     ecParams.Q.X = Convert.FromBase64String(builder.Configuration["BankIDSigningKey:x"].Replace('-', '+').Replace('_', '/'));
//     ecParams.Q.Y = Convert.FromBase64String(builder.Configuration["BankIDSigningKey:y"].Replace('-', '+').Replace('_', '/'));
//     ecParams.D = Convert.FromBase64String(builder.Configuration["BankIDSigningKey:d"].Replace('-', '+').Replace('_', '/'));
//     key.ImportParameters(ecParams);
//
//     var certRequest = new CertificateRequest($"CN=key-1", key, HashAlgorithmName.SHA256);
//     certRequest.CertificateExtensions.Add(new X509KeyUsageExtension(X509KeyUsageFlags.DataEncipherment, true));
//     X509Certificate2 cert = certRequest.CreateSelfSigned(DateTimeOffset.Now.AddDays(-1), DateTimeOffset.Now.AddYears(10));
//
//     return cert;
// });

builder.Services.AddScoped((_) =>
{
    // string secp256r1Oid = "1.2.840.10045.3.1.7";  //oid for prime256v1(7)  other identifier: secp256r1
        
    string subjectName = "Self-Signed-Cert-Example";

    // var ecdsa = ECDsa.Create(ECCurve.CreateFromValue(secp256r1Oid));
    var rsa = RSA.Create();

    var certRequest = new CertificateRequest($"CN={subjectName}", rsa, HashAlgorithmName.SHA256, RSASignaturePadding.Pkcs1);

    //add extensions to the request (just as an example)
    //add keyUsage
    certRequest.CertificateExtensions.Add(new X509KeyUsageExtension(X509KeyUsageFlags.DataEncipherment, true));

    X509Certificate2 generatedCert = certRequest.CreateSelfSigned(DateTimeOffset.Now.AddDays(-1), DateTimeOffset.Now.AddYears(10)); // generate the cert and sign!

    X509Certificate2 pfxGeneratedCert = new X509Certificate2(generatedCert.Export(X509ContentType.Pfx)); //has to be turned into pfx or Windows at least throws a security credentials not found during sslStream.connectAsClient or HttpClient request...

    return pfxGeneratedCert;
});

builder.Services.AddControllers();

var app = builder.Build();

if (app.Environment.IsDevelopment()) app.UseDeveloperExceptionPage();

app.UseRouting();

app.UseAuthentication();
app.UseAuthorization();

app.UseEndpoints(endpoints => { endpoints.MapControllers(); });

app.Run();