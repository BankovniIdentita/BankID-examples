# BankID OpenId Connect Provider - Back-Channel Initiated Logout

## Configuration

1. It's assumed that the BankId Provider is already created in the following fashion:

```php
    $provider = new BankIdProvider(
        settings: new Settings(
            bankIdBaseUri: 'https://oidc.sandbox.bankid.cz', //BankId instance base URI
            postLoginRedirectUri: 'http://localhost:3000', //your software URI + post-login redirect path
            postLogoutRedirectUri: 'https://acme.com/api/bankid/notifications', //attention! this URI is what we will work with
            clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe', //your id acquired from BankId dashboard
            clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q', //your secret also acquired from BankId dashboard
            authStrategy: AuthStrategy::SignedWithBankIdSecret, //choose exactly AuthStrategy::SignedWithBankIdSecret here
            jwk: null, //you don't need to pass JWK here. The parameter can be omitted
        ),
    );
```

2. Exposing the endpoints

In order to have this usecase working, you have to expose the special notifications URI.

First, configure it in the BankId Dashboard. Find the line that's called "Notification URI" and put there something like:
`https://acme.com/api/bankid/notifications`

Then, ensure that the BankId Provider is configured as it's said in the paragraph #1.

And at last, make sure that your application is actually running at the aforementioned address and also that the path (`/api/bankid/notifications`) matches the appropriate logic related to the notifications.

3. Getting the notifications

At some moment you may receive a POST request of the following shape:
```php
notification_token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c
```

The token string is something we need.

Extract it in the way your framework supports and pass to the BankId Provider:

```php
$events = $provider->getNotifications($_POST['notification_token']);
```

This `$events` array will contain one or more `BankId\OIDC\DTO\Event` objects.

Each one has:
- originalEventAt: the datetime string with the exact point in time of the original event
- affectedClaims: an array of claims that were changed. Take a look here to understand what exactly client did change.
- type: a string telling the purpose of the token. Typically "claims_updated" string will be there.
- affectedClientIds: an array of IDs matching to some users of your software. Together with "affectedClaims" it's enough to know which clients' data you have to refresh.

4. Post-notification actions

Once you know which clients were modified and in which way, feel free to request their data from the BankId side:

```php
$clientData = $client->getUserInfo();
```