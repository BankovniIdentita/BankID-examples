package cz.bankid.examples.product;


/**
 * The Czech BankID basic authentication product with colect UserInfo and Profile basic dataset.
 */
public interface IProduct {

      void setSub(String sub);
      String getSub();

      void setTxn(String txn);
      String getTxn();

}
