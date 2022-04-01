<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class UserInfo
{
    public function __construct(
        public readonly string $sub,
        public readonly string $txn,
        public readonly VerifiedClaims $verifiedClaims,
        public readonly ?string $name,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
        public readonly ?string $gender,
        public readonly ?string $birthdate,
        public readonly ?string $nickname,
        public readonly ?string $preferredUsername,
        public readonly ?string $email,
        public readonly ?bool $emailVerified,
        public readonly ?string $zoneinfo,
        public readonly ?string $locale,
        public readonly ?string $phoneNumber,
        public readonly ?bool $phoneNumberVerified,
        public readonly ?int $updatedAt,
    ) {
    }
}
