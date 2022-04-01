<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class IdCard
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?string $description,
        public readonly ?string $country,
        public readonly ?string $number,
        public readonly ?string $validTo,
        public readonly ?string $issuer,
        public readonly ?string $issueDate,
    ) {
    }
}
