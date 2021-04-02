package cz.bankid.examples.entities;

import cz.bankid.examples.entities.entity.IDCard;
import cz.bankid.examples.entities.entity.Maritalstatus;

import java.util.List;
/**
 * KYC Plus Product's verified claims.
 * Object that is the container for the verified Claims about the End-User.
 *
 * This is an element that will eventually be used by IDP in the future when the data will be verified, for example,
 * against state basic registers.
 */
public class IdentifyPlusClaims extends IdentifyClaims {

    /**
     * User's birth place.
     */
    String birthplace;
    /**
     * Optional element user's primary nationality, ISO 3166-1 format, subtype ALPHA-2. This means two
     * letters in uppercase.
     */
    String primary_nationality;
    /**
     * All user's nationalities, ISO 3166-1 format, subtype ALPHA-2. This means two letters in uppercase.
     */
    String[] nationalities;
    /**
     * Marital status. One of:
     *
     * COHABITATION - cohabitation status.
     * MARRIED - married status
     * DIVORCED - divorced status
     * REGISTERED_PARTNERSHIP - registered partnership status
     * REGISTERED_PARTNERSHIP_CANCELED - registered partnership canceled status
     * WIDOWED - widowed status
     * SINGLE - single status
     * UNKNOWN - unknown status
     */
    Maritalstatus maritalstatus;
    /**
     *
     * User's id card/s.
     */
    List<IDCard> idcards;
    /**
     * An person is over the threshold of adulthood as recognized or declared in law.
     */
    boolean majority;
    /**
     * Flag that the authenticated user is politically exposed person (PEP). In financial regulation, a politically
     * exposed person is one who has been entrusted with a prominent public function. A PEP generally presents a
     * higher risk for potential involvement in bribery and corruption by virtue of their position and the influence
     * that they may hold.
     */
    boolean pep;
    /**
     * An indication of whether this is a person with limited legal capacity.
     */
    boolean limited_legal_capacity;
    /**
     * User's payment account numbers in CZ IBAN format.
     */
    List<String> paymentAccounts;

    public String getBirthplace() {
        return birthplace;
    }

    public void setBirthplace(String birthplace) {
        this.birthplace = birthplace;
    }

    public String getPrimary_nationality() {
        return primary_nationality;
    }

    public void setPrimary_nationality(String primary_nationality) {
        this.primary_nationality = primary_nationality;
    }

    public String[] getNationalities() {
        return nationalities;
    }

    public void setNationalities(String[] nationalities) {
        this.nationalities = nationalities;
    }

    public Maritalstatus getMaritalstatus() {
        return maritalstatus;
    }

    public void setMaritalstatus(Maritalstatus maritalstatus) {
        this.maritalstatus = maritalstatus;
    }

    public List<IDCard> getIdcards() {
        return idcards;
    }

    public void setIdcards(List<IDCard> idcards) {
        this.idcards = idcards;
    }

    public boolean isMajority() {
        return majority;
    }

    public void setMajority(boolean majority) {
        this.majority = majority;
    }

    public boolean isPep() {
        return pep;
    }

    public void setPep(boolean pep) {
        this.pep = pep;
    }

    public boolean isLimited_legal_capacity() {
        return limited_legal_capacity;
    }

    public void setLimited_legal_capacity(boolean limited_legal_capacity) {
        this.limited_legal_capacity = limited_legal_capacity;
    }

    public List<String> getPaymentAccounts() {
        return paymentAccounts;
    }

    public void setPaymentAccounts(List<String> paymentAccounts) {
        this.paymentAccounts = paymentAccounts;
    }
}
