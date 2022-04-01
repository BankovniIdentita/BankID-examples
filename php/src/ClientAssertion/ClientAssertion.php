<?php

declare(strict_types=1);

namespace BankId\OIDC\ClientAssertion;

class ClientAssertion
{
    public function __construct(
        public readonly string $clientAssertionType,
        public readonly string $clientAssertion,
    ) {
    }
}
