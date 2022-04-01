<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\DTO\LogoutRequest;
use BankId\OIDC\Tools\RandomStringGenerator;
use BankId\OIDC\UriBuilder\LogoutUriBuilder;
use PHPUnit\Framework\TestCase;

class LogoutUriBuilderTest extends TestCase
{
    public function testGetLogoutUri(): void
    {
        $randomStringGenerator = new class() implements RandomStringGenerator {
            public function generate(): string
            {
                return 'random-string';
            }
        };

        $logoutUriBuilder = new LogoutUriBuilder(
            randomStringGenerator: $randomStringGenerator,
            baseUri: 'https://bankid.cz',
            idToken: 'id-token',
            postLogoutRedirectUri: 'https://acme.com/logout',
            state: 'some-state',
        );

        static::assertEquals(
            expected: new LogoutRequest(
                uri: 'https://bankid.cz/logout',
                idTokenHint: 'id-token',
                postLogoutRedirectUri: 'https://acme.com/logout',
                sessionState: 'some-state',
            ),
            actual: $logoutUriBuilder->getLogoutRequest(),
        );
    }
}
