<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class Verification
{
    public function __construct(
        public readonly string $trustFramework,
        public readonly string $verificationProcess,
    ) {
    }
}
