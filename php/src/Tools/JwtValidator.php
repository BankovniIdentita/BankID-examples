<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

use BankId\OIDC\Discovery\ConfigurationProvider;
use BankId\OIDC\Discovery\KeysProvider;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Easy\JWT;
use Jose\Easy\Load;
use Jose\Easy\Validate;

class JwtValidator
{
    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly KeysProvider $keysProvider,
    ) {
    }

    /**
     * @param array<string> $expectedAlgos
     */
    public function validate(string $jwt, array $expectedAlgos): JWT
    {
        /** @var array<JWK> $keys */
        $keys = array_map(
            fn (array $rawKey): JWK => new JWK($rawKey),
            $this->keysProvider->getKeys(),
        );

        /** @var Validate $jws */
        $jws = Load::jws($jwt)
            ->iss($this->configurationProvider->getIssuer())
            ->keyset(new JWKSet($keys));

        if (null !== $expectedAlgos) {
            /** @var Validate $jws */
            $jws = $jws->algs($expectedAlgos);
        }

        return $jws->run();
    }
}
