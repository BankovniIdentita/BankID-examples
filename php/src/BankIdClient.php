<?php

declare(strict_types=1);

namespace BankId\OIDC;

use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\DTO\Address;
use BankId\OIDC\DTO\IdCard;
use BankId\OIDC\DTO\Profile;
use BankId\OIDC\DTO\TokenInfo;
use BankId\OIDC\DTO\TokenPair;
use BankId\OIDC\DTO\UserInfo;
use BankId\OIDC\DTO\Verification;
use BankId\OIDC\DTO\VerifiedClaims;
use BankId\OIDC\Exception\AuthenticationException;
use BankId\OIDC\Exception\NetworkException;
use Psr\Http\Message\ResponseInterface;

class BankIdClient
{
    public function __construct(
        private readonly RequestAuthorizationFactory $requestAuthorizationFactory,
        private readonly Dependencies $dependencies,
        private readonly Settings $settings,
        private readonly TokenPair $tokenPair,
    ) {
    }

    public function getTokenPair(): TokenPair
    {
        return $this->tokenPair;
    }

    public function getProfile(): Profile
    {
        $request = $this->dependencies->requestFactory
            ->createRequest('GET', $this->settings->bankIdBaseUri . '/profile')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Bearer ' . $this->tokenPair->accessTokenString);

        $response = $this->dependencies->httpClient->sendRequest($request);

        $this->assertStatusCodeIs200($response);

        /** @var array{
         *  sub: string,
         *  txn: string,
         *  verified_claims: array{
         *      verification: array{
         *          trust_framework: string,
         *          verification_process: string,
         *      }|null,
         *      claims: array<string,mixed>,
         *  },
         *  given_name: string|null,
         *  family_name: string|null,
         *  gender: string|null,
         *  birthdate: string|null,
         *  birthnumber: string|null,
         *  age: int|null,
         *  majority: bool|null,
         *  date_of_death: string|null,
         *  birthplace: string|null,
         *  primary_nationality: string|null,
         *  nationalities: array<string>|null,
         *  maritalstatus: string|null,
         *  email: string|null,
         *  phone_number: string|null,
         *  pep: bool|null,
         *  limited_legal_capacity: bool|null,
         *  addresses: array<array{
         *      type: string|null,
         *      street: string|null,
         *      buildingapartment: string|null,
         *      streetnumber: string|null,
         *      city: string|null,
         *      zipcode: string|null,
         *      country: string|null,
         *      ruian_reference: string|null,
         *  }>|null,
         *  idcards: array<array{
         *      type: string|null,
         *      description: string|null,
         *      country: string|null,
         *      number: string|null,
         *      valid_to: string|null,
         *      issuer: string|null,
         *      issue_date: string|null,
         *  }>|null,
         *  paymentAccounts: array<string>|null,
         *  updated_at: int|null,
         * } $result
         */
        $result = json_decode($response->getBody()->getContents(), true);

        $verification = null;

        if (isset($result['verified_claims']['verification'])) {
            $verification = new Verification(
                trustFramework: $result['verified_claims']['verification']['trust_framework'],
                verificationProcess: $result['verified_claims']['verification']['verification_process'],
            );
        }

        $addresses = null;

        if (isset($result['addresses'])) {
            $addresses = array_map(
                callback: fn (array $rawAddress): Address => new Address(
                    type: $rawAddress['type'] ?? null,
                    street: $rawAddress['street'] ?? null,
                    buildingapartment: $rawAddress['buildingapartment'] ?? null,
                    streetnumber: $rawAddress['streetnumber'] ?? null,
                    city: $rawAddress['city'] ?? null,
                    zipcode: $rawAddress['zipcode'] ?? null,
                    country: $rawAddress['country'] ?? null,
                    ruian_reference: $rawAddress['ruian_reference'] ?? null,
                ),
                array: $result['addresses'],
            );
        }

        $idCards = null;

        if (isset($result['idcards'])) {
            $idCards = array_map(
                callback: fn (array $rawIdCard): IdCard => new IdCard(
                    type: $rawIdCard['type'] ?? null,
                    description: $rawIdCard['description'] ?? null,
                    country: $rawIdCard['country'] ?? null,
                    number: $rawIdCard['number'] ?? null,
                    validTo: $rawIdCard['valid_to'] ?? null,
                    issuer: $rawIdCard['issuer'] ?? null,
                    issueDate: $rawIdCard['issue_date'] ?? null,
                ),
                array: $result['idcards'],
            );
        }

        return new Profile(
            sub: $result['sub'],
            txn: $result['txn'],
            verifiedClaims: new VerifiedClaims(
                verification: $verification,
                claims: $result['verified_claims']['claims'],
            ),
            givenName: $result['given_name'] ?? null,
            familyName: $result['family_name'] ?? null,
            gender: $result['gender'] ?? null,
            birthdate: $result['birthdate'] ?? null,
            birthnumber: $result['birthnumber'] ?? null,
            age: $result['age'] ?? null,
            majority: $result['majority'] ?? null,
            dateOfDeath: $result['date_of_death'] ?? null,
            birthplace: $result['birthplace'] ?? null,
            primaryNationality: $result['primary_nationality'] ?? null,
            nationalities: $result['nationalities'] ?? null,
            maritalstatus: $result['maritalstatus'] ?? null,
            email: $result['email'] ?? null,
            phoneNumber: $result['phone_number'] ?? null,
            pep: $result['pep'] ?? null,
            limitedLegalCapacity: $result['limited_legal_capacity'] ?? null,
            addresses: $addresses,
            idCards: $idCards,
            paymentAccounts: $result['paymentAccounts'] ?? null,
            updatedAt: $result['updated_at'] ?? null,
        );
    }

