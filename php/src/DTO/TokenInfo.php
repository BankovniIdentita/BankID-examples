<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

use BankId\OIDC\AuthorizationParameters\Scope;

final class TokenInfo
{
    /**
     * @param array<Scope> $scope
     */
    public function __construct(
        public readonly bool $active,
        public readonly array $scope,
        public readonly string $clientId,
        public readonly string $tokenType,
        public readonly int $exp,
        public readonly int $iat,
        public readonly string $sub,
        public readonly string $iss,
    ) {
    }
}
