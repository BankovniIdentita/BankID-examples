package cz.bankid.examples.entities.entity;

/**
 * Id card type code:
 *
 * {@link ID} - Identity card.
 * {@link P} - Passport of the Czech Republic resident.
 * {@link DL} - Driving license
 * {@link IR} - Residence permit
 * {@link VS} - Visa permit label
 * {@link PS} - Residential label
 * {@link UNKNOWN} - Unknown id card type
 */
public enum IDCardType {
    ID,
    P,
    DL,
    IR,
    VS,
    PS,
    UNKNOWN
}
