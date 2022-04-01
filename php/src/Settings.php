<?php

declare(strict_types=1);

namespace BankId\OIDC;

use Jose\Component\Core\JWK;

final class Settings
{
    public readonly string $bankIdBaseUri;

    public function __construct(
        string $bankIdBaseUri,
        public readonly string $postLoginRedirectUri,
        public readonly string $postLogoutRedirectUri,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly AuthStrategy $authStrategy = AuthStrategy::PlainSecret,
        public readonly ?JWK $jwk = null,
    ) {
        $this->bankIdBaseUri = trim($bankIdBaseUri, '/');
    }
}
