<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;

class KeyGenerator
{
    /**
     * @param array<string,mixed> $values
     */
    public function generate(int $size = 4096, array $values = ['alg' => 'PS512', 'use' => 'sig']): JWK
    {
        return JWKFactory::createRSAKey($size, $values);
    }
}
