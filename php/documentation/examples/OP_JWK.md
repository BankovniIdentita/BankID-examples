# BankID OpenId Connect Provider - JWK Authorization based on OpenId Connect Provider JWKs

## Configuration

1. Create BankId Provider:

```php
    $provider = new BankIdProvider(
        settings: new Settings(
            bankIdBaseUri: 'https://oidc.sandbox.bankid.cz', //BankId instance base URI
            postLoginRedirectUri: 'http://localhost:3000', //your software URI + post-login redirect path
            postLogoutRedirectUri: 'http://localhost:3000/logout', //your software + post-logout redirect path
            clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe', //your id acquired from BankId dashboard
            clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q', //your secret also acquired from BankId dashboard
            authStrategy: AuthStrategy::SignedWithBankIdSecret, //choose exactly AuthStrategy::SignedWithBankIdSecret here
            jwk: null, //you don't need to pass JWK here. The parameter can be omitted
        ),
    );
```

2. JWKs

You don't need to worry about JWKs with this authorization strategy. Just configure the provider in the proposed way.

3. Exposing the endpoints

In the basic scenario, you don't need to expose any other endpoint except the one to accept the reverse redirect of the Client with "authorization code".
Once you receive this redirect, make sure to validate the state of the request and then to call `$provider->getClient($_GET['code'])` in order to get the configured BankId client.


4. Accepting the redirect

It's a good idea to verify that the `state` the client comes with is the same value as the one that has been generated at the time of the auth URI creation.

Once you perform all the checks, just pass the `code` get parameter to the provider in order to get the fully configured BankId client.

```php
$client = $provider->getClient($_GET['code']);
```


Please read [Quickstart](../QUICKSTART.md) chapter to know more about the flow of the authorization.