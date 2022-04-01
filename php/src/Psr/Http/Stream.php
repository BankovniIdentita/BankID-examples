<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Exception;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Do not use outside of the project!
 */
class Stream implements StreamInterface
{
    public function __construct(private readonly string $stream)
    {
    }

    public function __toString()
    {
        return $this->stream;
    }

    public function close()
    {
        throw new Exception('Not implemented');
    }

    public function detach()
    {
        throw new Exception('Not implemented');
    }

    public function getSize()
    {
        throw new Exception('Not implemented');
    }

    public function tell()
    {
        throw new Exception('Not implemented');
    }

    public function eof()
    {
        throw new Exception('Not implemented');
    }

    public function isSeekable()
    {
        throw new Exception('Not implemented');
    }

    public function seek($offset, $whence = SEEK_SET): never
    {
        throw new Exception('Not implemented');
    }

    public function rewind(): never
    {
        throw new Exception('Not implemented');
    }

    public function isWritable()
    {
        throw new Exception('Not implemented');
    }

    public function write($string)
    {
        throw new Exception('Not implemented');
    }

    public function isReadable()
    {
        throw new Exception('Not implemented');
    }

    public function read($length)
    {
        throw new Exception('Not implemented');
    }

    public function getContents()
    {
        return (string) $this;
    }

    public function getMetadata($key = null)
    {
        throw new Exception('Not implemented');
    }
}
