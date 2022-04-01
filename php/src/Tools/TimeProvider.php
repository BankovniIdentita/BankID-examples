<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

use DateTimeImmutable;

interface TimeProvider
{
    public function now(): DateTimeImmutable;
}
