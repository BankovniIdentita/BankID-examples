package cz.bankid.examples.entities;

import cz.bankid.examples.entities.entity.Verification;

/**
 * By this element BankID wants to ensure that RPs cannot mix up verified and unverified Claims and incidentally
 * process unverified Claims as verified Claims.
 */
public class IdentifyAMLVerifiedClaims extends IdentifyPlusVerifiedClaims implements VerifiedClaims {

    /**
     * This element contains the information about the process conducted to verify a person's identity and bind the
     * respective person data to a user account.
     */
    Verification verification;

    public Verification getVerification() {
        return verification;
    }

    public void setVerification(Verification verification) {
        this.verification = verification;
    }
}
