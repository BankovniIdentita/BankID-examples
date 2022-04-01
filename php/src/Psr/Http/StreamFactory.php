<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use Exception;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Do not use outside of the project!
 */
class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return new Stream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        throw new Exception('Not supported');
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        throw new Exception('Not supported');
    }
}
