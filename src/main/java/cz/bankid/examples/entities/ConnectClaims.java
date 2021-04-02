package cz.bankid.examples.entities;

import cz.bankid.examples.entities.entity.Gender;

/**
 * Connect Product's verified claims.
 * Object that is the container for the verified Claims about the End-User.
 *
 * This is an element that will eventually be used by IDP in the future when the data will be verified, for example,
 * against state basic registers.
 */
public class ConnectClaims implements Claims {

    /**
     * End-User's full name in displayable form including all name parts, possibly including titles and suffixes,
     * ordered according to the End-User's locale and preferences.
     */
    String name;
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
     * present, with the names being separated by space characters. Also note that in some cultures, middle
     * names are not used.
     */
    String middle_name;
    /**
     * Casual name of the End-User that may or may not be the same as the given_name. For instance, a nickname value
     * of Mike might be returned alongside a given_name value of Michael.
     */
    String nickname;
    /**
     * Shorthand name by which the End-User wishes to be referred to at the RP, such as janedoe or j.doe.
     * This value MAY be any valid JSON string including special characters such as @, /, or whitespace.
     * The API consumer MUST NOT rely upon this value being unique.
     */
    String preferred_username;
    /**
     * End-User's preferred e-mail address. Its value MUST conform to the RFC 5322 [RFC5322] addr-spec syntax.
     * The API consumer MUST NOT rely upon this value being unique.
     */
    String email;
    /**
     * True if the End-User's e-mail address has been verified; otherwise false. When this Claim Value is true,
     * this means that the OP took affirmative steps to ensure that this e-mail address was controlled by the
     * End-User at the time the verification was performed. The means by which an e-mail address is verified is
     * context-specific, and dependent upon the trust framework or contractual agreements within which the parties
     * are operating.
     */
    boolean email_verified;
    /**
     * End-User's gender. Values defined by this specification are female and male. Other values MAY be used when
     * neither of the defined values are applicable.
     */
    Gender gender;
    /**
     * End-User's birthday, represented as an ISO 8601:2004 [ISO8601‑2004] YYYY-MM-DD format. The year MAY be 0000,
     * indicating that it is omitted. To represent only the year, YYYY format is allowed. Note that depending on the
     * underlying platform's date related function, providing just year can result in varying month and day, so
     * the implementers need to take this factor into account to correctly process the dates.
     */
    String birthdate;
    /**
     * String from zoneinfo [zoneinfo] time zone database representing the End-User's time zone. For example,
     * Europe/Paris or America/Los_Angeles.
     */
    String zoneinfo;
    /**
     * End-User's locale, represented as a BCP47 [RFC5646] language tag. This is typically an ISO 639-1 Alpha-2
     * [ISO639‑1] language code in lowercase and an ISO 3166-1 Alpha-2 [ISO3166‑1] country code in uppercase,
     * separated by a dash. For example, en-US or fr-CA. As a compatibility note, some implementations have used an
     * underscore as the separator rather than a dash, for example, en_US; Relying Parties MAY choose to accept
     * this locale syntax as well.
     */
    String locale;
    /**
     * End-User's preferred telephone number. E.164 [E.164] is RECOMMENDED as the format of this Claim, for
     * example, +1 (425) 555-1212 or +56 (2) 687 2400. If the phone number contains an extension, it is
     * RECOMMENDED that the extension be represented using the RFC 3966 [RFC3966] extension syntax,
     * for example, +1 (604) 555-1234;ext=5678.
     */
    String phone_number;
    /**
     * True if the End-User's phone number has been verified; otherwise false. When this Claim Value is true,
     * this means that the OP took affirmative steps to ensure that this phone number was controlled by the
     * End-User at the time the verification was performed. The means by which a phone number is verified is
     * context-specific, and dependent upon the trust framework or contractual agreements within which the
     * parties are operating. When true, the phone_number Claim MUST be in E.164 format and any extensions
     * MUST be represented in RFC 3966 format
     */
    boolean phone_number_verified;
    /**
     * Time the End-User's information was last updated. Its value is a JSON number representing the number
     * of seconds from 1970-01-01T0:0:0Z as measured in UTC until the date/time.
     */
    long updated_at;

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
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

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public String getPreferred_username() {
        return preferred_username;
    }

    public void setPreferred_username(String preferred_username) {
        this.preferred_username = preferred_username;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public boolean isEmail_verified() {
        return email_verified;
    }

    public void setEmail_verified(boolean email_verified) {
        this.email_verified = email_verified;
    }

    public Gender getGender() {
        return gender;
    }

    public void setGender(Gender gender) {
        this.gender = gender;
    }

    public String getBirthdate() {
        return birthdate;
    }

    public void setBirthdate(String birthdate) {
        this.birthdate = birthdate;
    }

    public String getZoneinfo() {
        return zoneinfo;
    }

    public void setZoneinfo(String zoneinfo) {
        this.zoneinfo = zoneinfo;
    }

    public String getLocale() {
        return locale;
    }

    public void setLocale(String locale) {
        this.locale = locale;
    }

    public String getPhone_number() {
        return phone_number;
    }

    public void setPhone_number(String phone_number) {
        this.phone_number = phone_number;
    }

    public boolean isPhone_number_verified() {
        return phone_number_verified;
    }

    public void setPhone_number_verified(boolean phone_number_verified) {
        this.phone_number_verified = phone_number_verified;
    }

    public long getUpdated_at() {
        return updated_at;
    }

    public void setUpdated_at(long updated_at) {
        this.updated_at = updated_at;
    }
}
