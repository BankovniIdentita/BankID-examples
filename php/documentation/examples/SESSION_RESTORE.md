# BankID OpenId Connect Provider - Restoring the session from the stored tokens

## Saving the tokens

It's assumed that you have already created the authorization, and your application is capable of authorizing the client.
In simple words, you can already obtain the TokenPair class like this:

```php
$client = $provider->getClient($_GET['code']);

$tokenPair = $client->getTokenPair()
```

The next thing is to save it in some way. You may either save it field-by-field, or in a stringified way:

```php
file_put_contents('./token_pair.json', $tokenPair);
```

If followed the proposed example, you'll see something like:

```json
{
    "accessTokenString": "eyJraWQiOiJy...",
    "idTokenString": "eyJraWQiOiJycC1z...",
    "scope": "profile.birthnumber profile.phonenumber profile.zoneinfo openid profile.gender profile.titles profile.birthplaceNationality profile.name profile.idcards profile.locale profile.maritalstatus profile.legalstatus profile.email profile.paymentAccounts offline_access profile.addresses profile.birthdate profile.updatedat",
    "expiresAt": {
        "date": "2022-03-27 17:52:25.000000",
        "timezone_type": 1,
        "timezone": "+00:00"
    },
    "refreshTokenString": "eyJraWQiOiJycC..."
}
```

## Restoring the token pair

First, let's recreate the token pair instance:

```php
$tokenPair = new TokenPair(
    accessTokenString: 'eyJraWQiOiJ...',
    idTokenString: 'eyJraWQiOiJycC1...',
    scope: 'profile.birthnumber profile.phonenumber profile.zoneinfo...',
    expiresAt: new DateTimeImmutable('2022-03-27T17:58:54+00:00'),
    refreshTokenString: 'eyJraWQiOiJ...',
);
```

Then, pass it to BankId provider:

```php
$client = $provider->createClientFromTokenPair($tokenPair);
```

And then, use this client in the regular way.

## Expired access token

If the access token has expired, you'll see `AuthenticationException` raised in your application.
To bypass this problem, you need to refresh the access token. Please see [refresh token](./REFRESH_TOKEN.md) article.