<?php

declare(strict_types=1);

namespace BankId\OIDC\Exception;

use RuntimeException;

class NetworkException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $text,
    ) {
        parent::__construct('Network error: ' . $status, $status, null);
    }
}
