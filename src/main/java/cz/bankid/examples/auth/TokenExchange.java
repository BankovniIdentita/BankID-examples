package cz.bankid.examples.auth;

import com.nimbusds.jwt.JWT;
import com.nimbusds.oauth2.sdk.*;
import com.nimbusds.oauth2.sdk.auth.ClientAuthentication;
import com.nimbusds.oauth2.sdk.auth.ClientSecretPost;
import com.nimbusds.oauth2.sdk.auth.Secret;
import com.nimbusds.oauth2.sdk.id.ClientID;
import com.nimbusds.oauth2.sdk.token.AccessToken;
import com.nimbusds.oauth2.sdk.token.RefreshToken;
import com.nimbusds.openid.connect.sdk.OIDCTokenResponseParser;

import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;

/**
 * Token exchange example
 * Example of obtaining OIDC tokens in exchange for issued `code`. This exchange needs to be done in the case of a code
 * grant authorization flow after successfully completing the authorization.
 *
 * The issuance of a `refresh_token` is conditional on the registration of a refresh token grant at the Developer Portal for
 * application and the use of the scope `offline_access` in the /auth request. The example assumes that the application
 * already has `code` obtained from a callback call to a redirect URI.
 *
 */
public class TokenExchange {

    // Application configuration from BankID dev. portal
    private ClientID clientId = new ClientID(" ... application client_id ...");

    // Application redirect URI ()
    private String redirectURI = "https://application.my/callback";

    // Client secret value
    private String clintSecretStr = "... application client secret ...";

    // BankID token endpoint (from discovery endpoint)
    private String tokenEndpoint = "https://oidc.sandbox.bankid.cz/token";

    // Code from callback on redirect URI
    private String code = "... code ...";

    private void getToken() {

        try {
            // Set the code object
            AuthorizationCode authorizationCode = new AuthorizationCode(code);

            // Set the redirectURI and create code grant object
            URI callbackURI =  new URI(redirectURI);
            AuthorizationGrant codeGrant = new AuthorizationCodeGrant(authorizationCode, callbackURI);

            // Set the client_secret value and create client authentication
            Secret clientSecret = new Secret(clintSecretStr);
            ClientAuthentication clientAuth = new ClientSecretPost(clientId, clientSecret);

            //Create token endpoint URI and make the token request
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

    }

}
