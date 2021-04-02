package cz.bankid.examples.auth;

import com.google.gson.Gson;
import cz.bankid.examples.entities.IdentifyAMLVerifiedClaims;
import cz.bankid.examples.entities.ConnectVerifiedClaims;
import cz.bankid.examples.entities.IdentifyPlusVerifiedClaims;
import cz.bankid.examples.entities.entity.Maritalstatus;
import cz.bankid.examples.entities.entity.TrustFramework;
import cz.bankid.examples.product.IdentifyAML;
import cz.bankid.examples.product.Connect;
import cz.bankid.examples.product.IdentifyPlus;
import junit.framework.TestCase;

public class AuthenticateTest extends TestCase {
    String testUserInfo = "{\n" +
            "  \"sub\": \"23f1ac00-5d54-4169-a288-794ae2ead0c4\",\n" +
            "  \"txn\": \"6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.gender\",\n" +
            "  \"verified_claims\": {\n" +
            "    \"verification\": {\n" +
            "      \"trust_framework\": \"cz_aml\",\n" +
            "      \"time\": {},\n" +
            "      \"verification_process\": \"45244782\"\n" +
            "    },\n" +
            "    \"claims\": {\n" +
            "      \"name\": \"Jan Novák\",\n" +
            "      \"given_name\": \"Jan\",\n" +
            "      \"family_name\": \"Novák\",\n" +
            "      \"gender\": \"male\",\n" +
            "      \"birthdate\": \"1970-08-01\"\n" +
            "    }\n" +
            "  },\n" +
            "  \"name\": \"Jan Novák\",\n" +
            "  \"given_name\": \"Jan\",\n" +
            "  \"family_name\": \"Novák\",\n" +
            "  \"gender\": \"male\",\n" +
            "  \"birthdate\": \"1970-08-01\",\n" +
            "  \"nickname\": \"Fantomas\",\n" +
            "  \"preferred_username\": \"JanN\",\n" +
            "  \"email\": \"j.novak@email.com\",\n" +
            "  \"email_verified\": false,\n" +
            "  \"zoneinfo\": \"Europe/Prague\",\n" +
            "  \"locale\": \"cs_CZ\",\n" +
            "  \"phone_number\": \"+420123456789\",\n" +
            "  \"phone_number_verified\": true,\n" +
            "  \"updated_at\": 1568188433000\n" +
            "}";

