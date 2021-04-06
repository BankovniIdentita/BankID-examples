package cz.bankid.examples.auth;

import com.google.gson.Gson;
import cz.bankid.examples.entities.IdentifyAMLVerifiedClaims;
import cz.bankid.examples.entities.IdentifyPlusVerifiedClaims;
import cz.bankid.examples.entities.entity.MaritalStatus;
import cz.bankid.examples.entities.entity.TrustFramework;
import cz.bankid.examples.product.IdentifyAML;
import cz.bankid.examples.product.Connect;
import cz.bankid.examples.product.IdentifyPlus;
import org.junit.Test;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.stream.Collectors;

import static org.junit.Assert.assertEquals;

public class AuthenticateTest {
    private final String testUserInfo = getFileContent("userinfo.json");

    private final String testProfile = getFileContent("profile.json");

    @Test
    public void testGetConnect() {
        Gson gson = new Gson();
        Connect connect = gson.fromJson(testUserInfo, Connect.class);
        assertEquals("Fantomas", connect.getNickname());
        assertEquals("Jan Novák", (connect.getVerified_claims()).getClaims().getName());
    }

    @Test
    public void testKYCPlus() {
        Gson gson = new Gson();
        IdentifyPlus identifyPlus = gson.fromJson(testProfile, IdentifyPlus.class);
        assertEquals("CZ0708000000001019382023", identifyPlus.getPaymentAccounts().get(0));
        assertEquals(MaritalStatus.MARRIED, ((IdentifyPlusVerifiedClaims) identifyPlus.getVerified_claims()).getClaims().getMaritalstatus());
    }

    @Test
    public void testAML() {
        Gson gson = new Gson();
        IdentifyAML identifyAml = gson.fromJson(testProfile, IdentifyAML.class);
        assertEquals("CZ0708000000001019382023", identifyAml.getPaymentAccounts().get(0));
        assertEquals(MaritalStatus.MARRIED, ((IdentifyAMLVerifiedClaims) identifyAml.getVerified_claims()).getClaims().getMaritalstatus());
        assertEquals(TrustFramework.cz_aml, ((IdentifyAMLVerifiedClaims) identifyAml.getVerified_claims()).getVerification().getTrust_framework());
    }

    private String getFileContent(String fileName) {
        String fileContent = null;
        Path path = Path.of("src/test/resources/io/" + fileName);

        try {
            fileContent = Files.lines(path).collect(Collectors.joining(System.lineSeparator()));
        } catch (IOException e) {
            e.printStackTrace();
        }

        return fileContent;
    }
}