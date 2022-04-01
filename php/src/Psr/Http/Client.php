<?php

declare(strict_types=1);

namespace BankId\OIDC\Psr\Http;

use BankId\OIDC\Psr\Http\Exception\HttpException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal Do not use outside of the project!
 */
class Client implements ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return match ($request->getMethod()) {
            'post' => $this->sendPostRequest($request),
            default => $this->sendGetRequest($request),
        };
    }

    private function sendGetRequest(RequestInterface $request): ResponseInterface
    {
        $ch = curl_init();

        $headersAssoc = array_combine(
            array_keys($request->getHeaders()),
            array_map(
                fn (array $headerLines): string => implode('; ', $headerLines),
                $request->getHeaders(),
            ),
        );

        $headers = array_map(
            fn (string $key, string $value): string => $key . ': ' . $value,
            array_keys($headersAssoc),
            array_values($headersAssoc),
        );

        curl_setopt($ch, CURLOPT_URL, $request->getUri());
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (!is_string($response)) {
            throw new HttpException('Failed to access the address ' . $request->getMethod() . ' ' . $request->getUri());
        }

        /** @var int $statusCode */
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /** @var int $headerSize */
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headersString = substr($response, 0, $headerSize);

        $body = substr($response, $headerSize);

        /** @var array<string,array<string>> $headers */
        $headers = array_map(
            fn (string $headerLine): array => explode('; ', $headerLine),
            explode("\n", $headersString),
        );

        return new Response(
            statusCode: $statusCode,
            headers: $headers,
            body: new Stream($body),
        );
    }

    private function sendPostRequest(RequestInterface $request): ResponseInterface
    {
        $ch = curl_init();

        $headersAssoc = array_combine(
            array_keys($request->getHeaders()),
            array_map(
                fn (array $headerLines): string => implode('; ', $headerLines),
                $request->getHeaders(),
            ),
        );

        $headers = array_map(
            fn (string $key, string $value): string => $key . ': ' . $value,
            array_keys($headersAssoc),
            array_values($headersAssoc),
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getBody());
        curl_setopt($ch, CURLOPT_URL, $request->getUri());
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (!is_string($response)) {
            throw new HttpException('Failed to access the address ' . $request->getMethod() . ' ' . $request->getUri());
        }

        /** @var int $statusCode */
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /** @var int $headerSize */
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headersString = substr($response, 0, $headerSize);

        $body = substr($response, $headerSize);

        $headersPlainArray = explode("\n", $headersString);
        $headersPlainArray = array_filter(
            $headersPlainArray,
            fn (string $headersLine): bool => str_contains($headersLine, ':'),
        );

        $headers = [];
        foreach ($headersPlainArray as $headerLine) {
            $headerExploded = explode(': ', $headerLine);
            if (count($headerExploded) < 2) {
                continue;
            }

            $headers[$headerExploded[0]] = explode('; ', $headerExploded[1]);
        }

        return new Response(
            statusCode: $statusCode,
            headers: $headers,
            body: new Stream($body),
        );
    }
}
