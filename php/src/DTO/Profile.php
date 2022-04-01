<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class Profile
{
    /**
     * @param array<string>|null $nationalities
     * @param array<Address>|null $addresses
     * @param array<IdCard>|null $idCards
     * @param array<string>|null $paymentAccounts
     */
    public function __construct(
        public readonly string $sub,
        public readonly string $txn,
        public readonly VerifiedClaims $verifiedClaims,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
        public readonly ?string $gender,
        public readonly ?string $birthdate,
        public readonly ?string $birthnumber,
        public readonly ?int $age,
        public readonly ?bool $majority,
        public readonly ?string $dateOfDeath,
        public readonly ?string $birthplace,
        public readonly ?string $primaryNationality,
        public readonly ?array $nationalities,
        public readonly ?string $maritalstatus,
        public readonly ?string $email,
        public readonly ?string $phoneNumber,
        public readonly ?bool $pep,
        public readonly ?bool $limitedLegalCapacity,
        public readonly ?array $addresses,
        public readonly ?array $idCards,
        public readonly ?array $paymentAccounts,
        public readonly ?int $updatedAt,
    ) {
    }
}
