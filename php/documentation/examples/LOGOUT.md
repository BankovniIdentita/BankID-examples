# BankID OpenId Connect Provider - Back-Channel Initiated Logout

## Configuration

1. It's assumed that the BankId Provider is already created in the following fashion:

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

2. Exposing the endpoints

In order to have this usecase working, you don't need to expose nothing but "post-logout uri" in your application. Keeping in mind that you can logout only after a successful login, it's assumed also that you've exposed the "post-login uri" as well.

3. Creating the URI and redirect

Create the logour request:
```php
$logoutRequest = $provider->createLogoutUriBuilder($idToken)->getLogoutRequest();
```

And then do something similar to:
```php
<form action="<?= $logoutRequest->uri ?>" method="POST" target="_blank>
    <input name="id_token_hint" value="<?= $logoutRequest->idTokenHint ?>"/>
    <input name="post_logout_redirect_uri" value="<?= $logoutRequest->postLogoutRedirectUri ?>" />
    <input name="session_state" value="<?= $logoutRequest->sessionState ?>" />
    <input type="submit" value="Go">
</form>
```

Feel free to choose any other option of making the End User to follow the POST HTTP request to the required URI. Any of the ways will lead to a session termination from OP's side, and then the End User will be redirected back to your "post-logour redirect uri".

4. Accepting the redirect

Once the End User is back again, it's good to check whether his `session_state` matches the one you've generated earlier. It's a sign of non-interrupted data flow.

Once you perform all the checks, it's time to do all logout-related logic on your backend side.