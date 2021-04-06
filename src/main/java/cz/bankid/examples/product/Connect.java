package cz.bankid.examples.product;

import cz.bankid.examples.entities.ConnectClaims;
import cz.bankid.examples.entities.ConnectVerifiedClaims;
import cz.bankid.examples.entities.VerifiedClaims;


/**
 * The Czech BankID authentication product with UserInfo dataset
 *
 *          Product
 *             |
 *             -> Connect
 *
 */
public class Connect extends ConnectClaims implements IProduct {

    String sub;
    String txn;
    ConnectVerifiedClaims verified_claims;

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

    public ConnectVerifiedClaims getVerified_claims() {
        return verified_claims;
    }

    public void setVerified_claims(VerifiedClaims verified_claims) {
        this.verified_claims = (ConnectVerifiedClaims) verified_claims;
    }
}
