<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\AuthStrategy;
use BankId\OIDC\ClientAssertion\ClientAssertion;
use BankId\OIDC\ClientAssertion\ClientAssertionFactory;
use BankId\OIDC\RequestAuthorizationFactory;
use BankId\OIDC\Settings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestAuthorizationFactoryTest extends TestCase
{
    /** @var MockObject&ClientAssertionFactory */
    private ClientAssertionFactory $clientAssertionFactory;

    protected function setUp(): void
    {
        $this->clientAssertionFactory = $this->createMock(ClientAssertionFactory::class);
    }

    public function testClientSecretHmac(): void
    {
        $settings = new Settings(
            bankIdBaseUri: 'https://bankid.cz',
            postLoginRedirectUri: 'https://acme.com',
            postLogoutRedirectUri: 'https://acme.com/logout',
            clientId: 'some-client-id',
            clientSecret: base64_encode(random_bytes(64)),
            authStrategy: AuthStrategy::SignedWithBankIdSecret,
            jwk: null,
        );

        $this->clientAssertionFactory->expects(static::once())
            ->method('create')
            ->willReturn(new ClientAssertion('some-assertion-type', 'some-assertion'));

        $requestAuthorizationFactory = new RequestAuthorizationFactory(
            settings: $settings,
            clientAssertionFactory: $this->clientAssertionFactory,
        );

        static::assertEquals(
            expected: [
                'client_assertion_type' => 'some-assertion-type',
                'client_assertion' => 'some-assertion',
            ],
            actual: $requestAuthorizationFactory->create(),
        );
    }

    public function testPlainSecret(): void
    {
        $settings = new Settings(
            bankIdBaseUri: 'https://bankid.cz',
            postLoginRedirectUri: 'https://acme.com',
            postLogoutRedirectUri: 'https://acme.com/logout',
            clientId: 'some-client-id',
            clientSecret: '4ae7aac2-bd5d-4913-8dd7-176088c7aa80-addbcfce-2722-413a-b85e-3f3b168b7cbe',
            authStrategy: AuthStrategy::PlainSecret,
            jwk: null,
        );

        $this->clientAssertionFactory->expects(static::never())->method('create');

        $requestAuthorizationFactory = new RequestAuthorizationFactory(
            settings: $settings,
            clientAssertionFactory: $this->clientAssertionFactory,
        );

        static::assertEquals(
            expected: [
                'client_id' => 'some-client-id',
                'client_secret' => '4ae7aac2-bd5d-4913-8dd7-176088c7aa80-addbcfce-2722-413a-b85e-3f3b168b7cbe',
            ],
            actual: $requestAuthorizationFactory->create(),
        );
    }
}
