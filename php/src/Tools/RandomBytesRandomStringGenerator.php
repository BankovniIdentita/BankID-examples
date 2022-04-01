<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

class RandomBytesRandomStringGenerator implements RandomStringGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }
}
