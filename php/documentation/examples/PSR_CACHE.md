# BankID OpenId Connect Provider - Using the cache

Why does this library need a cache at all?

At the time of tokens receiving, OP (BankId) side responds with a set of tokens. Each token is a [JWT](https://jwt.io/introduction) structure with a signature in its' end. It's important for every RP to be sure that the token was issues exactly be the organization we expect to. This is why we need to access the public prints of OP's JWKs.

This request is done over the HTTP layer, and this means that it has not to be perfomed often. It's quite okay to cache the results for some reasonable time and store them somewhere inside of your infrastructure for a quicker access.

## Configuration

**By default, this library uses no cache.**

But it's simple to start using the cache. Pick the PSR-16 compatible library and pass its' instance to the original provider constructor.

For the example, we'll pick `symfony/cache` component, as it fits to our simple needs.

We'll use:
* `Symfony\Component\Cache\Adapter\ApcuAdapter` <-- the cache adapter that utilizes the [APCU](https://www.php.net/manual/en/intro.apcu.php) technology of caching
* `Symfony\Component\Cache\Psr16Cache` <-- the PSR-16 compatible wrapper of APCU adapter

```php
$apcuAdapter = new ApcuAdapter();
$cache = new Psr16Cache($apcuAdapter);

$provider = new BankIdProvider(
    settings: new Settings(
        bankIdBaseUri: 'https://oidc.sandbox.bankid.cz',
        postLoginRedirectUri: 'http://localhost:3000', //your software URI + post-login redirect path
        postLogoutRedirectUri: 'http://localhost:3000/logout', //your software + post-logout redirect path
        clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe',
        clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q',
        authStrategy: AuthStrategy::PlainSecret,
    ),
    dependencies: new Dependencies(
        cache: $cache, <-- here we pass the cache dependency so it overrides the default one
    ),
);
```

So now the OP's JWKs will be requested only once per hour. The same thing applies to the OpenId Connect discovery endpoint of OP.
