package cz.bankid.examples.entities.entity;

/**
 *
 * This element contains the information about the process conducted to verify a person's identity and bind
 * the respective person data to a user account.
 */
public class Verification {

    /**
     * String determining the trust framework governing the identity verification process and the identity
     * assurance level of the OP.
     */
    TrustFramework trust_framework;
    /**
     * Time stamp in ISO 8601:2004 [ISO8601-2004] YYYY-MM-DDThh:mm:ss±hh format representing the date and time when
     * identity verification took place. Presence of this element might be required for certain trust frameworks.
     */
    String time;
    /**
     * Reference to the identity verification process as performed by the identity providers. Used for backtracing
     * in case of disputes or audits. Presence of this element might be required for certain trust frameworks.
     * In the case of BankID, the value of this element requires the tax number of the bank (financial institution)
     * that carried out the identification process.
     *
     * This is the bank's tax number, which is kept in the list of regulated and registered entities of the CNB JERRS.
     */
    String verification_process;

    public TrustFramework getTrust_framework() {
        return trust_framework;
    }

    public void setTrust_framework(TrustFramework trust_framework) {
        this.trust_framework = trust_framework;
    }

    public String getTime() {
        return time;
    }

    public void setTime(String time) {
        this.time = time;
    }

    public String getVerification_process() {
        return verification_process;
    }

    public void setVerification_process(String verification_process) {
        this.verification_process = verification_process;
    }
}
