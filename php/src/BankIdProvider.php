<?php

declare(strict_types=1);

namespace BankId\OIDC;

use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\ClientAssertion\ClientAssertionFactory;
use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Discovery\KeysProvider;
use BankId\OIDC\DTO\Event;
use BankId\OIDC\DTO\TokenPair;
use BankId\OIDC\Exception\NetworkException;
use BankId\OIDC\Tools\JwtValidator;
use BankId\OIDC\UriBuilder\AuthorizationUriBuilder;
use BankId\OIDC\UriBuilder\LogoutUriBuilder;
use DateTimeImmutable;
use LogicException;

class BankIdProvider
{
    private readonly ConfigurationProvider $configurationProvider;
    private readonly KeysProvider $keysProvider;
    private readonly JwtValidator $jwtValidator;
    private readonly ClientAssertionFactory $clientAssertionFactory;
    private readonly RequestAuthorizationFactory $requestAuthorizationFactory;
    private readonly Dependencies $dependencies;

    public function __construct(
        private readonly Settings $settings,
        ?Dependencies $dependencies = null,
    ) {
        $this->dependencies = $dependencies ?? new Dependencies();

        $this->configurationProvider = new ConfigurationProvider(
            baseUri: $settings->bankIdBaseUri,
            dependencies: $this->dependencies,
        );

        $this->keysProvider = new KeysProvider(
            baseUri: $settings->bankIdBaseUri,
            dependencies: $this->dependencies,
        );

        $this->jwtValidator = new JwtValidator(
            configurationProvider: $this->configurationProvider,
            keysProvider: $this->keysProvider,
        );

        $this->clientAssertionFactory = new ClientAssertionFactory(
            settings: $this->settings,
            configurationProvider: $this->configurationProvider,
            randomStringGenerator: $this->dependencies->randomStringGenerator,
            timeProvider: $this->dependencies->timeProvider,
        );

        $this->requestAuthorizationFactory = new RequestAuthorizationFactory(
            settings: $this->settings,
            clientAssertionFactory: $this->clientAssertionFactory,
        );
    }

    public function createAuthUriBuilder(): AuthorizationUriBuilder
    {
        return new AuthorizationUriBuilder(
            baseUri: $this->settings->bankIdBaseUri,
            clientId: $this->settings->clientId,
            postLoginRedirectUri: $this->settings->postLoginRedirectUri,
            randomStringGenerator: $this->dependencies->randomStringGenerator,
        );
    }

    public function createLogoutUriBuilder(string $idToken): LogoutUriBuilder
    {
        return new LogoutUriBuilder(
            randomStringGenerator: $this->dependencies->randomStringGenerator,
            baseUri: $this->settings->bankIdBaseUri,
            idToken: $idToken,
            postLogoutRedirectUri: $this->settings->postLogoutRedirectUri,
        );
    }

    public function getClient(string $code): BankIdClient
    {
        $requestAuthorization = $this->requestAuthorizationFactory->create();

        $postFields = http_build_query([
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->settings->postLoginRedirectUri,
            'code' => $code,
            ...$requestAuthorization,
        ]);

        $request = $this->dependencies->requestFactory
            ->createRequest('POST', $this->configurationProvider->getTokenExcangeEndpoint())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                body: $this->dependencies->streamFactory->createStream($postFields),
            );

        $response = $this->dependencies->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            throw new NetworkException(status: $response->getStatusCode(), text: $response->getBody()->getContents());
        }

        /** @var array<string,string> $result */
        $result = json_decode($response->getBody()->getContents(), true);

        $this->jwtValidator->validate(
            jwt: $result['access_token'],
            expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );

        $this->jwtValidator->validate(
            jwt: $result['id_token'],
            expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );

        if (isset($result['refresh_token'])) {
            $this->jwtValidator->validate(
                jwt: $result['refresh_token'],
                expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
            );
        }

        return new BankIdClient(
            dependencies: $this->dependencies,
            settings: $this->settings,
            requestAuthorizationFactory: $this->requestAuthorizationFactory,
            tokenPair: new TokenPair(
                accessTokenString: $result['access_token'],
                idTokenString: $result['id_token'],
                scope: $result['scope'],
                expiresAt: new DateTimeImmutable(date(DateTimeImmutable::ATOM, time() + ((int) $result['expires_in']))),
                refreshTokenString: $result['refresh_token'] ?? null,
            ),
        );
    }

    public function createClientFromTokenPair(TokenPair $tokenPair): BankIdClient
    {
        return new BankIdClient(
            requestAuthorizationFactory: $this->requestAuthorizationFactory,
            dependencies: $this->dependencies,
            settings: $this->settings,
            tokenPair: $tokenPair,
        );
    }

    public function refreshClient(BankIdClient $oldClient): BankIdClient
    {
        $requestAuthorization = $this->requestAuthorizationFactory->create();

        $postFields = http_build_query([
            'grant_type' => 'refresh_token',
            'scope' => implode(' ', array_map(fn (Scope $scope): string => $scope->value, $oldClient->getTokenPair()->scope)),
            'refresh_token' => $oldClient->getTokenPair()->refreshTokenString,
            'redirect_uri' => $this->settings->postLoginRedirectUri,
            ...$requestAuthorization,
        ]);

        $request = $this->dependencies->requestFactory
            ->createRequest('POST', $this->settings->bankIdBaseUri . '/token')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                body: $this->dependencies->streamFactory->createStream($postFields),
            );

        $response = $this->dependencies->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            throw new NetworkException(status: $response->getStatusCode(), text: $response->getBody()->getContents());
        }

        /** @var array<string,string> $result */
        $result = json_decode($response->getBody()->getContents(), true);

        $this->jwtValidator->validate(
            jwt: $result['access_token'],
            expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );

        $this->jwtValidator->validate(
            jwt: $result['id_token'],
            expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );

        if (isset($result['refresh_token'])) {
            $this->jwtValidator->validate(
                jwt: $result['refresh_token'],
                expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
            );
        }

        return new BankIdClient(
            dependencies: $this->dependencies,
            settings: $this->settings,
            requestAuthorizationFactory: $this->requestAuthorizationFactory,
            tokenPair: new TokenPair(
                accessTokenString: $result['access_token'],
                idTokenString: $result['id_token'],
                scope: $result['scope'],
                expiresAt: new DateTimeImmutable(date(DateTimeImmutable::ATOM, time() + ((int) $result['expires_in']))),
                refreshTokenString: $result['refresh_token'] ?? null,
            ),
        );
    }

    /**
     * @return array<Event>
     */
    public function getNotifications(string $notificationToken): array
    {
        $notificationJWT = $this->jwtValidator->validate(
            jwt: $notificationToken,
            expectedAlgos: $this->configurationProvider->getTokenEndpointSigningAlgos(),
        );

        $rawEvents = $notificationJWT->claims->get('events');

        if (!is_array($rawEvents)) {
            throw new LogicException('The incoming notification token does not contain events.');
        }

        return array_map(
            fn (array $rawEvent): Event => new Event(
                sub: $rawEvent['sub'],
                originalEventAt: $rawEvent['original_event_at'],
                affectedClaims: $rawEvent['affected_claims'],
                type: $rawEvent['type'],
                affectedClientIds: $rawEvent['affected_client_ids'],
            ),
            $rawEvents,
        );
    }
}
