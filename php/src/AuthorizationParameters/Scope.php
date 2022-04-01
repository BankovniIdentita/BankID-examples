<?php

declare(strict_types=1);

namespace BankId\OIDC\AuthorizationParameters;

enum Scope: string {
    case OpenId = 'openid';
    case OfflineAccess = 'offline_access';
    case Address = 'profile.addresses';
    case BirthDate = 'profile.birthdate';
    case BirthNumber = 'profile.birthnumber';
    case BirthPlaceNationality = 'profile.birthplaceNationality';
    case Email = 'profile.email';
    case Gender = 'profile.gender';
    case IdCards = 'profile.idcards';
    case LegalStatus = 'profile.legalstatus';
    case Locale = 'profile.locale';
    case MaritalStatus = 'profile.maritalstatus';
    case Name = 'profile.name';
    case PaymentAccounts = 'profile.paymentAccounts';
    case PhoneNumber = 'profile.phonenumber';
    case Titles = 'profile.titles';
    case UpdatedAt = 'profile.updatedat';
    case ZoneInfo = 'profile.zoneinfo';
    case Verification = 'profile.verification';

    public const PARAM = 'scope';
}
