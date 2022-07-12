using System.IdentityModel.Tokens.Jwt;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace aspnet_sign.Pages;

public class Index : PageModel
{
    private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager;
    private readonly SignService _signService;
    public string IdTokenJson { get; set; }

    [FromQuery(Name = "code")]
    public string Code { get; set; }

    public Index(IConfigurationManager<OpenIdConnectConfiguration> configurationManager, SignService signService)
    {
        _configurationManager = configurationManager;
        _signService = signService;
    }
    
    public async Task OnGet(CancellationToken ct)
    {
        if (!string.IsNullOrEmpty(Code))
        {
            var config = await _configurationManager.GetConfigurationAsync(ct);

            var tokens = await _signService.ExchangeCodeForTokens(Code, "http://localhost:3000/", config, ct);

            IdTokenJson = new JwtSecurityToken(tokens.IdToken).Payload.SerializeToJson();
        }
    }
}
