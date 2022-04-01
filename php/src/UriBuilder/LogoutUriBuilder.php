<?php

declare(strict_types=1);

namespace BankId\OIDC\UriBuilder;

use BankId\OIDC\DTO\LogoutRequest;
use BankId\OIDC\Tools\RandomStringGenerator;

class LogoutUriBuilder
{
    private readonly string $state;

    public function __construct(
        private readonly RandomStringGenerator $randomStringGenerator,
        private readonly string $baseUri,
        private readonly string $idToken,
        private readonly string $postLogoutRedirectUri,
        ?string $state = null,
    ) {
        $this->state = $state ?? $this->randomStringGenerator->generate();
    }

    public function getLogoutRequest(): LogoutRequest
    {
        return new LogoutRequest(
            uri: $this->baseUri . '/logout',
            idTokenHint: $this->idToken,
            postLogoutRedirectUri: $this->postLogoutRedirectUri,
            sessionState: $this->state,
        );
    }
}
