package cz.bankid.examples.entities.entity;

/**
 * User's id card
 */
public class IDCard {

    /**
     * Id card type code:
     */
    IDCardType type;
    /**
     * Localized id card type description.
     */
    String description;
    /**
     * Country for which is id card valid.
     */
    String country;
    /**
     * Number of id card.
     */
    String number;
    /**
     * Id card validity.
     */
    String valid_to;
    /**
     * Office that issued id card.
     */
    String issuer;
    /**
     * Date of id card issue.
     */
    String issue_date;

    public IDCardType getType() {
        return type;
    }

    public void setType(IDCardType type) {
        this.type = type;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public String getCountry() {
        return country;
    }

    public void setCountry(String country) {
        this.country = country;
    }

    public String getNumber() {
        return number;
    }

    public void setNumber(String number) {
        this.number = number;
    }

    public String getValid_to() {
        return valid_to;
    }

    public void setValid_to(String valid_to) {
        this.valid_to = valid_to;
    }

    public String getIssuer() {
        return issuer;
    }

    public void setIssuer(String issuer) {
        this.issuer = issuer;
    }

    public String getIssue_date() {
        return issue_date;
    }

    public void setIssue_date(String issue_date) {
        this.issue_date = issue_date;
    }
}
