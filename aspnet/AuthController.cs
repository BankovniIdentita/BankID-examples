using System;
using System.Linq;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Security.Claims;
using System.Threading;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace aspnet
{
    [ApiController]
    public class AuthController : ControllerBase
    {
        private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager;
        private readonly IHttpClientFactory _httpClientFactory;

        public AuthController(IConfigurationManager<OpenIdConnectConfiguration> configurationManager,
            IHttpClientFactory httpClientFactory)
        {
            _configurationManager = configurationManager;
            _httpClientFactory = httpClientFactory;
        }

        [Authorize]
        [HttpGet("/")]
        public async Task<IActionResult> GetClaims(CancellationToken ct)
        {
            if (User.Identity is not { IsAuthenticated: true }) return Unauthorized();

            var sub = User.Claims.FirstOrDefault(x => x.Type == ClaimTypes.NameIdentifier)?.Value;

            var accessToken = await HttpContext.GetTokenAsync("access_token");

            // Refresh OIDC configuration from BankID
            var config = await _configurationManager.GetConfigurationAsync(ct);

            // Fetch UserInfo and Profile
            var userinfo = await FetchJSONWithAccessToken<dynamic>(accessToken, config.UserInfoEndpoint, ct);
            var profile = await FetchJSONWithAccessToken<dynamic>(accessToken, config.AdditionalData["profile_endpoint"] as string, ct);

            return Ok(new
            {
                Sub = sub,
                UserInfo = userinfo,
                Profile = profile
            });
        }

        private async Task<TResp> FetchJSONWithAccessToken<TResp>(string accessToken, string url,
            CancellationToken ct = default)
        {
            using var client = _httpClientFactory.CreateClient();
            var request = new HttpRequestMessage();
            request.Headers.Authorization = new AuthenticationHeaderValue("bearer", accessToken);
            request.Method = HttpMethod.Get;
            request.RequestUri = new Uri(url);

            var resp = await client.SendAsync(request, ct);
            resp.EnsureSuccessStatusCode();

            var data = await resp.Content.ReadFromJsonAsync<TResp>(cancellationToken: ct);

            return data;
        }

        [Authorize]
        [HttpGet("/logout")]
        public async Task<IActionResult> Logout()
        {
            await HttpContext.SignOutAsync();

            return RedirectToAction(nameof(GetClaims));
        }
    }
}