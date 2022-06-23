using System.Security.Cryptography.X509Certificates;
using aspnet_sign;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddHttpClient();

// We need this to access OIDC configuration later to extract OIDC endpoints, esp. Authorize endpoint
var discoveryUri = new Uri(new Uri(builder.Configuration["BankID:Issuer"]), "/.well-known/openid-configuration");
builder.Services.AddSingleton<IConfigurationManager<OpenIdConnectConfiguration>>(svc =>
    new ConfigurationManager<OpenIdConnectConfiguration>(
        discoveryUri.AbsoluteUri,
        new OpenIdConnectConfigurationRetriever(),
        new HttpDocumentRetriever()));

builder.Services.AddScoped<SignService>();

// We inject signing certificate from our "secure infrastructure"
// Refer to readme section about setting up your own certificate
builder.Services.AddScoped((_) => new X509Certificate2(X509Certificate2.CreateFromCertFile("cert.pfx")));

builder.Services.AddControllers();
builder.Services.AddRazorPages();

var app = builder.Build();

app.UseDeveloperExceptionPage();

app.UseRouting();

app.UseAuthentication();
app.UseAuthorization();

app.UseEndpoints(endpoints =>
{
    endpoints.MapControllers();
    endpoints.MapRazorPages();
});

app.MapRazorPages();

app.Run();
