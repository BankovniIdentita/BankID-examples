package cz.bankid.examples.entities.entity;

/**
 * User's address
 *
 */
public class Address {
    /**
     * Type of address
     */
    AddressType type;
    /**
     * Street name.
     */
    String street;
    /**
     * Address land registry number.
     */
    String buildingapartment;
    /**
     * Additional address house number.
     */
    String streetnumber;
    /**
     * City name.
     */
    String city;
    /**
     * Zip code of the address.
     */
    String zipcode;
    /**
     * Country Code, ISO 3166-1 format, subtype ALPHA-2. This means two letters in uppercase.
     */
    String country;
    /**
     * Address reference to the register of territorial identification, addresses and real estate (RUIAN).
     */
    String ruian_reference;

    public AddressType getType() {
        return type;
    }

    public void setType(AddressType type) {
        this.type = type;
    }

    public String getStreet() {
        return street;
    }

    public void setStreet(String street) {
        this.street = street;
    }

    public String getBuildingapartment() {
        return buildingapartment;
    }

    public void setBuildingapartment(String buildingapartment) {
        this.buildingapartment = buildingapartment;
    }

    public String getStreetnumber() {
        return streetnumber;
    }

    public void setStreetnumber(String streetnumber) {
        this.streetnumber = streetnumber;
    }

    public String getCity() {
        return city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getZipcode() {
        return zipcode;
    }

    public void setZipcode(String zipcode) {
        this.zipcode = zipcode;
    }

    public String getCountry() {
        return country;
    }

    public void setCountry(String country) {
        this.country = country;
    }

    public String getRuian_reference() {
        return ruian_reference;
    }

    public void setRuian_reference(String ruian_reference) {
        this.ruian_reference = ruian_reference;
    }
}
