# BankID OpenId Connect Provider - Getting new token set by a refresh token

## Getting a new token set

The typical access token lifetime is 24 hours. Once this time span has passed, it's required to refresh the access token.

Let's assume that the BankId Provider is initialized in the following way:

```php
$provider = new BankIdProvider(
    settings: new Settings(
        bankIdBaseUri: 'https://oidc.sandbox.bankid.cz', //BankId instance base URI
        postLoginRedirectUri: 'http://localhost:3000', //your software URI + post-login redirect path
        postLogoutRedirectUri: 'http://localhost:3000/logout', //your software + post-logout redirect path
        clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe', //your id acquired from BankId dashboard
        clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q', //your secret also acquired from BankId dashboard
        authStrategy: AuthStrategy::PlainSecret, //choose exactly AuthStrategy::PlainSecret here
        jwk: null, //you don't need to pass JWK here. The parameter can be omitted
    ),
);
```

In order to make it possible to make calls to BankId, you just have to call the `refreshClient` method of BankId Provider in this way:

```php
$newClient = $provider->refreshClient($oldClient);
```

That's it. New BankId Client will be already equipped with all the required data for making the calls.