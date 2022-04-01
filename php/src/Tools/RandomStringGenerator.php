<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

interface RandomStringGenerator
{
    public function generate(): string;
}
