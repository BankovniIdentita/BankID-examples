<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Exception;
use Psr\Http\Message\UriInterface;

/**
 * @internal Do not use outside of the project!
 */
class Uri implements UriInterface
{
    public function __construct(private readonly string $uri)
    {
    }

    public function getScheme()
    {
        throw new Exception('Not implemented');
    }

    public function getAuthority()
    {
        throw new Exception('Not implemented');
    }

    public function getUserInfo()
    {
        throw new Exception('Not implemented');
    }

    public function getHost()
    {
        throw new Exception('Not implemented');
    }

    public function getPort()
    {
        throw new Exception('Not implemented');
    }

    public function getPath()
    {
        throw new Exception('Not implemented');
    }

    public function getQuery()
    {
        throw new Exception('Not implemented');
    }

    public function getFragment()
    {
        throw new Exception('Not implemented');
    }

    public function withScheme($scheme)
    {
        throw new Exception('Not implemented');
    }

    public function withUserInfo($user, $password = null)
    {
        throw new Exception('Not implemented');
    }

    public function withHost($host)
    {
        throw new Exception('Not implemented');
    }

    public function withPort($port)
    {
        throw new Exception('Not implemented');
    }

    public function withPath($path)
    {
        throw new Exception('Not implemented');
    }

    public function withQuery($query)
    {
        throw new Exception('Not implemented');
    }

    public function withFragment($fragment)
    {
        throw new Exception('Not implemented');
    }

    public function __toString()
    {
        return $this->uri;
    }
}
