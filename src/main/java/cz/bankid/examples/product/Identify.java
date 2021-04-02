package cz.bankid.examples.product;

import cz.bankid.examples.entities.IdentifyClaims;
import cz.bankid.examples.entities.IdentifyVerifiedClaims;
import cz.bankid.examples.entities.VerifiedClaims;


/**
 * The Czech BankID authentication product with User Extended Profile dataset
 *
 *          Product
 *             |
 *             -> Identify (Basic)
 *
 *
 */
public class Identify extends IdentifyClaims implements IProduct {

    String sub;
    String txn;
    IdentifyVerifiedClaims verified_claims;

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
        this.verified_claims = (IdentifyVerifiedClaims) verified_claims;
    }
}
