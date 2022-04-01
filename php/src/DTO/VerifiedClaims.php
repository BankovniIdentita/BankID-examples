<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class VerifiedClaims
{
    /**
     * @param array<string,mixed> $claims
     */
    public function __construct(
        public readonly ?Verification $verification,
        public readonly array $claims,
    ) {
    }
}
