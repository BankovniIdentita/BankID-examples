package cz.bankid.examples.entities;

import cz.bankid.examples.entities.entity.Address;
import cz.bankid.examples.entities.entity.Gender;

import java.util.List;

/**
 * KYC Product's verified claims.
 * Object that is the container for the verified Claims about the End-User.
 *
 * This is an element that will eventually be used by IDP in the future when the data will be verified, for example,
 * against state basic registers.
 */
public class IdentifyClaims implements Claims {

    /**
     * User's title prefix
     */
    String title_prefix;
    /**
     * User`s title suffix
     */
    String title_suffix;
    /**
     * Given name(s) or first name(s) of the End-User. Note that in some cultures, people can have multiple given
     * names; all can be present, with the names being separated by space characters.
     */
    String given_name;
    /**
     * Surname(s) or last name(s) of the End-User. Note that in some cultures, people can have multiple family names
     * or no family name; all can be present, with the names being separated by space characters.
     */
    String family_name;
    /**
     * Middle name(s) of the End-User. Note that in some cultures, people can have multiple middle names; all can be
     * present, with the names being separated by space characters. Also note that in some cultures, middle names
     * are not used.
     */
    String middle_name;
    /**
     * End-User's preferred telephone number. E.164 [E.164] is RECOMMENDED as the format of this Claim, for example,
     * +1 (425) 555-1212 or +56 (2) 687 2400. If the phone number contains an extension, it is RECOMMENDED that the
     * extension be represented using the RFC 3966 [RFC3966] extension syntax, for example, +1 (604) 555-1234;ext=5678.
     */
    String phone_number;
    /**
     * End-User's preferred e-mail address. Its value MUST conform to the RFC 5322 [RFC5322] addr-spec syntax.
     * The API consumer MUST NOT rely upon this value being unique.
     */
    String email;
    /**
     * User’s addresses.
     */
    List<Address> addresses;
    /**
     * End-User's birthday, represented as an ISO 8601:2004 [ISO8601‑2004] YYYY-MM-DD format. The year MAY be 0000,
     * indicating that it is omitted. To represent only the year, YYYY format is allowed. Note that depending on the
     * underlying platform's date related function, providing just year can result in varying month and day, so the
     * implementers need to take this factor into account to correctly process the dates.
     */
    String birthdate;
    /**
     * Current age of the client given in years.
     */
    int age;
    /**
     * Date of death of the client. Practically still null. Only in the case of updating data
     * is it possible to receive a specific date.
     */
    String date_of_death;
    /**
     * End-User's gender. Values defined by this specification are female and male. Other values MAY be used when
     * neither of the defined values are applicable.
     */
    Gender gender;
    /**
     * User's birth number. Birth number is required if nationality is Czech ("CZ").
     */
    String birthnumber;
    /**
     * Time the End-User's information was last updated. Its value is a JSON number representing the number of
     * seconds from 1970-01-01T0:0:0Z as measured in UTC until the date/time.
     */
    long updated_at;

    public String getTitle_prefix() {
        return title_prefix;
    }

    public void setTitle_prefix(String title_prefix) {
        this.title_prefix = title_prefix;
    }

    public String getTitle_suffix() {
        return title_suffix;
    }

    public void setTitle_suffix(String title_suffix) {
        this.title_suffix = title_suffix;
    }

    public String getGiven_name() {
        return given_name;
    }

    public void setGiven_name(String given_name) {
        this.given_name = given_name;
    }

    public String getFamily_name() {
        return family_name;
    }

    public void setFamily_name(String family_name) {
        this.family_name = family_name;
    }

    public String getMiddle_name() {
        return middle_name;
    }

    public void setMiddle_name(String middle_name) {
        this.middle_name = middle_name;
    }

    public String getPhone_number() {
        return phone_number;
    }

    public void setPhone_number(String phone_number) {
        this.phone_number = phone_number;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public List<Address> getAddresses() {
        return addresses;
    }

    public void setAddresses(List<Address> addresses) {
        this.addresses = addresses;
    }

    public String getBirthdate() {
        return birthdate;
    }

    public void setBirthdate(String birthdate) {
        this.birthdate = birthdate;
    }

    public int getAge() {
        return age;
    }

    public void setAge(int age) {
        this.age = age;
    }

    public String getDate_of_death() {
        return date_of_death;
    }

    public void setDate_of_death(String date_of_death) {
        this.date_of_death = date_of_death;
    }

    public Gender getGender() {
        return gender;
    }

    public void setGender(Gender gender) {
        this.gender = gender;
    }

    public String getBirthnumber() {
        return birthnumber;
    }

    public void setBirthnumber(String birthnumber) {
        this.birthnumber = birthnumber;
    }

    public long getUpdated_at() {
        return updated_at;
    }

    public void setUpdated_at(long updated_at) {
        this.updated_at = updated_at;
    }
}
