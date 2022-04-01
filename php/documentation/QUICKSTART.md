# BankID OpenId Connect Provider - Quickstart

## The actions cycle covered in this document

1. User comes to your website
2. You redirect him to BankId in order to choose the proper interaction mode
3. Once everything is ready at BankId's side, BankId redirects the user back to your website
4. Your website exchanges the authorization code that the user brough to you for the tokens set (access token, refresh token, id token and their metainformation).

## Required actions

In order to use a fully-configured BankId OpenId Connect Provider, you have to:

1. Configure the library
2. Create the authorization URI
3. Redirect the User-Agent (a user's browser) to the aforementioned URI
4. Expose the code exchange endpoint

That's it.

## Configure the library

Instantiate the BankId Provider class:

```php
    $provider = new BankIdProvider(
        settings: new Settings(
            bankIdBaseUri: 'https://oidc.sandbox.bankid.cz',
            postLoginRedirectUri: 'http://localhost:3000/token',
            postLogoutRedirectUri: 'http://localhost:3000/logout',
            clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe',
            clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q',
        ),
    );
```

1. `bankIdBaseUri`: the URI of BankId instance you are about to use. Sandbox or production one.
2. `postLoginRedirectUri`: the absolute address that the User-Agent will be redirected back to, once the authentication is done. If your domain is `https://acme.com`, then put here something like `https://acme.com/api/bankid/token`
3. `postLogoutRedirectUri`: the absolute address that the User-Agent will be redirected back to, once your software initiates and accomplished the logout process. If your domain is `https://acme.com`, then put there something like `https://acme.com/api/bankid/logout`
4. `clientId`: the ID you have previously obtained at BankId Dashboard
5. `clientSecret` a string of symbols that together with ID serves to authenticate your software at BankId side. Also is obtained in BankId Dashboard

## Create the authorization URI

The authorization URI is built in the following fashion:

```php
    $authUriBuilder = $provider->createAuthUriBuilder()
        ->withScope(Scope::Name, Scope::Email, Scope::BirthDate, Scope::OfflineAccess);
```

Pass the desired scopes to `withScope` method. You can check the programmatically available scopes in `Scope` enum. 

To know more about the scopes: [https://developer.bankid.cz/docs/high_level_overview_sep](https://developer.bankid.cz/docs/high_level_overview_sep)

Once the AuthUriBuilder is configured, feel free to render the final output:

```php
$authUri = $authUriBuilder->getAuthorizationUri();
```

Before you redirect the User-Agent to the aforementioned endpoint, ensure you have obtained and saved the state of the authorization request:

```php
//to obtain
$authorizationState = $authUriBuilder->getState();

//to save it in client-specific way:
$_SESSION['state'] = $authorizationState;
```

## Redirect the User-Agent (a user's browser) to the aforementioned URI

This part heavily depends on what software do you use.

Symfony:
```php
return new RedirectResponse($authUri, $status);
```

Laravel:
```php
return redirect($authUri);
```

Plain PHP:
```php
header('Location: ' . $authUri);
exit();
```

## Expose the code exchange endpoint

This part is also framework-dependent, so let's generalize it to a single thing:
You have to somehow extract the `code` parameter from the incoming GET query. This parameter contains a special symbols sequence that you will later exchange to the tokens set.

Further we'll reference the `code` get parameter as `$code` variable.

Before any next action, let's ensure that the client that went back from BankId is exactly the person we've sent there before:

```php
$clientState = $_SESSION['state'] ?? null;

if (null === $clientState || $clientState !== $_GET['state']) {
    exit('State mismatch! Rejected.');
}
```

Now, we create the BankId Client entity. (This is the class you are actually making the requests with in real usecases)

```php
$client = $provider->getClient($code);
```

That's it, your BankId Client is fully ready. Let's make something with it!

### Get the tokens

```php
$tokenPair = $client->getTokenPair(); //BankId\OIDC\DTO\TokenPair

$accessToken = $client->getTokenPair()->getAccessTokenString(); //plain string

$refreshToken = $client->getTokenPair()->getRefreshTokenString();

$idToken = $client->getTokenPair()->getIdTokenString();

$scope = $client->getTokenPair()->getScope(); //BankId\OIDC\AuthorizationParameters\Scope[]

$expiresAt = $client->getTokenPair()->getExpiresAt(); //DateTimeImmutable
```

### Get the server data

```php
$profile = $client->getProfile(); //BankId\OIDC\DTO\Profile

$tokenInfo = $client->getTokenInfo(); //BankId\OIDC\DTO\TokenInfo

$userInfo = $client->getUserInfo(); //BankId\OIDC\DTO\UserInfo
```

# A full example:

```php
    //the part that redirects the User-Agent to the BankId's auth endpoint

    session_start();

    $provider = new BankIdProvider(
        settings: new Settings(
            bankIdBaseUri: 'https://oidc.sandbox.bankid.cz',
            redirectUri: 'http://localhost:3000/token',
            clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe',
            clientSecret: 'OgHok0p_bqesQGytk8YBb1PPjO6fL82ZUlAkO7fRfg-l6KhNQCt1t1h097de-CNj1a1JCJMViAM9N8MLcIml2Q',
        ),
    );

    $authUriBuilder = $provider->createAuthUriBuilder()
        ->withScope(Scope::Name, Scope::Email, Scope::BirthDate, Scope::OfflineAccess);

    //obtain the auth state
    $authorizationState = $authUriBuilder->getState();

    //save it in client-specific way:
    $_SESSION['state'] = $authorizationState;

    $authUri = $authUriBuilder->getAuthorizationUri();

    header('Location: ' . $authUri);
    exit();



    //the part that accepts the reverse redirect and makes the calls:

    session_start();

    $clientState = $_SESSION['state'] ?? null;

    if (null === $clientState || $clientState !== $_GET['state']) {
        exit('State mismatch! Rejected.');
    }
 
    $client = $provider->getClient($_GET['code']);
    
    $tokenPair = $client->getTokenPair(); //BankId\OIDC\DTO\TokenPair

    $profile = $client->getProfile(); //BankId\OIDC\DTO\Profile
```