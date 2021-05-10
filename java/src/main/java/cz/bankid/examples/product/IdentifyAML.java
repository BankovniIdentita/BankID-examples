package cz.bankid.examples.product;

import cz.bankid.examples.entities.IdentifyAMLVerifiedClaims;
import cz.bankid.examples.entities.IdentifyPlusClaims;
import cz.bankid.examples.entities.VerifiedClaims;

public class IdentifyAML extends IdentifyPlusClaims implements IProduct {

    String sub;
    String txn;
    IdentifyAMLVerifiedClaims verified_claims;

    @Override
    public String getSub() {
        return sub;
    }

    @Override
    public void setSub(String sub) {
        this.sub = sub;
    }

    @Override
    public String getTxn() {
        return txn;
    }

    @Override
    public void setTxn(String txn) {
        this.txn = txn;
    }

    public VerifiedClaims getVerified_claims() {
        return verified_claims;
    }

    public void setVerified_claims(VerifiedClaims verified_claims) {
        this.verified_claims = (IdentifyAMLVerifiedClaims) verified_claims;
    }
}
