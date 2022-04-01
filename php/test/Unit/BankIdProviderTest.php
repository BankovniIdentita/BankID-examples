<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\BankIdClient;
use BankId\OIDC\BankIdProvider;
use BankId\OIDC\ClientAssertion\ClientAssertionFactory;
use BankId\OIDC\Dependencies;
use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\DTO\Event;
use BankId\OIDC\DTO\TokenPair;
use BankId\OIDC\RequestAuthorizationFactory;
use BankId\OIDC\Settings;
use BankId\OIDC\Tools\RandomStringGenerator;
use BankId\OIDC\Tools\TimeProvider;
use BankId\OIDC\UriBuilder\AuthorizationUriBuilder;
use DateTimeImmutable;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;

class BankIdProviderTest extends TestCase
{
    /** @var MockObject&ClientInterface */
    private ClientInterface $httpClient;

    private RandomStringGenerator $randomStringGenerator;
    private RequestFactoryInterface $requestFactory;

    /** @var MockObject&StreamInterface */
    private StreamInterface $fakeRequest;

    /** @var MockObject&StreamFactoryInterface */
    private StreamFactoryInterface $streamFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->randomStringGenerator = new class() implements RandomStringGenerator {
            public function generate(): string
            {
                return 'some-state';
            }
        };

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = new HttpFactory();

