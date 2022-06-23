# BankID Sign ASP.NET Core example

This is an example of using [BankID](https://developer.bankid.cz/) with [ASP.NET Core](https://docs.microsoft.com/en-us/aspnet/core/introduction-to-aspnet-core?view=aspnetcore-6.0) to achieve signing of documents using the [BankID Sign API](https://developer.bankid.cz/docs/authorization_sep).

- Example calls for Sign and Multi Sign are provided. Multi Sign allows you to sign multiple documents at once but has slightly different `structured_scope`.
- Reads PDF metadata, embeds them into request object, signs/encrypts the request object, calls ROS EP, uploads documents and handles ID Token exchange
- Contains helper endpoints for generating custom `cert.pfx` as well as transforming it to JWKS JSON
- BankID Sign API requires use of Encrypted JWT (**JWE**) as well as signing JWTs issued by us using publicly accessible JSON Web Keys (**JWK**) that are registered in [BankID developer portal](https://developer.bankid.cz/). Please refer to section about configuring your own certificate for more info.
- To get you started ASAP you can use the bundled `cert.pfx` certificate which has JWKS accessible at `https://pastebin.com/raw/5K2zeysF`
- This example handles raw underlying JWT/JWE/JWK/OIDC instead of trying to shoehorn Sign into standard ASP.NET identity and authentication. This is mainly due to `structured_scope` and Request Object handling mandated by the BankID Sign API.
- Uses Razor pages to render resulting ID Token

## Setup

- Install [.NET Core SDK](https://dotnet.microsoft.com/download) of version 6.0 or later
- Navigate into the [aspnet-sign](/aspnet-sign) folder
- Run `dotnet restore` to fetch dependencies
- Run `dotnet run` to start a development server on port 3000
- Navigate to http://localhost:3000

Do note that the application is configured in [appsettings.json](/aspnet/appsettings.json) file.

## Setting up your own credentials and certificate

- Create your own certificate
  - Either start the application and use the http://localhost:3000/generate-certificate EP go generate an elliptic curve secp256r1 certificate
  - Or generate a certificate compliant with algorithms supported by BankID available at [BankID discovery](https://oidc.sandbox.bankid.cz/.well-known/openid-configuration). You may need to tweak `SignService.EncryptRequestObjectIntoJWE` method as it assumes an EC certificate
  - Overwrite the bundled `cert.pfx` with your certificate and start the application
  - Either copy JWKS JSON from http://localhost:3000/.well-known/jwks.json and host it somewhere (can be just Pastebin). Do note that this is unsuitable for production deployments because you can't conveniently rotate credentials.
  - Or make http://localhost:3000/.well-known/jwks.json available from the Internet. In development this can be done using service like https://ngrok.com/
- Navigate to [BankID developer portal](https://developer.bankid.cz/) and create an application
  - Add Redirect URI `http://localhost:3000/`
  - Disable unused scopes (so you can apply changes)
  - Enable Authorization code flow
  - Set Refresh token to `optional`
  - Set JWKS URI (`https://pastebin.com/raw/5K2zeysF` if using the bundled `cert.pfx`) for your certificate
  - [Example configuration screenshot](/aspnet-sign/docs/devportal-settings.png)
  - Apply changes
- Copy ClientID and ClientSecret from BankID developer portal into [appsettings.json](/aspnet/appsettings.json)

## Structure

### Program.cs

The application is configured in [Program.cs](Program.cs) file.

The most important part here is that we are providing our certificate to dependency injection:

```csharp
builder.Services.AddScoped((_) => new X509Certificate2(X509Certificate2.CreateFromCertFile("cert.pfx")));
```

And we prepare an easy OIDC discovery for later (this enables the `await _configurationManager.GetConfigurationAsync(ct)` calls):

```csharp
var discoveryUri = new Uri(new Uri(builder.Configuration["BankID:Issuer"]), "/.well-known/openid-configuration");
builder.Services.AddSingleton<IConfigurationManager<OpenIdConnectConfiguration>>(svc =>
    new ConfigurationManager<OpenIdConnectConfiguration>(
        discoveryUri.AbsoluteUri,
        new OpenIdConnectConfigurationRetriever(),
        new HttpDocumentRetriever()));
```

### Pages/Index.cshtml and Pages/Index.cshtml.cs

Contains simple HTML page to serve as a landing page and render the final ID Token with download URLs

### SignController.cs

In here we expose our `/sign` and `/multi-sign` EndPoints and orchestrate the signing process which culminates with redirect to BankID Authorize EP. Please refer to [BankID Sign API](https://developer.bankid.cz/docs/authorization_sep) for detailed language agnostic description of this process.

Do note how we construct the Request Object helpers. This abstraction could be improved to allow for sign area specification. It loosly represents what we add into the `structured_scope` claim in Request Object.

```csharp
  var req = new SigningRequest(null, null)
  {
      DocumentObjects = new DocumentObjects(new[]
      {
          new DocumentDescriptor(new FileInfo("pdf-samples/test.pdf")),
          new DocumentDescriptor(new FileInfo("pdf-samples/test2.pdf")),
      }, "Smlouvy")
  };
```

Also do note the hardcoded `http://localhost:3000/` (`callbackUri` parameters). This must be registered in the Developer Portal and must exactly match between Authorize EP call and Token EP call.

### SignService.cs

This is where most of the actual Sign specific logic happens.

- First we construct the claims for Request Object in `GenerateDocumentClaims` and `PrepareRequestObject`. You will want to match this to your specific usecase.
- We read pdf metadata during the `documentObject` construction. We hash the document in this step as well.
- We then use mint a Request Object JWK with our claims and sign it with our certificate. This is why we needed to register the cert in Developer Portal - because BankID needs to verify this signature.
- We download BankID encryption keys with `DownloadJWKS` and encrypt the signed JWK into JWE in `EncryptRequestObjectIntoJWE`

This logic is specific to crypto algorithms that BankID currently uses and to our certificate. You will want to match the `JwsAlgorithm.ES256` and `JweAlgorithm.RSA_OAEP_256` to your scenario.

```csharp
var signKey = GetSigningKeyPrivate();
var signedToken = Jose.JWT.Encode(requestObject, signKey, JwsAlgorithm.ES256);

var encJwk = jwks.Keys.First(x => x.Use == "enc");
var encryptedToken = Jose.JWT.EncodeBytes(Encoding.ASCII.GetBytes(signedToken), encJwk, JweAlgorithm.RSA_OAEP_256, JweEncryption.A256GCM);
```

- Next we take the signed and encrypted token and upload it to ROS EP in `RegisterRequestObject`
- ROS EP gives us URLs to upload documents onto and so we do so. This is done in `UploadFile` as multipart form upload. During this step **BankID verifies metadata from `structured_scope`**, so make sure they are correct!
- After the documents have been uploaded, `request_uri` from ROS response is now valid and we can use this to redirect to Authorize EP to continue using standard OIDC code flow.
