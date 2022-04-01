<?php

declare(strict_types=1);

namespace BankId\OIDC\AuthorizationParameters;

enum AcrValue: string
{
    case Loa2 = 'loa2';
    case Loa3 = 'loa3';

    public const PARAM = 'acr_value';
}
