<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Do not use outside of the project!
 */
class Response implements ResponseInterface
{
    /**
     * @param array<string,array<string>> $headers
     */
    public function __construct(
        private int $statusCode,
        private array $headers,
        private StreamInterface $body,
    ) {
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = $code;

        return $this;
    }

    public function getReasonPhrase()
    {
        throw new Exception('Not implemented');
    }

    public function getProtocolVersion()
    {
        throw new Exception('Not implemented');
    }

    public function withProtocolVersion($version)
    {
        throw new Exception('Not implemented');
    }

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
