<?php

declare(strict_types=1);

namespace BankId\OIDC\ClientAssertion;

use BankId\OIDC\AuthStrategy;
use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Settings;
use BankId\OIDC\Tools\RandomStringGenerator;
use BankId\OIDC\Tools\TimeProvider;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Easy\Build;
use Jose\Easy\JWSBuilder;
use LogicException;

class ClientAssertionFactory
{
    private const CLIENT_JWT_BEARER_ASSERTION = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
    private const ALG = 'HS512';

    public function __construct(
        private readonly Settings $settings,
        private readonly ConfigurationProvider $configurationProvider,
        private readonly RandomStringGenerator $randomStringGenerator,
        private readonly TimeProvider $timeProvider,
    ) {
    }

    public function create(): ClientAssertion
    {
        $jwk = match ($this->settings->authStrategy) {
            AuthStrategy::SignedWithBankIdSecret => $this->createDefaultJWK(),
            AuthStrategy::SignedWithOwnJWK => $this->settings->jwk ?? throw new LogicException('Self-issued JWK is required in order to use current AuthStrategy.'),
            default => throw new LogicException('You don\'t need to create client_assertion with currect AuthStrategy'),
        };

        $time = $this->timeProvider->now();

        /** @var JWSBuilder $jwsBuilder */
        $jwsBuilder = Build::jws()
            ->jti($this->randomStringGenerator->generate())
            ->sub($this->settings->clientId)
            ->iss($this->settings->clientId)
            ->aud($this->configurationProvider->getTokenExcangeEndpoint())
            ->exp($time->getTimestamp() + 3600)
            ->iat($time->getTimestamp())
            ->header('alg', AuthStrategy::SignedWithOwnJWK === $this->settings->authStrategy ? $jwk->get('alg') : self::ALG);

        $jws = $jwsBuilder->sign($jwk);

        return new ClientAssertion(
            clientAssertionType: self::CLIENT_JWT_BEARER_ASSERTION,
            clientAssertion: $jws,
        );
    }

    private function createDefaultJWK(): JWK
    {
        return JWKFactory::createFromSecret(
            $this->settings->clientSecret,
            [
                'alg' => self::ALG,
                'use' => 'sig',
            ],
        );
    }
}
