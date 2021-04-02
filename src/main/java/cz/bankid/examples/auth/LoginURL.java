package cz.bankid.examples.auth;

import com.nimbusds.oauth2.sdk.ResponseType;
import com.nimbusds.oauth2.sdk.Scope;
import com.nimbusds.oauth2.sdk.id.ClientID;
import com.nimbusds.oauth2.sdk.id.State;
import com.nimbusds.openid.connect.sdk.AuthenticationRequest;
import com.nimbusds.openid.connect.sdk.Nonce;
import com.nimbusds.openid.connect.sdk.Prompt;

import java.net.URI;
import java.net.URISyntaxException;

/**
 * Example of building a BankID Login URI
 *
 * The example uses values and parameters relevant to the BankID service. We recommend obtaining the URL for the correct
 * auth endpoint call from the OIDC configuration endpoint BankID. We also recommend that you verify supported scopes and
 * grants against the configuration.
 *
 */
public class LoginURL {

    // Application configuration from BankID dev. portal
    private String[] scopes = {"openid", "profile.titles", "profile.name", "profile.email", "offline_access"};
    private ClientID clientId = new ClientID(" ... application client_id ...");
    private String redirectURI = "https://application.my/callback";

    // BankID configuration (from discovery endpoint)
    private String authorizationEndpoint = "https://oidc.sandbox.bankid.cz/auth";


    public void getLoginURI() {

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

    }

}
