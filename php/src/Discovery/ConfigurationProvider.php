<?php

declare(strict_types=1);

namespace BankId\OIDC\Discovery;

use BankId\OIDC\Dependencies;
use BankId\OIDC\Exception\NetworkException;
use LogicException;

class ConfigurationProvider
{
    /** @var array<string,mixed>|null */
    private ?array $configuration = null;

    public function __construct(
        private readonly string $baseUri,
        private readonly Dependencies $dependencies,
    ) {
    }

    public function getAuthorizationEndpoint(): string
    {
        /** @var string|null $authorizationEndpoint */
        $authorizationEndpoint = $this->getConfiguration()['authorization_endpoint'] ?? null;

        if (null === $authorizationEndpoint) {
            throw new LogicException('authorization_endpoint key is missing from configration discovery data');
        }

        return $authorizationEndpoint;
    }

    public function getTokenExcangeEndpoint(): string
    {
        /** @var string|null $tokenEndpoint */
        $tokenEndpoint = $this->getConfiguration()['token_endpoint'] ?? null;

        if (null === $tokenEndpoint) {
            throw new LogicException('token_endpoint key is missing from configration discovery data');
        }

        return $tokenEndpoint;
    }

    /**
     * @return array<string>
     */
    public function getTokenEndpointSigningAlgos(): array
    {
        /** @var array<string>|null $algos */
        $algos = $this->getConfiguration()['token_endpoint_auth_signing_alg_values_supported'] ?? null;

        if (null === $algos) {
            throw new LogicException('token_endpoint_auth_signing_alg_values_supported key is missing from configration discovery data');
        }

        return $algos;
    }

    public function getIssuer(): string
    {
        /** @var string|null $issuer */
        $issuer = $this->getConfiguration()['issuer'] ?? null;

        if (null === $issuer) {
            throw new LogicException('issuer key is missing from configration discovery data');
        }

        return $issuer;
    }

    protected function getCacheKey(): string
    {
        return sprintf('%s_%s', $this->baseUri, 'config');
    }

    protected function getTtl(): int
    {
        return 3600;
    }

    /**
     * @return array<string,mixed>
     */
    private function getConfiguration(): array
    {
        if (null !== $this->configuration) {
            return $this->configuration;
        }

        /** @var array<array<string,mixed>>|null $cachedValue */
        $cachedValue = $this->dependencies->cache?->get(
            key: $this->getCacheKey(),
        );

        if (null !== $cachedValue) {
            $this->configuration = $cachedValue;

            return $cachedValue;
        }

        $request = $this->dependencies->requestFactory
            ->createRequest('GET', $this->baseUri . '/.well-known/openid-configuration');

        $response = $this->dependencies->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            throw new NetworkException(status: $response->getStatusCode(), text: $response->getBody()->getContents());
        }

        /** @var array<string,mixed> $config */
        $config = json_decode($response->getBody()->getContents(), true);

        $this->configuration = $config;

        if (null !== $this->dependencies->cache) {
            $this->dependencies->cache->set(
                key: $this->getCacheKey(),
                value: $this->configuration,
                ttl: $this->getTtl(),
            );
        }

        return $this->configuration;
    }
}
