<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

class LogoutRequest
{
    public function __construct(
        public readonly string $uri,
        public readonly string $idTokenHint,
        public readonly string $postLogoutRedirectUri,
        public readonly string $sessionState,
    ) {
    }
}
