<?php

declare(strict_types=1);

namespace BankId\OIDC\AuthorizationParameters;

enum ResponseType: string
{
    case Token = 'token';
    case Code = 'code';

    public const PARAM = 'response_type';
}
