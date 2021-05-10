package cz.bankid.examples.product;

import cz.bankid.examples.entities.IdentifyPlusClaims;
import cz.bankid.examples.entities.IdentifyPlusVerifiedClaims;
import cz.bankid.examples.entities.VerifiedClaims;


/**
 * The Czech BankID authentication product with User Extended Profile dataset
 *
 *          Product
 *             |
 *             -> KYC (Basic)
 *                  |
 *                  -> KYCPlus
 *
 */
public class IdentifyPlus extends IdentifyPlusClaims implements IProduct {

    String sub;
    String txn;
    IdentifyPlusVerifiedClaims verified_claims;


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

    public void setVerified_claims(IdentifyPlusVerifiedClaims verified_claims) {
        this.verified_claims = verified_claims;
    }

    public VerifiedClaims getVerified_claims() {
        return verified_claims;
    }


}
