<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\AuthorizationParameters\AcrValue;
use BankId\OIDC\AuthorizationParameters\CodeChallengeMethod;
use BankId\OIDC\AuthorizationParameters\ResponseType;
use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\UriBuilder\AuthorizationUriBuilder;
use BankId\OIDC\Tools\RandomStringGenerator;
use PHPUnit\Framework\TestCase;

class AuthorizationUriBuilderTest extends TestCase
{
    protected AuthorizationUriBuilder $authorizationUriBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $randomStringGenerator = new class() implements RandomStringGenerator {
            public function generate(): string
            {
                return 'some-state';
            }
        };

        $this->authorizationUriBuilder = new AuthorizationUriBuilder(
            baseUri: 'https://bankid.cz',
            clientId: 'xxxx-client-id',
            postLoginRedirectUri: 'https://acme.com',
            randomStringGenerator: $randomStringGenerator,
        );
    }

    public function testWithScope(): void
    {
        $authUriBuiler = $this->authorizationUriBuilder->withScope(Scope::Address, Scope::BirthNumber, Scope::Email, Scope::IdCards);

        static::assertEquals(
            //scope=openid+profile.addresses+profile.birthnumber+profile.email+profile.idcards
            expected: 'https://bankid.cz/auth?approval_prompt=auto&scope=openid+profile.addresses+profile.birthnumber+profile.email+profile.idcards&code_challenge_method=plain&response_type=code&acr_value=loa2&state=some-state&client_id=xxxx-client-id&redirect_uri=https%3A%2F%2Facme.com',
            actual: $authUriBuiler->getAuthorizationUri(),
        );
    }

    public function testWithResponseType(): void
    {
        $authUriBuidler = $this->authorizationUriBuilder->withResponseType(ResponseType::Token);

        static::assertStringContainsString(
            needle: 'response_type=token',
            haystack: $authUriBuidler->getAuthorizationUri(),
        );

        static::assertEquals(
            expected: 'https://bankid.cz/auth?approval_prompt=auto&scope=openid&code_challenge_method=plain&response_type=token&acr_value=loa2&state=some-state&client_id=xxxx-client-id&redirect_uri=https%3A%2F%2Facme.com',
            actual: $authUriBuidler->getAuthorizationUri(),
        );
    }

    public function testWithCodeChallengeMethod(): void
    {
        $authUriBuidler = $this->authorizationUriBuilder->withCodeChallengeMethod(CodeChallengeMethod::S256);

        static::assertStringContainsString(
            needle: 'code_challenge_method=S256',
            haystack: $authUriBuidler->getAuthorizationUri(),
        );

        static::assertEquals(
            expected: 'https://bankid.cz/auth?approval_prompt=auto&scope=openid&code_challenge_method=S256&response_type=code&acr_value=loa2&state=some-state&client_id=xxxx-client-id&redirect_uri=https%3A%2F%2Facme.com',
            actual: $authUriBuidler->getAuthorizationUri(),
        );
    }

    public function testWithAcrValue(): void
    {
        $authUriBuidler = $this->authorizationUriBuilder->withAcrValue(AcrValue::Loa3);

        static::assertStringContainsString(
            needle: 'acr_value=loa3',
            haystack: $authUriBuidler->getAuthorizationUri(),
        );

        static::assertEquals(
            expected: 'https://bankid.cz/auth?approval_prompt=auto&scope=openid&code_challenge_method=plain&response_type=code&acr_value=loa3&state=some-state&client_id=xxxx-client-id&redirect_uri=https%3A%2F%2Facme.com',
            actual: $authUriBuidler->getAuthorizationUri(),
        );
    }

    public function testGetState(): void
    {
        static::assertEquals(
            expected: 'some-state',
            actual: $this->authorizationUriBuilder->getState(),
        );
    }
}
