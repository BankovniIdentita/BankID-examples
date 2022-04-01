<?php

declare(strict_types=1);

namespace BankId\OIDC\DTO;

final class Address
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?string $street,
        public readonly ?string $buildingapartment,
        public readonly ?string $streetnumber,
        public readonly ?string $city,
        public readonly ?string $zipcode,
        public readonly ?string $country,
        public readonly ?string $ruian_reference,
    ) {
    }
}
