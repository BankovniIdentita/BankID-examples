<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

class Event
{
    /**
     * @param array<string> $affectedClaims
     * @param array<string> $affectedClientIds
     */
    public function __construct(
        public readonly string $sub,
        public readonly string $originalEventAt,
        public readonly array $affectedClaims,
        public readonly string $type,
        public readonly array $affectedClientIds,
    ) {
    }
}
