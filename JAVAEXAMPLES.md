# Java Code Examples

This document contains several examples for calling and using essential BankID services. The code samples are intended
primarily for the first acquaintance on the BankID Sandbox.

> The range of examples covers the primary use of the service. The content of examples covers the immediate use of the services.   
> Samples are also available on GitHub as repositories with predefined entities at [BankID Examples](https://github.com/BankovniIdentita/BankID-examples)

**Prepared examples:**

- BankID OIDC Configuration 
- Authorization URI 
- Token exchange 
- Profile and Userinfo Call 
- BankID Products

## BankID OIDC Configuration

Example of obtaining OpenID Connect configuration data from BankID Sandbox

This configuration provides information about OpenID Connect service:

- endpoint addresses
- encryption and signing algorithms
- list of supported scopes and claims
- list of supported grants and functions

[BankIDConfiguration.java](src/main/java/cz/bankid/examples/auth/BankIDConfiguration.java)
```java
import com.nimbusds.oauth2.sdk.http.HTTPRequest;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.id.Issuer;
import com.nimbusds.openid.connect.sdk.op.OIDCProviderConfigurationRequest;
import com.nimbusds.openid.connect.sdk.op.OIDCProviderMetadata;
import java.net.URI;

...

    // The BankID Sandbox issuer uri
    String bankIDIssuerURI = "https://oidc.sandbox.bankid.cz/";

    // Create new issuer
    Issuer issuer = new Issuer(bankIDIssuerURI);
    
    // Request for configuration
    OIDCProviderConfigurationRequest request = new OIDCProviderConfigurationRequest(issuer);
    try {
        HTTPRequest httpRequest = request.toHTTPRequest();
        
        // Call for configuration
        HTTPResponse httpResponse = httpRequest.send();
        
        // Parsing BankID Configuration
        OIDCProviderMetadata opMetadata = OIDCProviderMetadata.parse(httpResponse.getContentAsJSONObject());
        
        // Obtain the auth endpoint URI
        URI auth = opMetadata.getAuthorizationEndpointURI();
        
    } catch(IOException e) {
        // TODO processing the error
    } catch(ParseException e) {
        // TODO processing the error
    }

...

```

## Example of building a BankID Login URI
The example uses values and parameters relevant to the BankID service. We recommend obtaining the URL for the correct 
auth endpoint call from the OIDC configuration endpoint BankID. We also recommend that you verify supported scopes and 
grants against the configuration.

> The exact values of the parameters should correspond to the application settings in the BankID Developer Portal.

[LoginURL.java](src/main/java/cz/bankid/examples/auth/LoginURL.java)
```java
import com.nimbusds.oauth2.sdk.ResponseType;
import com.nimbusds.oauth2.sdk.Scope;
import com.nimbusds.oauth2.sdk.id.ClientID;
import com.nimbusds.oauth2.sdk.id.State;
import com.nimbusds.openid.connect.sdk.AuthenticationRequest;
import com.nimbusds.openid.connect.sdk.Nonce;
import com.nimbusds.openid.connect.sdk.Prompt;
import java.net.URI;

...

    // Application configuration from BankID dev. portal
    String[] scopes = {"openid", "profile.titles", "profile.name", "profile.email", "offline_access"};
    ClientID clientId = new ClientID(" ... application client_id ...");
    String redirectURI = "https://application.my/callback";

    // Creating the required scopes
    Scope scope = new Scope(scopes);

    try {
        
        // Construct the AuthenticationRequest Builder with scope, client_id and redirect_uri parameters
        AuthenticationRequest.Builder authBuilder = new AuthenticationRequest.Builder(
            // We are generating a URL for code_grant
                new ResponseType("code"),
                scope,
                clientId,
                new URI(redirectURI));
        
        // Set the auth endpoint URI
        authBuilder.endpointURI(new URI(authorizationEndpoint));
        
        // Set random (default constructor) state
        authBuilder.state(new State());
        
        // Set random (default constructor) nonce
        authBuilder.nonce(new Nonce());
        
        // Set prompt=consent
        authBuilder.prompt(new Prompt("consent"));

        // ... and build the auth login URI
        AuthenticationRequest request = authBuilder.build();

    } catch (URISyntaxException e) {
        // TODO processing the error
    }

...
    
```

## Token exchange example
Example of obtaining OIDC tokens in exchange for issued `code`. This exchange needs to be done in the case of a code 
grant authorization flow after successfully completing the authorization.  
The issuance of a `refresh_token` is conditional on the registration of a refresh token grant at the Developer Portal for 
application and the use of the scope `offline_access` in the /auth request. The example assumes that the application 
already has `code` obtained from a callback call to a redirect URI.

[TokenExchange.java](src/main/java/cz/bankid/examples/auth/TokenExchange.java)
```java
import com.nimbusds.jwt.JWT;
import com.nimbusds.oauth2.sdk.*;
import com.nimbusds.oauth2.sdk.auth.ClientAuthentication;
import com.nimbusds.oauth2.sdk.auth.ClientSecretPost;
import com.nimbusds.oauth2.sdk.auth.Secret;
import com.nimbusds.oauth2.sdk.id.ClientID;
import com.nimbusds.oauth2.sdk.token.AccessToken;
import com.nimbusds.oauth2.sdk.token.RefreshToken;
import com.nimbusds.openid.connect.sdk.OIDCTokenResponseParser;
import java.net.URI;

...

    // Application configuration from BankID dev. portal
    ClientID clientId = new ClientID(" ... application client_id ...");

    // Application redirect URI ()
    String redirectURI = "https://application.my/callback";
    
    // Client secret value
    String clintSecretStr = "... application client secret ...";
    
    // BankID token endpoint (from discovery endpoint)
    String tokenEndpoint = "https://oidc.sandbox.bankid.cz/token";
    
    // Code from callback on redirect URI
    String code = "... code ...";
    
    try {
        // Set the code object
        AuthorizationCode authorizationCode = new AuthorizationCode(code);
        
        // Set the redirectURI and create code grant object
        URI callbackURI =  new URI(redirectURI);
        AuthorizationGrant codeGrant = new AuthorizationCodeGrant(authorizationCode, callbackURI);
    
        // Set the client_secret value and create client authentication
        Secret clientSecret = new Secret(clintSecretStr);
        ClientAuthentication clientAuth = new ClientSecretPost(clientId, clientSecret);
    
        // Create token endpoint URI and make the token request
        URI tokenEndpointURI = new URI(tokenEndpoint);
        TokenRequest request = new TokenRequest(tokenEndpointURI, clientAuth, codeGrant);
    
        // Get the token response
        TokenResponse tokenResponse = OIDCTokenResponseParser.parse(request.toHTTPRequest().send());
        if (tokenResponse.indicatesSuccess()) {
            
            // Get success response
            AccessTokenResponse successResponse = tokenResponse.toSuccessResponse();
            
            // Obtaining an access_token
            AccessToken accessToken = successResponse.getTokens().getAccessToken();
            
            // and also obtaining an refresh_token (if can)
            RefreshToken refreshToken = successResponse.getTokens().getRefreshToken();
            
            // and and finally obtaining an id_token
            JWT idToken = successResponse.getTokens().toOIDCTokens().getIDToken();
            
            ...
            
        } else {
            // TODO processing the error
        }
    } catch (URISyntaxException e) {
        // TODO processing the error
    } catch (ParseException e) {
        // TODO processing the error
    } catch (IOException e) {
        // TODO processing the error
    }


```

## UserInfo or Profile Call example

Example of how to get user data from the BankID UserInfo and Profile API. The example assumes
that the end-user was before the call properly authenticated itself, and the `code` was exchanged
for a `access_token`. The range of data provided corresponds to the used scopes.

The example shows retrieving data in the form of JSON.

[UserData.java](src/main/java/cz/bankid/examples/auth/UserData.java)
```java
import com.nimbusds.oauth2.sdk.ParseException;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.token.BearerAccessToken;
import com.nimbusds.openid.connect.sdk.UserInfoRequest;
import java.net.URI;

...

    // Set the right data API url (for example UserInfo)
    String userInfoURL = "https://oidc.sandbox.bankid.cz/userinfo";
    
    try {
        URI userInfoEndpoint = new URI(userInfoURL);
        
        // You must have a valid access_token
        BearerAccessToken token = BearerAccessToken.parse("Bearer .... my access token ...");
    
        // And call the API
        HTTPResponse dataResponse = new UserInfoRequest(userInfoEndpoint, token)
                        .toHTTPRequest()
                        .send();
    
        // Data in JSON format
        String data = dataResponse.getContentAsJSONObject().toJSONString();
    
    } catch (URISyntaxException e) {
        // TODO processing the error
    } catch (ParseException e) {
        // TODO processing the error
    } catch (IOException e) {
        // TODO processing the error
    }

...

```

## Example of obtaining specific BankID products
Data of individual products are obtained from /userinfo and /profile API. Userinfo endpoint is primarily intended
for obtaining Connect product data. The Profile API is used to get data from identification products such as
Identify, Identify Plus, and Identify AML.

> The data content of individual products depends on what scope the application has set in the Developer Portal
> and what scope was finally agreed by the user during the authentication process.

As object classes of BankID products for this example, it is possible to use examples of java classes
in the public BankID repository [here](https://github.com/BankovniIdentita/BankID-examples). The structure of all essential BankID products
(Connect, Identify, Identify Plus and Identify AML) is available in the repository [BankID Examples](https://github.com/BankovniIdentita/BankID-examples).


[BankIDProducts.java](src/main/java/cz/bankid/examples/auth/BankIDProducts.java)
```java
import com.google.gson.Gson;
import com.nimbusds.oauth2.sdk.ParseException;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.token.BearerAccessToken;
import com.nimbusds.openid.connect.sdk.UserInfoRequest;
import cz.bankid.examples.product.IdentifyAML;
import java.net.URI;

...

    // Set the right data API url (for example UserInfo)
    String profileURL = "https://oidc.sandbox.bankid.cz/profile";
    
    try {
        URI profileEndpoint = new URI(profileURL);
        
        // You must have a valid access_token
        BearerAccessToken token = BearerAccessToken.parse("Bearer .... my access token ...");
    
        // And call the Profile API
        HTTPResponse profileResponse = new UserInfoRequest(profileEndpoint, token)
                .toHTTPRequest()
                .send();
    
        // Use Gson for transformation
        Gson gson = new Gson();
        if (profileResponse.getStatusCode() != 200) {
    
            // Convert json to IdentifyAML product (from BankID repository)
            IdentifyAML amlProduct = gson.fromJson(
                    profileResponse.getContentAsJSONObject().toJSONString(), 
                    IdentifyAML.class
            );
    
            // Get name from users data
            String userName = amlProduct.getFamily_name();
    
            ...
        
        }
    
    } catch (URISyntaxException e) {
        // TODO processing the error
    } catch (ParseException e) {
        // TODO processing the error
    } catch (IOException e) {
        // TODO processing the error
    }

...
```

