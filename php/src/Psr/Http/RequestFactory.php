<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal Do not use outside of the project!
 */
class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request(
            method: $method,
            uri: is_string($uri) ? new Uri($uri) : $uri,
            body: new Stream(''),
            headers: [],
        );
    }
}
