using System;
using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.Authentication.OpenIdConnect;
using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.IdentityModel.Protocols;
using Microsoft.IdentityModel.Protocols.OpenIdConnect;

namespace aspnet
{
    public class Startup
    {
        private readonly IConfiguration _configuration;

        public Startup(IConfiguration configuration)
        {
            _configuration = configuration;
        }

        // This method gets called by the runtime. Use this method to add services to the container.
        public void ConfigureServices(IServiceCollection services)
        {
            // Add HTTP client for fetching userinfo and profile
            services.AddHttpClient();

            // We need this to access OIDC configuration later to extract Profile endpoint address
            var discoveryUri = new Uri(new Uri(_configuration["BankID:Issuer"]), "/.well-known/openid-configuration");
            services.AddSingleton<IConfigurationManager<OpenIdConnectConfiguration>>(svc =>
                new ConfigurationManager<OpenIdConnectConfiguration>(
                    discoveryUri.AbsoluteUri,
                    new OpenIdConnectConfigurationRetriever(),
                    new HttpDocumentRetriever()));

            // Configure authentication
            services
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
                    config.Authority = _configuration["BankID:Issuer"];
                    config.ClientId = _configuration["BankID:ClientID"];
                    config.ClientSecret = _configuration["BankID:ClientSecret"];

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

            services.AddControllers();
        }

        // This method gets called by the runtime. Use this method to configure the HTTP request pipeline.
        public void Configure(IApplicationBuilder app, IWebHostEnvironment env)
        {
            if (env.IsDevelopment()) app.UseDeveloperExceptionPage();

            app.UseRouting();

            app.UseAuthentication();
            app.UseAuthorization();

            app.UseEndpoints(endpoints => { endpoints.MapControllers(); });
        }
    }
}