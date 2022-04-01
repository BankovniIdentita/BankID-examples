<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Dependencies;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;

class ConfigurationProviderTest extends TestCase
{
    private ConfigurationProvider $configurationProvider;

    /** @var MockObject&StreamInterface */
    private StreamInterface $body;

    private int $statusCode = 200;

    /** @var MockObject&CacheInterface */
    private CacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->body = $this->createMock(StreamInterface::class);

        /** @var MockObject&ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($this->body);
        $response->method('getStatusCode')->willReturn($this->statusCode);

        /** @var MockObject&ClientInterface $httpClient */
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $this->cache = $this->createMock(CacheInterface::class);

        $this->configurationProvider = new ConfigurationProvider(
            baseUri: 'some-base-uri',
            dependencies: new Dependencies(
                httpClient: $httpClient,
                requestFactory: $this->createMock(RequestFactoryInterface::class),
                streamFactory: $this->createMock(StreamFactoryInterface::class),
                cache: $this->cache,
            ),
        );
    }

    public function testGetAuthorizationEndpoint(): void
    {
        $this->body->method('getContents')->willReturn($this->getValidConfiguration());

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/auth',
            actual: $this->configurationProvider->getAuthorizationEndpoint(),
        );
    }

    public function testGetTokenExcangeEndpoint(): void
    {
        $this->body->method('getContents')->willReturn($this->getValidConfiguration());

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/token',
            actual: $this->configurationProvider->getTokenExcangeEndpoint(),
        );
    }

    public function testGetTokenEndpointSigningAlgos(): void
    {
        $this->body->method('getContents')->willReturn($this->getValidConfiguration());

        static::assertEquals(
            expected: [
                'HS256',
                'HS512',
                'RS256',
                'RS512',
                'PS512',
                'ES512',
            ],
            actual: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );
    }

    public function testGetIssuer(): void
    {
        $this->body->method('getContents')->willReturn($this->getValidConfiguration());

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/',
            actual: $this->configurationProvider->getIssuer(),
        );
    }

    public function testGetIssuerFromCache(): void
    {
        $this->body->expects(static::never())->method('getContents');
        $this->cache->expects(static::once())->method('get')->with('some-base-uri_config')->willReturn(['issuer' => 'https://oidc.sandbox.bankid.cz/']);
        $this->cache->expects(static::never())->method('set');

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/',
            actual: $this->configurationProvider->getIssuer(),
        );
    }

    public function testGetIssuerCallsCacheOnlyOnceDueToRuntimeCaching(): void
    {
        $this->body->expects(static::never())->method('getContents');
        $this->cache->expects(static::once())->method('get')->with('some-base-uri_config')->willReturn(['issuer' => 'https://oidc.sandbox.bankid.cz/']);
        $this->cache->expects(static::never())->method('set');

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/',
            actual: $this->configurationProvider->getIssuer(),
        );

        static::assertEquals(
            expected: 'https://oidc.sandbox.bankid.cz/',
            actual: $this->configurationProvider->getIssuer(),
        );
    }

    private function getValidConfiguration(): string
    {
        return '{
          "introspection_endpoint_auth_signing_alg_values_supported": [
            "HS256",
            "HS512",
            "RS256",
            "RS512",
            "PS512",
            "ES512"
        ],
            "request_parameter_supported": false,
            "authorize_endpoint": "https://oidc.sandbox.bankid.cz/auth",
            "claims_parameter_supported": false,
            "introspection_endpoint": "https://oidc.sandbox.bankid.cz/token-info",
            "profile_endpoint": "https://oidc.sandbox.bankid.cz/profile",
             "issuer": "https://oidc.sandbox.bankid.cz/",
            "id_token_encryption_enc_values_supported": [
            "A256GCM"
            ],
            "userinfo_encryption_enc_values_supported": [
            "A256GCM"
        ],
            "authorization_endpoint": "https://oidc.sandbox.bankid.cz/auth",
            "service_documentation": "https://developer.bankid.cz/docs",
            "introspection_endpoint_auth_methods_supported": [
            "client_secret_post",
            "client_secret_jwt",
            "private_key_jwt"
        ],
            "claims_supported": [
            "addresses.buildingapartment",
            "addresses.city",
            "addresses.country",
            "addresses.ruian_reference",
            "addresses.street",
            "addresses.streetnumber",
            "addresses.type",
            "addresses.zipcode",
            "age",
            "birthcountry",
            "birthdate",
            "birthnumber",
            "birthplace",
            "claims_updated",
            "date_of_death",
            "email",
            "email_verified",
            "family_name",
            "gender",
            "given_name",
            "idcards.country",
            "idcards.description",
            "idcards.issue_date",
            "idcards.issuer",
            "idcards.number",
            "idcards.type",
            "idcards.valid_to",
            "limited_legal_capacity",
            "locale",
            "majority",
            "maritalstatus",
            "middle_name",
            "name",
            "nationalities",
            "nickname",
            "paymentAccounts",
            "pep",
            "phone_number",
            "phone_number_verified",
            "preferred_username",
            "primary_nationality",
            "sub",
            "title_prefix",
            "title_suffix",
            "txn",
            "updated_at",
            "verified_claims.verification",
            "zoneinfo"
        ],
            "op_policy_uri": "https://developer.bankid.cz/documents/privacy-policy",
            "token_endpoint_auth_methods_supported": [
            "client_secret_post",
            "client_secret_jwt",
            "private_key_jwt"
        ],
            "response_modes_supported": [
            "query"
        ],
            "backchannel_logout_session_supported": false,
            "token_endpoint": "https://oidc.sandbox.bankid.cz/token",
            "response_types_supported": [
            "code",
            "token"
        ],
            "request_uri_parameter_supported": true,
            "grant_types_supported": [
            "authorization_code",
            "implicit",
            "refresh_token"
        ],
            "ui_locales_supported": [
            "cs"
        ],
            "userinfo_endpoint": "https://oidc.sandbox.bankid.cz/userinfo",
            "verification_endpoint": "https://oidc.sandbox.bankid.cz/verification",
            "op_tos_uri": "https://developer.bankid.cz/documents/terms-of-use",
            "ros_endpoint": "https://oidc.sandbox.bankid.cz/ros",
            "require_request_uri_registration": true,
            "code_challenge_methods_supported": [
            "plain",
            "S256"
        ],
            "id_token_encryption_alg_values_supported": [
            "RSA-OAEP",
            "RSA-OAEP-256",
            "ECDH-ES"
        ],
            "frontchannel_logout_session_supported": false,
            "claims_locales_supported": [
            "en",
            "en-US"
        ],
            "request_object_signing_alg_values_supported": [
            "PS512",
            "ES512"
        ],
            "request_object_encryption_alg_values_supported": [
            "RSA-OAEP",
            "RSA-OAEP-256",
            "ECDH-ES"
        ],
            "scopes_supported": [
            "openid",
            "offline_access",
            "profile.addresses",
            "profile.birthdate",
            "profile.birthnumber",
            "profile.birthplaceNationality",
            "profile.email",
            "profile.gender",
            "profile.idcards",
            "profile.legalstatus",
            "profile.locale",
            "profile.maritalstatus",
            "profile.name",
            "profile.paymentAccounts",
            "profile.phonenumber",
            "profile.titles",
            "profile.updatedat",
            "profile.zoneinfo",
            "profile.verification",
            "notification.claims_updated"
        ],
            "backchannel_logout_supported": true,
            "check_session_iframe": "https://oidc.sandbox.bankid.cz/session-iframe",
            "acr_values_supported": [
            "loa2",
            "loa3"
        ],
            "request_object_encryption_enc_values_supported": [
            "A256GCM"
        ],
            "display_values_supported": [
            "page"
        ],
            "profile_signing_alg_values_supported": [
            "PS512"
        ],
            "userinfo_signing_alg_values_supported": [
            "PS512"
        ],
            "profile_encryption_enc_values_supported": [
            "A256GCM"
        ],
            "userinfo_encryption_alg_values_supported": [
            "RSA-OAEP",
            "RSA-OAEP-256",
            "ECDH-ES"
        ],
            "end_session_endpoint": "https://oidc.sandbox.bankid.cz/logout",
            "token_endpoint_auth_signing_alg_values_supported": [
            "HS256",
            "HS512",
            "RS256",
            "RS512",
            "PS512",
            "ES512"
        ],
            "frontchannel_logout_supported": true,
            "profile_encryption_alg_values_supported": [
            "RSA-OAEP",
            "RSA-OAEP-256",
            "ECDH-ES"
        ],
            "jwks_uri": "https://oidc.sandbox.bankid.cz/.well-known/jwks",
            "subject_types_supported": [
            "public",
            "pairwise"
        ],
            "id_token_signing_alg_values_supported": [
            "PS512"
        ]
        }';
    }
}
