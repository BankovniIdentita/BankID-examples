<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\BankIdClient;
use BankId\OIDC\ClientAssertion\ClientAssertion;
use BankId\OIDC\ClientAssertion\ClientAssertionFactory;
use BankId\OIDC\Dependencies;
use BankId\OIDC\DTO\Address;
use BankId\OIDC\DTO\IdCard;
use BankId\OIDC\DTO\Profile;
use BankId\OIDC\DTO\TokenInfo;
use BankId\OIDC\DTO\TokenPair;
use BankId\OIDC\DTO\UserInfo;
use BankId\OIDC\DTO\Verification;
use BankId\OIDC\DTO\VerifiedClaims;
use BankId\OIDC\RequestAuthorizationFactory;
use BankId\OIDC\Settings;
use DateTimeImmutable;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;

class BankIdClientTest extends TestCase
{
    private BankIdClient $bankIdClient;

    /** @var MockObject&StreamInterface */
    private StreamInterface $body;

    /** @var MockObject&ResponseInterface */
    private ResponseInterface $response;

    /** @var MockObject&ClientInterface */
    private ClientInterface $httpClient;

    /** @var MockObject&ClientAssertionFactory */
    private ClientAssertionFactory $clientAssertionFactory;

    /** @var MockObject&RequestAuthorizationFactory */
    private RequestAuthorizationFactory $requestAuthorizationFactory;

    private int $statusCode = 200;

    protected function setUp(): void
    {
        parent::setUp();

        $this->body = $this->createMock(StreamInterface::class);

        /* @var MockObject&ResponseInterface $response */
        $this->response = $this->createMock(ResponseInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
        $this->response->method('getStatusCode')->willReturn($this->statusCode);

        $this->httpClient = $this->createMock(ClientInterface::class);

        $this->clientAssertionFactory = $this->createMock(ClientAssertionFactory::class);
        $this->clientAssertionFactory->method('create')->willReturn(new ClientAssertion('client:assertion:type', 'client-assertion'));

        $this->requestAuthorizationFactory = $this->createMock(RequestAuthorizationFactory::class);

        $this->bankIdClient = new BankIdClient(
            requestAuthorizationFactory: $this->requestAuthorizationFactory,
            dependencies: new Dependencies(
                httpClient: $this->httpClient,
                requestFactory: new HttpFactory(),
                streamFactory: new HttpFactory(),
                cache: $this->createMock(CacheInterface::class),
            ),
            settings: new Settings(
                bankIdBaseUri: 'https://bankid.cz',
                postLoginRedirectUri: 'https://acme.com',
                postLogoutRedirectUri: 'https://acme.com/logout',
                clientId: 'some-client-id',
                clientSecret: 'some-client-secret',
            ),
            tokenPair: new TokenPair(
                accessTokenString: 'access_token',
                idTokenString: 'id_token',
                scope: 'openid offline_access profile.addresses profile.birthdate',
                expiresAt: new DateTimeImmutable(date('Y-m-d', time() + 3600)),
                refreshTokenString: 'refresh_token',
            ),
        );
    }

    public function testGetProfile(): void
    {
        $this->body->method('getContents')->willReturn($this->getProfileResponse());

        $this->httpClient->expects(static::once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request): ResponseInterface {
                static::assertEquals('https://bankid.cz/profile', $request->getUri());
                static::assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                static::assertEquals('Bearer access_token', $request->getHeaderLine('Authorization'));

                return $this->response;
            });

        static::assertEquals(
            expected: new Profile(
                sub: '23f1ac00-5d54-4169-a288-794ae2ead0c4',
                txn: '6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.addresses',
                verifiedClaims: new VerifiedClaims(
                    verification: new Verification(
                        trustFramework: 'cz_aml',
                        verificationProcess: '45244782',
                    ),
                    claims: [
                        'given_name' => 'Jan',
                        'family_name' => 'Novák',
                        'birthdate' => '1970-08-01',
                        'gender' => 'male',
                        'addresses' => [
                            [
                                'type' => 'PERMANENT_RESIDENCE',
                                'street' => 'Olbrachtova',
                                'buildingapartment' => '1929',
                                'streetnumber' => '62',
                                'city' => 'Praha',
                                'zipcode' => '14000',
                                'country' => 'CZ',
                            ],
                        ],
                        'idcards' => [
                            [
                                'type' => 'ID',
                                'description' => 'Občanský průkaz',
                                'country' => 'CZ',
                                'number' => '123456789',
                                'valid_to' => '2023-10-11',
                                'issuer' => 'Úřad městské části Praha 4',
                                'issue_date' => '2020-01-28',
                            ],
                        ],
                    ],
                ),
                givenName: 'Jan',
                familyName: 'Novák',
                gender: 'male',
                birthdate: '1970-08-01',
                birthnumber: '7008010147',
                age: 50,
                majority: true,
                dateOfDeath: null,
                birthplace: 'Praha 4',
                primaryNationality: 'CZ',
                nationalities: [
                    'CZ',
                    'AT',
                    'SK',
                ],
                maritalstatus: 'MARRIED',
                email: 'J.novak@email.com',
                phoneNumber: '+420123456789',
                pep: false,
                limitedLegalCapacity: false,
                addresses: [
                    new Address(
                        type: 'PERMANENT_RESIDENCE',
                        street: 'Olbrachtova',
                        buildingapartment: '1929',
                        streetnumber: '62',
                        city: 'Praha',
                        zipcode: '14000',
                        country: 'CZ',
                        ruian_reference: '14458921',
                    ),
                ],
                idCards: [
                    new IdCard(
                        type: 'ID',
                        description: 'Občanský průkaz',
                        country: 'CZ',
                        number: '123456789',
                        validTo: '2023-10-11',
                        issuer: 'Úřad městské části Praha 4',
                        issueDate: '2020-01-28',
                    ),
                ],
                paymentAccounts: [
                    'CZ0708000000001019382023',
                ],
                updatedAt: 1568188433000,
            ),
            actual: $this->bankIdClient->getProfile(),
        );
    }

