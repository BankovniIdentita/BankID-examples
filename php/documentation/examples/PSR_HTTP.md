# BankID OpenId Connect Provider - Using the custom HTTP client

For the most common usecases, no special HTTP client is needed. This library is ready to make the network connections from the scratch. But what if you need something like a custom proxy or some other sort of HTTP adjustments?

It's clear that you need to configure the HTTP client in your way.

## Configuration

First, you need to create the HTTP client of your choice. We'll try to configure Guzzle HTTP client as an example.

```php
$client = new GuzzleHttp\Client([
    'timeout' => 15, //15 seconds of max timeout
    'proxy' => '192.168.16.1:10', //the proxy address
]);
```

Then, pass the dependencies set to the provider instance:

```php
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
        httpClient: $client,
        //It's important to pass Guzzle's HttpFactory and StreamFactory as well for the better compatibility:
        requestFactory: new GuzzleHttp\Psr7\HttpFactory(), 
        streamFactory: new GuzzleHttp\Psr7\HttpFactory(),
    ),
);
```

Now, the library can operate in the regular way with all the HTTP adjustments.