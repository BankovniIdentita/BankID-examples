<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\AuthStrategy;
use BankId\OIDC\ClientAssertion\ClientAssertion;
use BankId\OIDC\ClientAssertion\ClientAssertionFactory;
use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Settings;
use BankId\OIDC\Tools\RandomStringGenerator;
use BankId\OIDC\Tools\TimeProvider;
use DateTimeImmutable;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Easy\JWT;
use Jose\Easy\Load;
use Jose\Easy\Validate;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientAssertionFactoryTest extends TestCase
{
    /** @var MockObject&ConfigurationProvider */
    private ConfigurationProvider $configurationProvider;

    private RandomStringGenerator $randomStringGenerator;

    private TimeProvider $timeProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationProvider = $this->createMock(ConfigurationProvider::class);
        $this->randomStringGenerator = new class() implements RandomStringGenerator {
            public function generate(): string
            {
                return 'random-string';
            }
        };
        $this->timeProvider = new class() implements TimeProvider {
            public function now(): DateTimeImmutable
            {
                return DateTimeImmutable::createFromFormat('!Y-m-d', '2022-02-26'); //@phpstan-ignore-line
            }
        };
    }

    public function testCreateFromClientSecret(): void
    {
        $settings = new Settings(
            bankIdBaseUri: 'https://bankid.cz',
            postLoginRedirectUri: 'https://acme.com',
            postLogoutRedirectUri: 'https://acme.com/logout',
            clientId: 'some-client-id',
            clientSecret: 'afc707e6-46c9-4472-8894-e57cd97fd29b-42bd165c-79cf-4318-ba42-e66506f4983e',
            authStrategy: AuthStrategy::SignedWithBankIdSecret,
            jwk: null,
        );

        $this->configurationProvider->expects(static::once())->method('getTokenExcangeEndpoint')->willReturn('token-exchange-ep');

        $clientAssertionFactory = new ClientAssertionFactory(
            settings: $settings,
            configurationProvider: $this->configurationProvider,
            randomStringGenerator: $this->randomStringGenerator,
            timeProvider: $this->timeProvider,
        );

        $assertion = $clientAssertionFactory->create();

        static::assertInstanceOf(ClientAssertion::class, $assertion);
        static::assertEquals('urn:ietf:params:oauth:client-assertion-type:jwt-bearer', $assertion->clientAssertionType);

        /** @var Validate $jwsValidation */
        $jwsValidation = Load::jws($assertion->clientAssertion)
            ->algs(['HS512'])
            ->iss('some-client-id')
            ->key(JWKFactory::createFromSecret($settings->clientSecret, ['alg' => 'HS512', 'use' => 'sig']));

        $jwt = $jwsValidation->run();

        static::assertInstanceOf(JWT::class, $jwt);
        static::assertEquals(
            expected: [
                'jti' => 'random-string',
                'sub' => 'some-client-id',
                'iss' => 'some-client-id',
                'aud' => [
                    'token-exchange-ep',
                ],
                'exp' => 1645837200,
                'iat' => 1645833600,
            ],
            actual: $jwt->claims->all(),
        );
    }

    public function testCreateFromOwnJwk(): void
    {
        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS512', 'use' => 'sig']);

        $settings = new Settings(
            bankIdBaseUri: 'https://bankid.cz',
            postLoginRedirectUri: 'https://acme.com',
            postLogoutRedirectUri: 'https://acme.com/logout',
            clientId: 'some-client-id',
            clientSecret: 'afc707e6-46c9-4472-8894-e57cd97fd29b-42bd165c-79cf-4318-ba42-e66506f4983e',
            authStrategy: AuthStrategy::SignedWithOwnJWK,
            jwk: $jwk,
        );

        $this->configurationProvider->expects(static::once())->method('getTokenExcangeEndpoint')->willReturn('token-exchange-ep');

        $clientAssertionFactory = new ClientAssertionFactory(
            settings: $settings,
            configurationProvider: $this->configurationProvider,
            randomStringGenerator: $this->randomStringGenerator,
            timeProvider: $this->timeProvider,
        );

        $assertion = $clientAssertionFactory->create();

        static::assertInstanceOf(ClientAssertion::class, $assertion);
        static::assertEquals('urn:ietf:params:oauth:client-assertion-type:jwt-bearer', $assertion->clientAssertionType);

        /** @var Validate $jwsValidation */
        $jwsValidation = Load::jws($assertion->clientAssertion)
            ->algs(['RS512'])
            ->iss('some-client-id')
            ->key($jwk);

        $jwt = $jwsValidation->run();

        static::assertInstanceOf(JWT::class, $jwt);
        static::assertEquals(
            expected: [
                'jti' => 'random-string',
                'sub' => 'some-client-id',
                'iss' => 'some-client-id',
                'aud' => [
                    'token-exchange-ep',
                ],
                'exp' => 1645837200,
                'iat' => 1645833600,
            ],
            actual: $jwt->claims->all(),
        );
    }

    public function testCreationWithPlainSecretStrategyIsNotAllowed(): void
    {
        $clientAssertionFactory = new ClientAssertionFactory(
            settings: new Settings(
                bankIdBaseUri: 'https://bankid.cz',
                postLoginRedirectUri: 'https://acme.com',
                postLogoutRedirectUri: 'https://acme.com/logout',
                clientId: 'some-client-id',
                clientSecret: 'afc707e6-46c9-4472-8894-e57cd97fd29b-42bd165c-79cf-4318-ba42-e66506f4983e',
                authStrategy: AuthStrategy::PlainSecret,
                jwk: null,
            ),
            configurationProvider: $this->createMock(ConfigurationProvider::class),
            randomStringGenerator: $this->createMock(RandomStringGenerator::class),
            timeProvider: $this->createMock(TimeProvider::class),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You don\'t need to create client_assertion with currect AuthStrategy');

        $clientAssertionFactory->create();
    }
}
