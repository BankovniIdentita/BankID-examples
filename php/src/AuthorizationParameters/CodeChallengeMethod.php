<?php

declare(strict_types=1);

namespace BankId\OIDC\AuthorizationParameters;

enum CodeChallengeMethod: string {
    case Plain = 'plain';
    case S256 = 'S256';

    public const PARAM = 'code_challenge_method';
}
