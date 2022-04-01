<?php

declare(strict_types=1);

namespace BankId\OIDC\Discovery;

use BankId\OIDC\Dependencies;
use BankId\OIDC\Exception\NetworkException;

class KeysProvider
{
    /** @var array<array<string,mixed>>|null */
    private ?array $keys = null;

    public function __construct(
        private readonly string $baseUri,
        private readonly Dependencies $dependencies,
    ) {
    }

    /**
     * @return int seconds
     */
    protected function getTtl(): int
    {
        return 3600;
    }

    protected function getCacheKey(): string
    {
        return sprintf('%s_%s', $this->baseUri, 'keys');
    }

    /**
     * @return array<array<string,mixed>>
     */
    public function getKeys(): array
    {
        return $this->fetchKeys();
    }

    /**
     * @return array<array<string,mixed>>
     */
    public function fetchKeys(): array
    {
        if (null !== $this->keys) {
            return $this->keys;
        }

        /** @var array<array<string,mixed>>|null $cachedValue */
        $cachedValue = $this->dependencies->cache?->get(
            key: $this->getCacheKey(),
        );

        if (null !== $cachedValue) {
            $this->keys = $cachedValue;

            return $cachedValue;
        }

        $request = $this->dependencies->requestFactory
            ->createRequest('GET', $this->baseUri . '/.well-known/jwks');

        $response = $this->dependencies->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            throw new NetworkException(status: $response->getStatusCode(), text: $response->getBody()->getContents());
        }

        /** @var array<string,array<array<string,mixed>>> $result */
        $result = json_decode($response->getBody()->getContents(), true);

        $this->keys = $result['keys'];

        if (null !== $this->dependencies->cache) {
            $this->dependencies->cache->set(
                key: $this->getCacheKey(),
                value: $this->keys,
                ttl: $this->getTtl(),
            );
        }

        return $this->keys;
    }
}