    String testProfile = "{\n" +
            "  \"sub\": \"23f1ac00-5d54-4169-a288-794ae2ead0c4\",\n" +
            "  \"txn\": \"6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.addresses\",\n" +
            "  \"verified_claims\": {\n" +
            "    \"verification\": {\n" +
            "      \"trust_framework\": \"cz_aml\",\n" +
            "      \"time\": null,\n" +
            "      \"verification_process\": \"45244782\"\n" +
            "    },\n" +
            "    \"claims\": {\n" +
            "      \"given_name\": \"Jan\",\n" +
            "      \"family_name\": \"Novák\",\n" +
            "      \"gender\": \"male\",\n" +
            "      \"birthdate\": \"1970-08-01\",\n" +
            "      \"maritalstatus\": \"MARRIED\",\n" +
            "      \"addresses\": [\n" +
            "        {\n" +
            "          \"type\": \"PERMANENT_RESIDENCE\",\n" +
            "          \"street\": \"Olbrachtova\",\n" +
            "          \"buildingapartment\": \"1929\",\n" +
            "          \"streetnumber\": \"62\",\n" +
            "          \"city\": \"Praha\",\n" +
            "          \"zipcode\": \"14000\",\n" +
            "          \"country\": \"CZ\"\n" +
            "        }\n" +
            "      ],\n" +
            "      \"idcards\": [\n" +
            "        {\n" +
            "          \"type\": \"ID\",\n" +
            "          \"description\": \"Občanský průkaz\",\n" +
            "          \"country\": \"CZ\",\n" +
            "          \"number\": \"123456789\",\n" +
            "          \"valid_to\": \"2023-10-11\",\n" +
            "          \"issuer\": \"Úřad městské části Praha 4\",\n" +
            "          \"issue_date\": \"2020-01-28\"\n" +
            "        }\n" +
            "      ]\n" +
            "    }\n" +
            "  },\n" +
            "  \"given_name\": \"Jan\",\n" +
            "  \"family_name\": \"Novák\",\n" +
            "  \"gender\": \"male\",\n" +
            "  \"birthdate\": \"1970-08-01\",\n" +
            "  \"birthnumber\": \"7008010147\",\n" +
            "  \"age\": 50,\n" +
            "  \"majority\": true,\n" +
            "  \"date_of_death\": null,\n" +
            "  \"birthplace\": \"Praha 4\",\n" +
            "  \"primary_nationality\": \"CZ\",\n" +
            "  \"nationalities\": [\n" +
            "    \"CZ\",\n" +
            "    \"AT\",\n" +
            "    \"SK\"\n" +
            "  ],\n" +
            "  \"maritalstatus\": \"MARRIED\",\n" +
            "  \"email\": \"J.novak@email.com\",\n" +
            "  \"phone_number\": \"+420123456789\",\n" +
            "  \"pep\": false,\n" +
            "  \"limited_legal_capacity\": false,\n" +
            "  \"addresses\": [\n" +
            "    {\n" +
            "      \"type\": \"PERMANENT_RESIDENCE\",\n" +
            "      \"street\": \"Olbrachtova\",\n" +
            "      \"buildingapartment\": \"1929\",\n" +
            "      \"streetnumber\": \"62\",\n" +
            "      \"city\": \"Praha\",\n" +
            "      \"zipcode\": \"14000\",\n" +
            "      \"country\": \"CZ\",\n" +
            "      \"ruian_reference\": \"186GF76\"\n" +
            "    }\n" +
            "  ],\n" +
            "  \"idcards\": [\n" +
            "    {\n" +
            "      \"type\": \"ID\",\n" +
            "      \"description\": \"Občanský průkaz\",\n" +
            "      \"country\": \"CZ\",\n" +
            "      \"number\": \"123456789\",\n" +
            "      \"valid_to\": \"2023-10-11\",\n" +
            "      \"issuer\": \"Úřad městské části Praha 4\",\n" +
            "      \"issue_date\": \"2020-01-28\"\n" +
            "    }\n" +
            "  ],\n" +
            "  \"paymentAccounts\": [\n" +
            "    \"CZ0708000000001019382023\"\n" +
            "  ],\n" +
            "  \"updated_at\": 1568188433000\n" +
            "}";

    public void testGetAuthUrl() {
    }

    public void testGetTokens() {
    }

    public void testGetConnect() {
        Gson gson = new Gson();
        Connect connect = gson.fromJson(testUserInfo, Connect.class);
        assertEquals("Fantomas", connect.getNickname());
        assertEquals("Jan Novák", ((ConnectVerifiedClaims)connect.getVerified_claims()).getClaims().getName());
    }

    public void testKYCPlus() {
        Gson gson = new Gson();
        IdentifyPlus identifyPlus = gson.fromJson(testProfile, IdentifyPlus.class);
        assertEquals("CZ0708000000001019382023", identifyPlus.getPaymentAccounts().get(0));
        assertEquals(Maritalstatus.MARRIED, ((IdentifyPlusVerifiedClaims) identifyPlus.getVerified_claims()).getClaims().getMaritalstatus());
    }

    public void testAML() {
        Gson gson = new Gson();
        IdentifyAML identifyAml = gson.fromJson(testProfile, IdentifyAML.class);
        assertEquals("CZ0708000000001019382023", identifyAml.getPaymentAccounts().get(0));
        assertEquals(Maritalstatus.MARRIED, ((IdentifyAMLVerifiedClaims) identifyAml.getVerified_claims()).getClaims().getMaritalstatus());
        assertEquals(TrustFramework.cz_aml, ((IdentifyAMLVerifiedClaims) identifyAml.getVerified_claims()).getVerification().getTrust_framework());
    }
}