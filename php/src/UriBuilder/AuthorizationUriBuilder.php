<?php

declare(strict_types=1);

namespace BankId\OIDC\UriBuilder;

use BankId\OIDC\AuthorizationParameters\AcrValue;
use BankId\OIDC\AuthorizationParameters\CodeChallengeMethod;
use BankId\OIDC\AuthorizationParameters\ResponseType;
use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\Tools\RandomStringGenerator;

class AuthorizationUriBuilder
{
    private string $state;

    /**
     * @param array<Scope> $scope
     */
    public function __construct(
        private readonly string $baseUri,
        private readonly string $clientId,
        private readonly string $postLoginRedirectUri,
        private readonly RandomStringGenerator $randomStringGenerator,
        private readonly ResponseType $responseType = ResponseType::Code,
        private readonly CodeChallengeMethod $codeChallengeMethod = CodeChallengeMethod::Plain,
        private readonly AcrValue $acrValue = AcrValue::Loa2,
        private readonly array $scope = [Scope::OpenId],
        ?string $state = null,
    ) {
        $this->state = $state ?? $this->randomStringGenerator->generate();
    }

    public function withScope(Scope ...$scope): self
    {
        return new self(
            baseUri: $this->baseUri,
            clientId: $this->clientId,
            postLoginRedirectUri: $this->postLoginRedirectUri,
            randomStringGenerator: $this->randomStringGenerator,
            responseType: $this->responseType,
            codeChallengeMethod: $this->codeChallengeMethod,
            acrValue: $this->acrValue,
            scope: array_merge([Scope::OpenId], $scope),
            state: $this->state,
        );
    }

    public function withResponseType(ResponseType $responseType): self
    {
        return new self(
            baseUri: $this->baseUri,
            clientId: $this->clientId,
            postLoginRedirectUri: $this->postLoginRedirectUri,
            randomStringGenerator: $this->randomStringGenerator,
            responseType: $responseType,
            codeChallengeMethod: $this->codeChallengeMethod,
            acrValue: $this->acrValue,
            scope: $this->scope,
            state: $this->state,
        );
    }

    public function withCodeChallengeMethod(CodeChallengeMethod $codeChallengeMethod): self
    {
        return new self(
            baseUri: $this->baseUri,
            clientId: $this->clientId,
            postLoginRedirectUri: $this->postLoginRedirectUri,
            randomStringGenerator: $this->randomStringGenerator,
            responseType: $this->responseType,
            codeChallengeMethod: $codeChallengeMethod,
            acrValue: $this->acrValue,
            scope: $this->scope,
            state: $this->state,
        );
    }

    public function withAcrValue(AcrValue $acrValue): self
    {
        return new self(
            baseUri: $this->baseUri,
            clientId: $this->clientId,
            postLoginRedirectUri: $this->postLoginRedirectUri,
            randomStringGenerator: $this->randomStringGenerator,
            responseType: $this->responseType,
            codeChallengeMethod: $this->codeChallengeMethod,
            acrValue: $acrValue,
            scope: $this->scope,
            state: $this->state,
        );
    }

    public function getAuthorizationUri(): string
    {
        $scope = implode(
            ' ',
            array_map(
                fn (Scope $scope): string => $scope->value,
                $this->scope,
            ),
        );

        $query = http_build_query(
            data: [
                'approval_prompt' => 'auto',
                Scope::PARAM => $scope,
                CodeChallengeMethod::PARAM => $this->codeChallengeMethod->value,
                ResponseType::PARAM => $this->responseType->value,
                AcrValue::PARAM => $this->acrValue->value,
                'state' => $this->state,
                'client_id' => $this->clientId,
                'redirect_uri' => $this->postLoginRedirectUri,
            ],
        );

        return $this->baseUri . '/auth?' . $query;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
