package cz.bankid.examples.auth;

import com.nimbusds.oauth2.sdk.ParseException;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.token.BearerAccessToken;
import com.nimbusds.openid.connect.sdk.UserInfoRequest;

import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;

/**
 * UserInfo or Profile Call example
 *
 * Example of how to get user data from the BankID UserInfo and Profile API. The example assumes
 * that the end-user was before the call properly authenticated itself, and the code was exchanged
 * for a token. The range of data provided corresponds to the scopes used.
 */
public class UserData {

    // Set the right data API url (for example UserInfo)
    private String userInfoURL = "https://oidc.sandbox.bankid.cz/userinfo";

    private void getData() {

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

    }

}
