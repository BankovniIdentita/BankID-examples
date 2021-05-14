# BankID ASP.NET Core examples

This is an example of using [BankID](https://developer.bankid.cz/) with [ASP.NET Core](https://docs.microsoft.com/en-us/aspnet/core/introduction-to-aspnet-core?view=aspnetcore-5.0) to achieve Connect (login), and Identify (userinfo and profile) using a code grant.

This example stores user session in a cookie to allow easy interop with standard ASP.NET authentication mechanisms.

- When you first requests `/` route, Correlation cookie for `state` is set and you are be redirected to BankID `/auth` EndPoint
- After successful authentication, BankID redirects to the `/callback` URL
  - This verifies Correlation cookie against `state` forwarded from BankID
  - And sets ASP.NET session cookie
- Whenever you visit `/` route again, `GetClaims` action from [AuthController](/aspnet/AuthController.cs) is called
  - This extracts `sub` claim from ASP.NET identity as well as the `access_token`
  - GetClaims action requests UserInfo and Profile EndPoints from BankID and returns them
- When you wish to logout, visit the `/logout` route
  - Session is configured to last one hour, after this time access token from BankID would have expired. Using refresh tokens is an exercise left to the reader.

## Setup

- Install [.NET Core SDK](https://dotnet.microsoft.com/download) of version 5.0 or later
- Navigate into the [aspnet](/aspnet) folder
- Run `dotnet restore` to fetch dependencies
- Run `dotnet run` to start a development server on port 3000
- Navigate to http://localhost:3000

Do note that the application is configured in [appsettings.json](/aspnet/appsettings.json) file.

If you wish to use your own client, make sure to update the `BankID` Configuration accordingly and configure `http://localhost:3000/callback` callback URL for your application in [BankID developer portal](https://developer.bankid.cz/).

## Structure

#### Startup.cs

ASP.NET applications are traditionally configured in [Startup.cs](/aspnet/Startup.cs) file. We are adding cookie and OIDC authentication and an HTTP Client.

Do note the scope configuration during OIDC setup:

```csharp
config.Scope.Clear();
config.Scope.Add("openid");
config.Scope.Add("profile.email");
config.Scope.Add("profile.addresses");
```

#### AuthController.cs

Bulk of the work happens in our controller [AuthController.cs](/aspnet/AuthController.cs) file. In here we define both our application routes (`/` and `/logout`).

Note the `[Authorize]` attribute which says that the action requires authorization

```csharp
[Authorize]
[HttpGet("/")]
public async Task<IActionResult> GetClaims(CancellationToken ct)
```

Profile EndPoint address is a non standard value for OpenID Connect discovery and so we need to refetch the discovery and extract this URL manually:

In [Startup.cs](/aspnet/Startup.cs) we register an OIDC configuration manager:

```csharp
// We need this to access OIDC configuration later to extract Profile endpoint address
var discoveryUri = new Uri(new Uri(_configuration["BankID:Issuer"]), "/.well-known/openid-configuration");
services.AddSingleton<IConfigurationManager<OpenIdConnectConfiguration>>(svc =>
    new ConfigurationManager<OpenIdConnectConfiguration>(
        discoveryUri.AbsoluteUri,
        new OpenIdConnectConfigurationRetriever(),
        new HttpDocumentRetriever()));
```

We then inject and use it in [AuthController.cs](/aspnet/AuthController.cs):

```csharp
private readonly IConfigurationManager<OpenIdConnectConfiguration> _configurationManager
...
// Refresh OIDC configuration from BankID
var config = await _configurationManager.GetConfigurationAsync(ct);
...
var profile = await FetchJSONWithAccessToken<dynamic>(accessToken, config.AdditionalData["profile_endpoint"] as string, ct);
```

This may seem complicated, but it ensures that the BankID OIDC configuration is properly cached
