package cz.bankid.examples.auth;

import com.nimbusds.oauth2.sdk.ParseException;
import com.nimbusds.oauth2.sdk.http.HTTPRequest;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.id.Issuer;
import com.nimbusds.openid.connect.sdk.op.OIDCProviderConfigurationRequest;
import com.nimbusds.openid.connect.sdk.op.OIDCProviderMetadata;

import java.io.IOException;
import java.net.URI;

/**
 * Example of obtaining OpenID Connect configuration data from BankID Sandbox
 *
 * This configuration provides information about OpenID Connect service:
 *
 *   - endpoint addresses
 *   - encryption and signing algorithms
 *   - list of supported scopes and claims
 *   - list of supported grants and functions
 *
 */
public class BankIDConfiguration {

    // The BankID Sandbox issuer uri
    private String bankIDIssuerURI = "https://oidc.sandbox.bankid.cz/";


    private void getConfiguration() {

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

        } catch (IOException e) {
            // TODO processing the error
        } catch (ParseException e) {
            // TODO processing the error
        }

    }

}
