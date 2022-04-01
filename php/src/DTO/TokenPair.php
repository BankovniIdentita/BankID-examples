<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

use BankId\OIDC\AuthorizationParameters\Scope;
use DateTimeImmutable;
use Exception;

class TokenPair
{
    /**
     * @var array<Scope>
     */
    public readonly array $scope;

    /**
     * @param string $scope
     */
    public function __construct(
        public readonly string $accessTokenString,
        public readonly string $idTokenString,
        string $scope,
        public DateTimeImmutable $expiresAt,
        public readonly ?string $refreshTokenString = null,
    ) {
        $this->scope = array_map(
            fn (string $scope): Scope => Scope::from($scope),
            array_filter(explode(' ', trim($scope))),
        );
    }

    public function __toString()
    {
        $encoded = json_encode([
            'accessTokenString' => $this->accessTokenString,
            'idTokenString' => $this->idTokenString,
            'scope' => implode(' ', array_map(fn (Scope $scope): string => $scope->value, $this->scope)),
            'expiresAt' => $this->expiresAt->format(DateTimeImmutable::ATOM),
            'refreshTokenString' => $this->refreshTokenString,
        ]);

        if (!is_string($encoded)) {
            throw new Exception('Failed to encode token pair!');
        }

        return $encoded;
    }
}
