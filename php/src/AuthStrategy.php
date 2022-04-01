<?php

declare(strict_types=1);

namespace BankId\OIDC;

/*
 * Describes the way the client performs the authentication when exchanging the token.
 */
enum AuthStrategy {
    case PlainSecret;
    case SignedWithBankIdSecret;
    case SignedWithOwnJWK;
}
