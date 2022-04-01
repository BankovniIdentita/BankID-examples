<?php

declare(strict_types=1);

namespace BankId\OIDC;

use BankId\OIDC\Psr\Http\Client;
use BankId\OIDC\Psr\Http\RequestFactory;
use BankId\OIDC\Psr\Http\StreamFactory;
use BankId\OIDC\Tools\RandomBytesRandomStringGenerator;
use BankId\OIDC\Tools\RandomStringGenerator;
use BankId\OIDC\Tools\SplTimeProvider;
use BankId\OIDC\Tools\TimeProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;

class Dependencies
{
    public readonly RandomStringGenerator $randomStringGenerator;
    public readonly TimeProvider $timeProvider;
    public readonly ClientInterface $httpClient;
    public readonly RequestFactoryInterface $requestFactory;
    public readonly StreamFactoryInterface $streamFactory;

    public function __construct(
        public readonly ?CacheInterface $cache = null,
        ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null,
        ?RandomStringGenerator $randomStringGenerator = null,
        ?TimeProvider $timeProvider = null,
    ) {
        $this->httpClient = $httpClient ?? new Client();
        $this->requestFactory = $requestFactory ?? new RequestFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
        $this->randomStringGenerator = $randomStringGenerator ?? new RandomBytesRandomStringGenerator();
        $this->timeProvider = $timeProvider ?? new SplTimeProvider();
    }
}
