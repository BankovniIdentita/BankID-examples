using System.IdentityModel.Tokens.Jwt;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace aspnet_sign
{
    [ApiController]
    public class SignController : ControllerBase
    {
        private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager;
        private readonly SignService _signService;

        public SignController(IConfigurationManager<OpenIdConnectConfiguration> configurationManager, SignService signService)
        {
            _configurationManager = configurationManager;
            _signService = signService;
        }

        [HttpGet("/sign")]
        public async Task<IActionResult> SignExample(CancellationToken ct)
        {
            var req = new SigningRequest(null, null)
            {
                DocumentObject = new DocumentObject(new DocumentDescriptor(new FileInfo("pdf-samples/test.pdf")))
            };

            var txId = Guid.NewGuid();

            var bankIdDiscovery = await _configurationManager.GetConfigurationAsync(ct);
            
            var requestObject = await _signService.PrepareRequestObject(txId, req);
            var jwks = await _signService.DownloadJWKS(bankIdDiscovery, ct);
            var jwe = _signService.EncryptRequestObjectIntoJWE(jwks, requestObject);
            var ros = await _signService.RegisterRequestObject(jwe, bankIdDiscovery, ct);

            await _signService.UploadFiles(req, ros, ct);

            var redirectUri = await _signService.GetAuthorizeRedirectUri(ros.RequestUri, "http://localhost:3000/", ct);

            return Redirect(redirectUri);
        }

        [HttpGet("/multi-sign")]
        public async Task<IActionResult> MultiSignExample(CancellationToken ct)
        {
            var req = new SigningRequest(null, null)
            {
                DocumentObjects = new DocumentObjects(new[]
                {
                    new DocumentDescriptor(new FileInfo("pdf-samples/test.pdf")),
                    new DocumentDescriptor(new FileInfo("pdf-samples/test2.pdf")),
                }, "Smlouvy")
            };

            var txId = Guid.NewGuid();

            var bankIdDiscovery = await _configurationManager.GetConfigurationAsync(ct);
            
            var requestObject = await _signService.PrepareRequestObject(txId, req);
            var jwks = await _signService.DownloadJWKS(bankIdDiscovery, ct);
            var jwe = _signService.EncryptRequestObjectIntoJWE(jwks, requestObject);
            var ros = await _signService.RegisterRequestObject(jwe, bankIdDiscovery, ct);

            await _signService.UploadFiles(req, ros, ct);
            
            var redirectUri = await _signService.GetAuthorizeRedirectUri(ros.RequestUri, "http://localhost:3000/", ct);

            return Redirect(redirectUri);
        }
    }
}
