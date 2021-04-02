package cz.bankid.examples.entities.entity;

/**
 * Type of address element as an ENUM:
 *
 * {@link PERMANENT_RESIDENCE} - permanent residence address
 * {@link SECONDARY_RESIDENCE} - secondary residence address
 * {@link UNKNOWN} - unknown address type
 */
public enum AddressType {
    /**
     * Permanent residence address
     */
    PERMANENT_RESIDENCE,
    /**
     * Secondary residence address
     */
    SECONDARY_RESIDENCE,
    /**
     * Unknown address type
     */
    UNKNOWN
}
