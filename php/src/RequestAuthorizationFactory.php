<?php

declare(strict_types=1);

namespace BankId\OIDC;

use BankId\OIDC\ClientAssertion\ClientAssertionFactory;

class RequestAuthorizationFactory
{
    public function __construct(
        private readonly Settings $settings,
        private readonly ClientAssertionFactory $clientAssertionFactory,
    ) {
    }

    /**
     * @return array<string,string>
     */
    public function create(): array
    {
        return match ($this->settings->authStrategy) {
            AuthStrategy::SignedWithBankIdSecret, AuthStrategy::SignedWithOwnJWK => $this->createWithAssertion(),
            default => $this->createWithPlainSecret(),
        };
    }

    /**
     * @return array<string,string>
     */
    private function createWithPlainSecret(): array
    {
        return [
            'client_id' => $this->settings->clientId,
            'client_secret' => $this->settings->clientSecret,
        ];
    }

    /**
     * @return array<string,string>
     */
    private function createWithAssertion(): array
    {
        $assertion = $this->clientAssertionFactory->create();

        return [
            'client_assertion_type' => $assertion->clientAssertionType,
            'client_assertion' => $assertion->clientAssertion,
        ];
    }
}
