<?php

declare(strict_types=1);

namespace BankId\OIDC\Exception;

class AuthenticationException extends NetworkException
{
    public function __construct(
        public readonly string $text,
    ) {
    }
}
