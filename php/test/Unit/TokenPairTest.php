<?php

declare(strict_types=1);

namespace BankId\OIDC\Test\Unit;

use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\DTO\TokenPair;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TokenPairTest extends TestCase
{
    public function testGetScope(): void
    {
        $tokenPair = new TokenPair(
            accessTokenString: 'access_token',
            idTokenString: 'id_token',
            scope: 'openid offline_access profile.addresses profile.birthdate profile.birthnumber profile.birthplaceNationality profile.email profile.gender profile.idcards profile.legalstatus profile.locale profile.maritalstatus profile.name profile.paymentAccounts profile.phonenumber profile.titles profile.updatedat profile.zoneinfo profile.verification',
            expiresAt: new DateTimeImmutable('2023-09-16'),
            refreshTokenString: 'refresh_token',
        );

        static::assertEquals(
            expected: [
                Scope::OpenId,
                Scope::OfflineAccess,
                Scope::Address,
                Scope::BirthDate,
                Scope::BirthNumber,
                Scope::BirthPlaceNationality,
                Scope::Email,
                Scope::Gender,
                Scope::IdCards,
                Scope::LegalStatus,
                Scope::Locale,
                Scope::MaritalStatus,
                Scope::Name,
                Scope::PaymentAccounts,
                Scope::PhoneNumber,
                Scope::Titles,
                Scope::UpdatedAt,
                Scope::ZoneInfo,
                Scope::Verification,
            ],
            actual: $tokenPair->scope,
        );
    }
}
