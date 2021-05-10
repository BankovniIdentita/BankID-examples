package cz.bankid.examples.auth;

import com.google.gson.Gson;
import com.nimbusds.oauth2.sdk.ParseException;
import com.nimbusds.oauth2.sdk.http.HTTPResponse;
import com.nimbusds.oauth2.sdk.token.BearerAccessToken;
import com.nimbusds.openid.connect.sdk.UserInfoRequest;
import cz.bankid.examples.product.IdentifyAML;

import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;

/**
 * Example of obtaining specific BankID products
 * Data of individual products are obtained from /userinfo and /profile API. Userinfo endpoint is primarily intended
 * for obtaining Connect product data. The Profile API is used to get data from identification products such as
 * Identify, Identify Plus, and Identify AML.
 *
 * The data content of individual products depends on what scope the application has set in the Developer Portal
 * and what scope was finally agreed by the user during the authentication process.
 *
 * As object classes of BankID products for this example, it is possible to use examples of java classes
 * in the public BankID repository here. The structure of all essential BankID products
 * (Connect, Identify, Identify Plus and Identify AML) is available in the repository.
 */
public class BankIDProducts {

    // Set the right data API url (for example UserInfo)
    private String profileURL = "https://oidc.sandbox.bankid.cz/profile";

    private void getData() {

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