    public function testGetTokenInfo(): void
    {
        $this->requestAuthorizationFactory->expects(static::once())->method('create')->willReturn(['client_id' => 'some-client-id', 'client_secret' => 'some-client-secret']);
        $this->body->method('getContents')->willReturn($this->getTokenInfoResponse());

        $this->httpClient->expects(static::once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request): ResponseInterface {
                static::assertEquals('https://bankid.cz/token-info', $request->getUri());
                static::assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                static::assertEquals('token=access_token&client_id=some-client-id&client_secret=some-client-secret', $request->getBody()->getContents());

                return $this->response;
            });

        static::assertEquals(
            expected: new TokenInfo(
                active: true,
                scope: [
                    Scope::OpenId,
                    Scope::Address,
                ],
                clientId: 'd1bdc32e-1b06-4609-9f60-073685267f88',
                tokenType: 'access_token',
                exp: 1419356238,
                iat: 1419350238,
                sub: '25657805-66d4-4707-980a-f12429f17592',
                iss: 'https://bankid.cz/',
            ),
            actual: $this->bankIdClient->getTokenInfo(),
        );
    }

    public function testGetUserInfo(): void
    {
        $this->body->method('getContents')->willReturn($this->getUserInfoResponse());

        $this->httpClient->expects(static::once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request): ResponseInterface {
                static::assertEquals('https://bankid.cz/userinfo', $request->getUri());
                static::assertEquals('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                static::assertEquals('Bearer access_token', $request->getHeaderLine('Authorization'));

                return $this->response;
            });

        static::assertEquals(
            expected: new UserInfo(
                sub: '23f1ac00-5d54-4169-a288-794ae2ead0c4',
                txn: '6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.gender',
                verifiedClaims: new VerifiedClaims(
                    verification: new Verification(
                        trustFramework: 'cz_aml',
                        verificationProcess: '45244782',
                    ),
                    claims: [
                        'given_name' => 'Jan',
                        'family_name' => 'Novák',
                        'gender' => 'male',
                        'birthdate' => '1970-08-01',
                    ],
                ),
                name: 'Jan Novák',
                givenName: 'Jan',
                familyName: 'Novák',
                gender: 'male',
                birthdate: '1970-08-01',
                nickname: 'Fantomas',
                preferredUsername: 'JanN',
                email: 'j.novak@email.com',
                emailVerified: false,
                zoneinfo: 'Europe/Prague',
                locale: 'cs_CZ',
                phoneNumber: '+420123456789',
                phoneNumberVerified: true,
                updatedAt: 1568188433000,
            ),
            actual: $this->bankIdClient->getUserInfo(),
        );
    }

    private function getProfileResponse(): string
    {
        return '{
            "sub": "23f1ac00-5d54-4169-a288-794ae2ead0c4",
            "txn": "6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.addresses",
            "verified_claims": {
              "verification": {
                "trust_framework": "cz_aml",
                "verification_process": "45244782"
              },
              "claims": {
                "given_name": "Jan",
                "family_name": "Novák",
                "gender": "male",
                "birthdate": "1970-08-01",
                "addresses": [
                  {
                    "type": "PERMANENT_RESIDENCE",
                    "street": "Olbrachtova",
                    "buildingapartment": "1929",
                    "streetnumber": "62",
                    "city": "Praha",
                    "zipcode": "14000",
                    "country": "CZ"
                  }
                ],
                "idcards": [
                  {
                    "type": "ID",
                    "description": "Občanský průkaz",
                    "country": "CZ",
                    "number": "123456789",
                    "valid_to": "2023-10-11",
                    "issuer": "Úřad městské části Praha 4",
                    "issue_date": "2020-01-28"
                  }
                ]
              }
            },
            "given_name": "Jan",
            "family_name": "Novák",
            "gender": "male",
            "birthdate": "1970-08-01",
            "birthnumber": "7008010147",
            "age": 50,
            "majority": true,
            "date_of_death": null,
            "birthplace": "Praha 4",
            "primary_nationality": "CZ",
            "nationalities": [
              "CZ",
              "AT",
              "SK"
            ],
            "maritalstatus": "MARRIED",
            "email": "J.novak@email.com",
            "phone_number": "+420123456789",
            "pep": false,
            "limited_legal_capacity": false,
            "addresses": [
              {
                "type": "PERMANENT_RESIDENCE",
                "street": "Olbrachtova",
                "buildingapartment": "1929",
                "streetnumber": "62",
                "city": "Praha",
                "zipcode": "14000",
                "country": "CZ",
                "ruian_reference": "14458921"
              }
            ],
            "idcards": [
              {
                "type": "ID",
                "description": "Občanský průkaz",
                "country": "CZ",
                "number": "123456789",
                "valid_to": "2023-10-11",
                "issuer": "Úřad městské části Praha 4",
                "issue_date": "2020-01-28"
              }
            ],
            "paymentAccounts": [
              "CZ0708000000001019382023"
            ],
            "updated_at": 1568188433000
          }';
    }

    private function getTokenInfoResponse(): string
    {
        return '{
            "active": true,
            "scope": "openid profile.addresses",
            "client_id": "d1bdc32e-1b06-4609-9f60-073685267f88",
            "token_type": "access_token",
            "exp": 1419356238,
            "iat": 1419350238,
            "sub": "25657805-66d4-4707-980a-f12429f17592",
            "aud": "https://rp.example.com/resource",
            "iss": "https://bankid.cz/"
          }';
    }

    private function getUserInfoResponse(): string
    {
        return '{
            "sub": "23f1ac00-5d54-4169-a288-794ae2ead0c4",
            "txn": "6941683f-c6ee-410c-add0-d52d63091069:openid:profile.name:profile.gender",
            "verified_claims": {
              "verification": {
                "trust_framework": "cz_aml",
                "verification_process": "45244782"
              },
              "claims": {
                "given_name": "Jan",
                "family_name": "Novák",
                "gender": "male",
                "birthdate": "1970-08-01"
              }
            },
            "name": "Jan Novák",
            "given_name": "Jan",
            "family_name": "Novák",
            "gender": "male",
            "birthdate": "1970-08-01",
            "nickname": "Fantomas",
            "preferred_username": "JanN",
            "email": "j.novak@email.com",
            "email_verified": false,
            "zoneinfo": "Europe/Prague",
            "locale": "cs_CZ",
            "phone_number": "+420123456789",
            "phone_number_verified": true,
            "updated_at": 1568188433000
          }';
    }
}