    public function getTokenInfo(): TokenInfo
    {
        $requestAuthorization = $this->requestAuthorizationFactory->create();

        $postFields = http_build_query([
            'token' => $this->tokenPair->accessTokenString,
            ...$requestAuthorization,
        ]);

        $request = $this->dependencies->requestFactory
            ->createRequest('POST', $this->settings->bankIdBaseUri . '/token-info')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Bearer ' . $this->tokenPair->accessTokenString)
            ->withBody(
                body: $this->dependencies->streamFactory->createStream($postFields),
            );

        $response = $this->dependencies->httpClient->sendRequest($request);

        $this->assertStatusCodeIs200($response);

        /** @var array{
         *  active: bool,
         *  scope: string,
         *  client_id: string,
         *  token_type: string,
         *  exp: int,
         *  iat: int,
         *  sub: string,
         *  aud: string,
         *  iss: string,
         * } $result */
        $result = json_decode($response->getBody()->getContents(), true);

        return new TokenInfo(
            active: $result['active'],
            scope: array_map(
                fn (string $scope): Scope => Scope::from($scope),
                explode(' ', $result['scope']),
            ),
            clientId: $result['client_id'],
            tokenType: $result['token_type'],
            exp: $result['exp'],
            iat: $result['iat'],
            sub: $result['sub'],
            iss: $result['iss'],
        );
    }

    public function getUserInfo(): UserInfo
    {
        $request = $this->dependencies->requestFactory
            ->createRequest('GET', $this->settings->bankIdBaseUri . '/userinfo')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'Bearer ' . $this->tokenPair->accessTokenString);

        $response = $this->dependencies->httpClient->sendRequest($request);

        $this->assertStatusCodeIs200($response);

        /**
         * @var array{
         * sub: string,
         * txn: string,
         * verified_claims: array{
         *   verification: array{
         *     trust_framework: string,
         *     verification_process: string,
         *   }|null,
         *   claims: array<string,string>
         * },
         * name: string|null,
         * given_name: string|null,
         * family_name: string|null,
         * gender: string|null,
         * birthdate: string|null,
         * nickname: string|null,
         * preferred_username: string|null,
         * email: string|null,
         * email_verified: bool|null,
         * zoneinfo: string|null,
         * locale: string|null,
         * phone_number: string|null,
         * phone_number_verified: bool|null,
         * updated_at: int|null,
         * } $result
         */
        $result = json_decode($response->getBody()->getContents(), true);

        $verification = null;

        if (isset($result['verified_claims']['verification'])) {
            $verification = new Verification(
                trustFramework: $result['verified_claims']['verification']['trust_framework'],
                verificationProcess: $result['verified_claims']['verification']['verification_process'],
            );
        }

        return new UserInfo(
            sub: $result['sub'],
            txn: $result['txn'],
            verifiedClaims: new VerifiedClaims(
                verification: $verification,
                claims: $result['verified_claims']['claims'],
            ),
            name: $result['name'] ?? null,
            givenName: $result['given_name'] ?? null,
            familyName: $result['family_name'] ?? null,
            gender: $result['gender'] ?? null,
            birthdate: $result['birthdate'] ?? null,
            nickname: $result['nickname'] ?? null,
            preferredUsername: $result['preferred_username'] ?? null,
            email: $result['email'] ?? null,
            emailVerified: $result['email_verified'] ?? null,
            zoneinfo: $result['zoneinfo'] ?? null,
            locale: $result['locale'] ?? null,
            phoneNumber: $result['phone_number'] ?? null,
            phoneNumberVerified: $result['phone_number_verified'] ?? null,
            updatedAt: $result['updated_at'] ?? null,
        );
    }

    private function assertStatusCodeIs200(ResponseInterface $response): void
    {
        if (200 !== $response->getStatusCode()) {
            if (401 === $response->getStatusCode()) {
                throw new AuthenticationException($response->getBody()->getContents());
            }

            throw new NetworkException(status: $response->getStatusCode(), text: $response->getBody()->getContents());
        }
    }
}
