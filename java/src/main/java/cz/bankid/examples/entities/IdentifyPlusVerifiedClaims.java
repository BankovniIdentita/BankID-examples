package cz.bankid.examples.entities;

/**
 * By this element BankID wants to ensure that RPs cannot mix up verified and unverified Claims and
 * incidentally process unverified Claims as verified Claims.
 */
public class IdentifyPlusVerifiedClaims implements VerifiedClaims {

    /**
     * Object that is the container for the verified Claims about the End-User.
     *
     * This is an element that will eventually be used by IDP in the future when the data will be verified, for
     * example, against state basic registers.
     */
    IdentifyPlusClaims claims;

    public IdentifyPlusClaims getClaims() {
        return claims;
    }

    public void setClaims(Claims claims) {
        this.claims = (IdentifyPlusClaims) claims;
    }

}
