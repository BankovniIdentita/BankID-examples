<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @internal Do not use outside of the project!
 */
class Request implements RequestInterface
{
    /**
     * @param array<string,array<string>> $headers
     */
    public function __construct(
        private string $method,
        private UriInterface $uri,
        private array $headers,
        private StreamInterface $body,
    ) {
    }

    public function getRequestTarget()
    {
        throw new Exception('Not implemented');
    }

    public function withRequestTarget($requestTarget)
    {
        throw new Exception('Not implemented');
    }

    public function getMethod()
    {
        return strtolower($this->method);
    }

    public function withMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;

        return $this;
    }

    public function getProtocolVersion()
    {
        throw new Exception('Not implemented');
    }

    public function withProtocolVersion($version)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @return array<string,array<string>>
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        throw new Exception('Not implemented');
    }

    public function getHeader($name)
    {
        throw new Exception('Not implemented');
    }

    public function getHeaderLine($name)
    {
        throw new Exception('Not implemented');
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] ??= [];
        array_push($this->headers[$name], $value);

        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        throw new Exception('Not implemented');
    }

    public function withoutHeader($name)
    {
        throw new Exception('Not implemented');
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        $this->body = $body;

        return $this;
    }
}
