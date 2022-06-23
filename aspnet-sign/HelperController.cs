using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using Jose;
using Microsoft.AspNetCore.Mvc;

namespace aspnet_sign
{
    [ApiController]
    public class HelperController : ControllerBase
    {
        private readonly SignService _signService;

        public HelperController(SignService signService)
        {
            _signService = signService;
        }

        [HttpGet("/generate-certificate")]
        public IActionResult GenerateCertificate()
        {
            string secp256r1Oid = "1.2.840.10045.3.1.7";
            string subjectName = "Self-Signed-Cert-Example";

            var ecdsa = ECDsa.Create(ECCurve.CreateFromValue(secp256r1Oid));
            var certRequest = new CertificateRequest($"CN={subjectName}", ecdsa, HashAlgorithmName.SHA512);
            certRequest.CertificateExtensions.Add(new X509KeyUsageExtension(X509KeyUsageFlags.DataEncipherment, true));
            X509Certificate2 generatedCert = certRequest.CreateSelfSigned(DateTimeOffset.Now.AddDays(-1), DateTimeOffset.Now.AddYears(10));

            return File(generatedCert.Export(X509ContentType.Pfx), "application/x-pkcs12", "cert.pfx");
        }

        [HttpGet("/.well-known/jwks.json")]
        public IActionResult JWKS()
        {
            var jwks = new JwkSet(_signService.GetSigningJwkPublic());

            return Ok(jwks.ToDictionary());
        }
    }
}
