<?php

declare(strict_types=1);

namespace BankId\OIDC\Tools;

use DateTimeImmutable;

class SplTimeProvider implements TimeProvider
{
    public function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, date(DateTimeImmutable::ATOM, time())); //@phpstan-ignore-line
    }
}