        $this->fakeRequest = $this->createMock(StreamInterface::class);

        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->streamFactory->method('createStream')->willReturn($this->fakeRequest);
    }

    public function testCreateAuthUriBuilder(): void
    {
        $this->httpClient
            ->method('sendRequest')
            ->with(new Request(method: 'GET', uri: 'https://bankid.cz/.well-known/openid-configuration', headers: []))
            ->willReturn($this->getCertainResponse('https://bankid.cz/.well-known/openid-configuration'));

        $bankIdProvider = new BankIdProvider(
            settings: new Settings(
                bankIdBaseUri: 'https://bankid.cz',
                postLoginRedirectUri: 'https://acme.com',
                postLogoutRedirectUri: 'https://acme.com/logout',
                clientId: 'some-client-id',
                clientSecret: 'some-client-secret',
            ),
            dependencies: new Dependencies(
                httpClient: $this->httpClient,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                randomStringGenerator: $this->randomStringGenerator,
                cache: $this->createMock(CacheInterface::class),
            ),
        );

        static::assertEquals(
            expected: new AuthorizationUriBuilder(
                baseUri: 'https://bankid.cz',
                clientId: 'some-client-id',
                postLoginRedirectUri: 'https://acme.com',
                randomStringGenerator: $this->randomStringGenerator,
                state: 'some-state',
            ),
            actual: $bankIdProvider->createAuthUriBuilder(),
        );
    }

    public function testGetClient(): void
    {
        $this->httpClient->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                return $this->getCertainResponse((string) $request->getUri());
            });

        $timeProvider = new class() implements TimeProvider {
            public function now(): DateTimeImmutable
            {
                return DateTimeImmutable::createFromFormat('!Y-m-d', '2022-02-26'); // @phpstan-ignore-line
            }
        };

        $bankIdProvider = new BankIdProvider(
            settings: new Settings(
                bankIdBaseUri: 'https://bankid.cz',
                postLoginRedirectUri: 'https://acme.com',
                postLogoutRedirectUri: 'https://acme.com/logout',
                clientId: 'some-client-id',
                clientSecret: 'some-client-secret',
            ),
            dependencies: new Dependencies(
                httpClient: $this->httpClient,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                cache: $this->createMock(CacheInterface::class),
                randomStringGenerator: $this->randomStringGenerator,
                timeProvider: $timeProvider,
            ),
        );

        $client = $bankIdProvider->getClient('some-code');

        ($configurationProvider = new ConfigurationProvider(
            baseUri: 'https://bankid.cz',
            dependencies: new Dependencies(
                httpClient: $this->httpClient,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                cache: $this->createMock(CacheInterface::class),
                randomStringGenerator: $this->randomStringGenerator,
                timeProvider: $timeProvider,
            ),
        ))->getIssuer(); // to fill the inner cache value

        $settings = new Settings(
            bankIdBaseUri: 'https://bankid.cz',
            postLoginRedirectUri: 'https://acme.com',
            postLogoutRedirectUri: 'https://acme.com/logout',
            clientId: 'some-client-id',
            clientSecret: 'some-client-secret',
        );

        static::assertEquals(
            expected: new BankIdClient(
                requestAuthorizationFactory: new RequestAuthorizationFactory(
                    settings: $settings,
                    clientAssertionFactory: new ClientAssertionFactory(
                        settings: $settings,
                        configurationProvider: $configurationProvider,
                        randomStringGenerator: $this->randomStringGenerator,
                        timeProvider: $timeProvider,
                    ),
                ),
                dependencies: new Dependencies(
                    httpClient: $this->httpClient,
                    requestFactory: $this->requestFactory,
                    streamFactory: $this->streamFactory,
                    cache: $this->createMock(CacheInterface::class),
                    randomStringGenerator: $this->randomStringGenerator,
                    timeProvider: $timeProvider,
                ),
                settings: $settings,
                tokenPair: new TokenPair(
                    scope: 'openid profile.birthdate profile.addresses',
                    accessTokenString: 'eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1Mzk1NTc0LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6ImRkMTA5NGRjLThlNWItNDU3MC04Y2UxLTEyNmEyZDg4MGM2MiJ9.Ic2jf-kiI-x8bvIiqYwVhsxOaXzAGrmwsYxUh-vhsru4sqKaWG_6owU1SpQ9Z3tLDSbegWWcnlRE7jbgcJpCC1vKhz2yPZmP-nnRyqZonUgZG8l-CoGtM164r6PLVkCgZ2sfcB7d8kM41kWOzapNKzrev5px9NqIbWlmipsnF75NpWY2QgcRexMhIT3ej2HxnKrQWELc2X1EitPK5GNbDPBOybdUdg6SXjMKPZ-FrP_NW_b4XAmGfx74lbsvtQbXppgfZ6o-vJWxlFqjBysviDg1rfrRLwN8llDpFWN0QTAaBOaekIveEcJBsU_wqxx26E1HssEIMzpOllvw7j6lBg',
                    idTokenString: 'eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJhY3IiOiIxIiwic3ViIjoiMWVjN2MwNjMtZDYwMC00OTYxLThlYTUtN2E0MDdkY2M4NTI1IiwiYXVkIjoiNzJmZGEwMTEtMDQ3OS00YTRjLTlmZmYtMGE2YzdmNTg0ZTFlIiwiYXV0aF90aW1lIjoxNjQ1MzkxOTc2NDUxLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzkyNTc3LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6IjljYzYxMDk3LWMxY2ItNDZmZi1hNjdkLTZkNzgyMmJkOWJkMCJ9.TBsjHLWyRL_-PbJok5W2h1QgjkMd_xIHO83J0DlgrZe5odd5JtTmwUVUAX__TXmzTp7769_cIc8kpSz8GLWKpH2TBwaFAmIQuHIFB3zrmlbxaDc7Ei0jXFDLXZrH1ALAhfhTaKkRHg-ox3hnd6knLvupNxeqxZapeK70qyQRM9M4vAgmHW9_cI8Kx53jJxYmwXpRLHEiRMgCSYj-0RPjeeQwoL1IRH1EwZ4D4DSjfKcluf3hnrAJ9GBcEccE42LsCWn6q0Ga0n11DeEBByZtr8iwUJVmjTI_JG-Hyrn2KzZxwmhxtSBFQ9ZByrOsILy88qvHEJKBIcg7iS19f3C7bA',
                    expiresAt: new DateTimeImmutable(date(DateTimeImmutable::ATOM, time() + 3600)),
                    refreshTokenString: 'eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjc2OTI3OTc3LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6ImNmNzZiODQ5LTkxY2YtNDk4MS1hOGRkLTZkYjA5MWY5MTc4ZCJ9.ZJqu3jJGX5RhXPxUnTco8rGnrzeZR4MSBthRQh5lnmMXXVrczPQhUzIHKbiMkx6mH1Abbreu5hFVymolkE2KVKiPKa4LT0DSuf870UnYhGGU1jOw1cPHM7RozQpVptNe8KzLxXwviAlacLWaHK7r0GsOcm7yttdSqdNQhYxq3arqupbt_2gEqqi4C3FK2J31hCftONXbGtOVS6ixF5eEFhPqRfddu6BMcJ2JaJlqX8B5_biUqxZ8BYUOoRBfyZStcQFF_3ewInSkssfZyX8lfB3jfarYQW_kkk623Zqby5f13WfgUfm7Xopv7oo7fSNp5H4-hoxZmMOVwJttV_DtSA',
                ),
            ),
            actual: $client,
        );
    }

    public function testGetNotifications(): void
    {
        $this->httpClient->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                return $this->getCertainResponse((string) $request->getUri());
            });

        $bankIdProvider = new BankIdProvider(
            settings: new Settings(
                bankIdBaseUri: 'https://bankid.cz',
                postLoginRedirectUri: 'https://acme.com',
                postLogoutRedirectUri: 'https://acme.com/logout',
                clientId: 'some-client-id',
                clientSecret: 'some-client-secret',
            ),
            dependencies: new Dependencies(
                httpClient: $this->httpClient,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                cache: $this->createMock(CacheInterface::class),
                randomStringGenerator: $this->randomStringGenerator,
                timeProvider: $this->createMock(TimeProvider::class),
            ),
        );

        $events = $bankIdProvider->getNotifications('eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiaWF0IjoxNjQ4Mzg2MjMxLCJqdGkiOiI1YzY4ZjVmOS05MjE5LTQ1MjQtODQxMi0yNDRlNzM1MWFmYTUiLCJldmVudHMiOlt7InN1YiI6IjFlYzdjMDYzLWQ2MDAtNDk2MS04ZWE1LTdhNDA3ZGNjODUyNSIsIm9yaWdpbmFsX2V2ZW50X2F0IjoiMjAyMi0wMy0yN1QxMzowMzo1MFoiLCJhZmZlY3RlZF9jbGFpbXMiOlsiYWRkcmVzc2VzIiwiaWRjYXJkcyIsImVtYWlsIiwibmFtZSIsImZhbWlseV9uYW1lIl0sInR5cGUiOiJjbGFpbXNfdXBkYXRlZCIsImFmZmVjdGVkX2NsaWVudF9pZHMiOlsiZDU0NGVjN2UtNjM5MS00MGIwLWFmZTYtNjAxZWRlNGI0N2ZlIl19XX0.w90RJuB5TwB35THxSyQUwZX39zBMvJ6nabdFglihHPDAJFra4v0ZuGW_B2U2-0qlURUsa3PYXdeEZgqhhpW654ztKnjnPY7pxyWv1XNIIIRSzoBX5YjLxwXfKqyyTX3MZMLa2AmLWNNaJWKKlbHtxtUfYIwvKcUR0XGtY_TgVvGqsLDuwaoCUFhV1XZkTGu3kl2PAnumX0zbS4Z7Fsr5_zBXl0QxKxKvvryez4Z7OfsaSQHoT8b8sSoC94nVpPhSXoPB7flvsYJqntJ7vWLTb3fo8D-Gxfc3BUkeA0YgimVKkkoGb1v2RiYeG2GahyKs3Op6Y6bWMzIpZAWlswmRBQ');

        static::assertEquals(
            expected: [
                new Event(
                    sub: '1ec7c063-d600-4961-8ea5-7a407dcc8525',
                    originalEventAt: '2022-03-27T13:03:50Z',
                    affectedClaims: [
                        'addresses',
                        'idcards',
                        'email',
                        'name',
                        'family_name',
                    ],
                    type: 'claims_updated',
                    affectedClientIds: ['d544ec7e-6391-40b0-afe6-601ede4b47fe'],
                ),
            ],
            actual: $events,
        );
    }

    private function getCertainResponse(string $uri): ResponseInterface
    {
        /** @var MockObject&StreamInterface $body */
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn(self::RESPONSE_MAP[$uri]);

        /** @var MockObject&ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($body);

        return $response;
    }

    private const RESPONSE_MAP = [
        'https://bankid.cz/token' => '{
            "access_token": "eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiIxZWM3YzA2My1kNjAwLTQ5NjEtOGVhNS03YTQwN2RjYzg1MjUiLCJhenAiOiI3MmZkYTAxMS0wNDc5LTRhNGMtOWZmZi0wYTZjN2Y1ODRlMWUiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1Mzk1NTc0LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6ImRkMTA5NGRjLThlNWItNDU3MC04Y2UxLTEyNmEyZDg4MGM2MiJ9.Ic2jf-kiI-x8bvIiqYwVhsxOaXzAGrmwsYxUh-vhsru4sqKaWG_6owU1SpQ9Z3tLDSbegWWcnlRE7jbgcJpCC1vKhz2yPZmP-nnRyqZonUgZG8l-CoGtM164r6PLVkCgZ2sfcB7d8kM41kWOzapNKzrev5px9NqIbWlmipsnF75NpWY2QgcRexMhIT3ej2HxnKrQWELc2X1EitPK5GNbDPBOybdUdg6SXjMKPZ-FrP_NW_b4XAmGfx74lbsvtQbXppgfZ6o-vJWxlFqjBysviDg1rfrRLwN8llDpFWN0QTAaBOaekIveEcJBsU_wqxx26E1HssEIMzpOllvw7j6lBg",
            "token_type": "Bearer",
            "refresh_token": "eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjc2OTI3OTc3LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6ImNmNzZiODQ5LTkxY2YtNDk4MS1hOGRkLTZkYjA5MWY5MTc4ZCJ9.ZJqu3jJGX5RhXPxUnTco8rGnrzeZR4MSBthRQh5lnmMXXVrczPQhUzIHKbiMkx6mH1Abbreu5hFVymolkE2KVKiPKa4LT0DSuf870UnYhGGU1jOw1cPHM7RozQpVptNe8KzLxXwviAlacLWaHK7r0GsOcm7yttdSqdNQhYxq3arqupbt_2gEqqi4C3FK2J31hCftONXbGtOVS6ixF5eEFhPqRfddu6BMcJ2JaJlqX8B5_biUqxZ8BYUOoRBfyZStcQFF_3ewInSkssfZyX8lfB3jfarYQW_kkk623Zqby5f13WfgUfm7Xopv7oo7fSNp5H4-hoxZmMOVwJttV_DtSA",
            "expires_in": 3600,
            "scope": "openid profile.birthdate profile.addresses",
            "id_token": "eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJhY3IiOiIxIiwic3ViIjoiMWVjN2MwNjMtZDYwMC00OTYxLThlYTUtN2E0MDdkY2M4NTI1IiwiYXVkIjoiNzJmZGEwMTEtMDQ3OS00YTRjLTlmZmYtMGE2YzdmNTg0ZTFlIiwiYXV0aF90aW1lIjoxNjQ1MzkxOTc2NDUxLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjQ1MzkyNTc3LCJpYXQiOjE2NDUzOTE5NzcsImp0aSI6IjljYzYxMDk3LWMxY2ItNDZmZi1hNjdkLTZkNzgyMmJkOWJkMCJ9.TBsjHLWyRL_-PbJok5W2h1QgjkMd_xIHO83J0DlgrZe5odd5JtTmwUVUAX__TXmzTp7769_cIc8kpSz8GLWKpH2TBwaFAmIQuHIFB3zrmlbxaDc7Ei0jXFDLXZrH1ALAhfhTaKkRHg-ox3hnd6knLvupNxeqxZapeK70qyQRM9M4vAgmHW9_cI8Kx53jJxYmwXpRLHEiRMgCSYj-0RPjeeQwoL1IRH1EwZ4D4DSjfKcluf3hnrAJ9GBcEccE42LsCWn6q0Ga0n11DeEBByZtr8iwUJVmjTI_JG-Hyrn2KzZxwmhxtSBFQ9ZByrOsILy88qvHEJKBIcg7iS19f3C7bA"
        }',
        'https://bankid.cz/.well-known/openid-configuration' => '{
            "introspection_endpoint_auth_signing_alg_values_supported": [
                "HS256",
                "HS512",
                "RS256",
                "RS512",
                "PS512",
                "ES512"
            ],
            "request_parameter_supported": false,
            "authorize_endpoint": "https://bankid.cz/auth",
            "claims_parameter_supported": false,
            "introspection_endpoint": "https://bankid.cz/token-info",
            "profile_endpoint": "https://bankid.cz/profile",
            "issuer": "https://oidc.sandbox.bankid.cz/",
            "id_token_encryption_enc_values_supported": [
                "A256GCM"
            ],
            "userinfo_encryption_enc_values_supported": [
                "A256GCM"
            ],
            "authorization_endpoint": "https://bankid.cz/auth",
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
            "token_endpoint": "https://bankid.cz/token",
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
            "userinfo_endpoint": "https://bankid.cz/userinfo",
            "verification_endpoint": "https://bankid.cz/verification",
            "op_tos_uri": "https://developer.bankid.cz/documents/terms-of-use",
            "ros_endpoint": "https://bankid.cz/ros",
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
            "check_session_iframe": "https://bankid.cz/session-iframe",
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
            "end_session_endpoint": "https://bankid.cz/logout",
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
            "jwks_uri": "https://bankid.cz/.well-known/jwks",
            "subject_types_supported": [
                "public",
                "pairwise"
            ],
            "id_token_signing_alg_values_supported": [
                "PS512"
            ]
        }',
        'https://bankid.cz/.well-known/jwks' => '{
            "keys": [
                {
                "kty": "RSA",
                "x5t#S256": "fYowjlnVtUVM3EvJahDnIBjZITeS2SK-9zeE4j3iZ-w",
                "e": "AQAB",
                "use": "enc",
                "kid": "rp-encrypt",
                "x5c": [
                    "MIIElTCCBBugAwIBAgICECwwCgYIKoZIzj0EAwMwfjELMAkGA1UEBhMCQ1oxDjAMBgNVBAgMBVByYWhhMSAwHgYDVQQKDBdCYW5rb3ZuaSBpZGVudGl0YSwgYS5zLjEdMBsGA1UEAwwUQmFua0lEIFByb2R1Y3Rpb24gQ0ExHjAcBgkqhkiG9w0BCQEWD2FkbWluQGJhbmtpZC5jejAeFw0yMTAzMTgwNzUwMDJaFw0yNDAzMTcwNzUwMDJaMFsxCzAJBgNVBAYTAkNaMQ4wDAYDVQQIDAVQcmFoYTEgMB4GA1UECgwXQmFua292bmkgaWRlbnRpdGEsIGEucy4xGjAYBgNVBAMMEUJhbmtJRCBwcm9kdWN0aW9uMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAxFLhcDDXnkdcO7CV1gjm4pXu60VFVuVKdYazZ+Bv1EXZ8I6NNQ/yrS0fysyLdaeNEwTrQ2rhb2BjuaR9aOvrPdhFlS2yKZ+k4+wkWeioc6t3jZvb9fJvKpCxozMU8XwC/OVO81G3Az5Gyv/nAGCzNmHRsXUiJBA9gh5OVduBJyAZN6w7s8F4A+QQlSdbMkVduHpUqGlGbvDDZ0zpssJQv2pA3i6y3mfAEPccr75Vgx/le9+6PC/e7BaZFUY/BdP6KmesitPZgD6EACP/QUh21jHn0feGDV+nGkZswPxZp3FCEz6YnkZg24/C6JHOjUee/gATjjjUC+uxpVPLuUGjR+Rf0WMmczMec3LJTfXwhx33ai6nQ02vp8UUGzjfSzF0UiztrWJQ9pRgc4o95h4npcLO+n7uh3NVR2/nHtBPEYGvxxZyX50Ux8HibaHEKZvoQARQ6/MTKgo0FpjGd0G97BxB5FKxw7WwiSLI9USQuDubnE3xqnQMsgJcAlg2HcQkCMu5P+6H2mer9l3wm127KFDHaZeUvV8feEBX6juz4kguQwwtZg/Op1/Hbjh/+pRvUCnbj+erjLzX4Y1rwYZlTlg3QRTaTbxV+Qhfv5gO7ZTlXSvyCIWhKnUYc8EGT1VpKDhoOdzVM23VT5m9plZKZQsyrMJMD1DP15sh2Tj1/8ECAwEAAaOB4DCB3TAJBgNVHRMEAjAAMBEGCWCGSAGG+EIBAQQEAwIGQDAzBglghkgBhvhCAQ0EJhYkQmFua0lEIFByb2R1Y3Rpb24gQ2xpZW50IENlcnRpZmljYXRlMB0GA1UdDgQWBBRzRJstwoejw003aJ6fquk9rsU1QDAfBgNVHSMEGDAWgBQqoi9yTXXY0beUgU8zj/QtExL35jALBgNVHQ8EBAMCBBAwOwYDVR0fBDQwMjAwoC6gLIYqaHR0cHM6Ly9jYS5iYW5raWQuY3ovY3JsL3Byb2QvcHJvZC5jcmwuY3JsMAoGCCqGSM49BAMDA2gAMGUCMQCDv5oUXSpGdQFgSD9QPzl6pqTRX2zMeFT4OPj3IKSJPrdEi7A4iPTjWs9r2dm9ngsCMEwCMeFbc3iIA6H+iZGDEgls4pOJQAn5qNq1td9VQijqw+XSeGMkwYmtV/SvRlOyyw=="
                ],
                "n": "xFLhcDDXnkdcO7CV1gjm4pXu60VFVuVKdYazZ-Bv1EXZ8I6NNQ_yrS0fysyLdaeNEwTrQ2rhb2BjuaR9aOvrPdhFlS2yKZ-k4-wkWeioc6t3jZvb9fJvKpCxozMU8XwC_OVO81G3Az5Gyv_nAGCzNmHRsXUiJBA9gh5OVduBJyAZN6w7s8F4A-QQlSdbMkVduHpUqGlGbvDDZ0zpssJQv2pA3i6y3mfAEPccr75Vgx_le9-6PC_e7BaZFUY_BdP6KmesitPZgD6EACP_QUh21jHn0feGDV-nGkZswPxZp3FCEz6YnkZg24_C6JHOjUee_gATjjjUC-uxpVPLuUGjR-Rf0WMmczMec3LJTfXwhx33ai6nQ02vp8UUGzjfSzF0UiztrWJQ9pRgc4o95h4npcLO-n7uh3NVR2_nHtBPEYGvxxZyX50Ux8HibaHEKZvoQARQ6_MTKgo0FpjGd0G97BxB5FKxw7WwiSLI9USQuDubnE3xqnQMsgJcAlg2HcQkCMu5P-6H2mer9l3wm127KFDHaZeUvV8feEBX6juz4kguQwwtZg_Op1_Hbjh_-pRvUCnbj-erjLzX4Y1rwYZlTlg3QRTaTbxV-Qhfv5gO7ZTlXSvyCIWhKnUYc8EGT1VpKDhoOdzVM23VT5m9plZKZQsyrMJMD1DP15sh2Tj1_8E"
                },
                {
                "kty": "EC",
                "x5t#S256": "TnaGIMHLKjxvfx4EQGrXOueG9c8Fk2nlyGsTBVBK2Tw",
                "use": "sig",
                "crv": "P-384",
                "kid": "mtls",
                "x5c": [
                    "MIIDETCCApagAwIBAgICECowCgYIKoZIzj0EAwMwfjELMAkGA1UEBhMCQ1oxDjAMBgNVBAgMBVByYWhhMSAwHgYDVQQKDBdCYW5rb3ZuaSBpZGVudGl0YSwgYS5zLjEdMBsGA1UEAwwUQmFua0lEIFByb2R1Y3Rpb24gQ0ExHjAcBgkqhkiG9w0BCQEWD2FkbWluQGJhbmtpZC5jejAeFw0yMTAzMDQwNTA1NDdaFw0yMjAzMDQwNTA1NDdaMGExCzAJBgNVBAYTAkNaMQ4wDAYDVQQIDAVQcmFoYTEgMB4GA1UECgwXQmFua292bmkgaWRlbnRpdGEsIGEucy4xIDAeBgNVBAMMF0Jhbmtvdm5pIGlkZW50aXRhLCBhLnMuMHYwEAYHKoZIzj0CAQYFK4EEACIDYgAEG6vPecxuqTN92+g6mFqrLoov7IWt599QUQ23j7oxY4ZmBAMAz2KM7zULau/+X0SPDk9A4mx6nwOfjL4SP1ysEziu5ScLvd4O4v8ql2UA2cFxIqFcLAEpPnWYiSUN2v26o4IBAjCB/zAJBgNVHRMEAjAAMBEGCWCGSAGG+EIBAQQEAwIFoDAzBglghkgBhvhCAQ0EJhYkQmFua0lEIFByb2R1Y3Rpb24gQ2xpZW50IENlcnRpZmljYXRlMB0GA1UdDgQWBBRAQ7pl/4tw0JgPsfAYOdAB1vcAsjAfBgNVHSMEGDAWgBQqoi9yTXXY0beUgU8zj/QtExL35jAOBgNVHQ8BAf8EBAMCBeAwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMEMDsGA1UdHwQ0MDIwMKAuoCyGKmh0dHBzOi8vY2EuYmFua2lkLmN6L2NybC9wcm9kL3Byb2QuY3JsLmNybDAKBggqhkjOPQQDAwNpADBmAjEA+zx8xKUGv3jS7JjXgxVrxX4ZxCSHd2A7GmLCp0YdS42+X0a0/xP+30nWEd0YG7o1AjEAqJHcip1H0HV3uqw+B5AtssGEH//lt+N5MZZkNWSa8WYtbOunXentBaqOfUmGRqqT"
                ],
                "x": "G6vPecxuqTN92-g6mFqrLoov7IWt599QUQ23j7oxY4ZmBAMAz2KM7zULau_-X0SP",
                "y": "Dk9A4mx6nwOfjL4SP1ysEziu5ScLvd4O4v8ql2UA2cFxIqFcLAEpPnWYiSUN2v26"
                },
                {
                "kty": "RSA",
                "x5t#S256": "VOAJMMCpfJDYdRW1uE_9_Fw8pBA1HJcqmQq_4xFRuWc",
                "e": "AQAB",
                "use": "sig",
                "kid": "rp-sign",
                "x5c": [
                    "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"
                ],
                "n": "xBeRT-xgvxR40MEmJWN-9EPyRl_MxaiWoKAfCG16iM0W0lyBqdej2QKSX70rh6di3YupbNyZeD5Yij037_VPOrrdyyk2Oirw2xx1-aWS6B3T9GXD_Vmfb3k6kw6A9nl4mlDbV7MuWS_fqHldv2QTFCPxX0AQ2ohZJrJiLD3Rbm93W14tzYQUEixUh1YamrtR-iTGHSHOxdTNHqk1r_waTBh-GJiYPxNMd3BruLZrv0cHlH3NdxG-Ge37Rgg6_sI3OEp8qmVul8cazVM4nth1vWLBTeEDY2vmANfZ119vPAaAvDw1vxgI5XBDGR6QZCKF8ifx5bfA5fxEQhLfEtC_lQ"
                }
            ]
            }',
    ];
}
