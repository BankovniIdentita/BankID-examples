<?php

declare(strict_types=1);

namespace App;

require './vendor/autoload.php';

session_start();

use BankId\OIDC\AuthorizationParameters\AcrValue;
use BankId\OIDC\AuthorizationParameters\CodeChallengeMethod;
use BankId\OIDC\AuthorizationParameters\ResponseType;
use BankId\OIDC\AuthorizationParameters\Scope;
use BankId\OIDC\AuthStrategy;
use BankId\OIDC\BankIdProvider;
use BankId\OIDC\DTO\TokenPair;
use BankId\OIDC\Exception\AuthenticationException;
use BankId\OIDC\Settings;
use DateTimeImmutable;
use DateTimeInterface;
use Jose\Component\Core\JWK;
use LogicException;
use Throwable;

// Prepare the generated JWK in advance
try {
    $privateKey = JWK::createFromJson(file_get_contents('./key.json'));
    $publicKey = JWK::createFromJson(file_get_contents('./publickey.json'));
} catch (Throwable) {
    $privateKey = null;
    $publicKey = null;
}

$provider = new BankIdProvider(
    settings: new Settings(
        bankIdBaseUri: 'https://oidc.sandbox.bankid.cz',
        postLoginRedirectUri: 'http://localhost:3000',
        postLogoutRedirectUri: 'https://431f-78-191-55-118.ngrok.io/logout',
        clientId: 'd544ec7e-6391-40b0-afe6-601ede4b47fe',
        clientSecret: 'F2fYeSRphbVIyn2s6Ef8IxsjV-hOxhpbAFYO0L_pSSNGclK2YC7ue2GQJrXnqt5ryNsepezASEVh7S6qWGJcEQ',
        authStrategy: AuthStrategy::SignedWithBankIdSecret, // can be omitted
        jwk: $privateKey,
    ),
);

if ('invalid_scope' === ($_GET['error'] ?? null)) {
    echo urldecode($_GET['error_description']);
    exit();
}

/*
 * Tip: you can provide your own implementations of libraries here:
 *
 * new ProviderDependencies(
 *     httpClient: new Client(), <------- any PSR-compatible ClientInterface
 *     requestFactory: new RequestFactory(), <--------- any PSR-compatible RequestFactoryInterface
 *     streamFactory: new StreamFactory(), <---------- any PSR-compatible StreamFactoryInterface
 *     cache: new Psr16Cache(new ApcuAdapter()) <----- here, Symfony's cache library is used (any PSR-16-compatible CacheInterface)
 *
 * This will reduce the amount of calls made to BankId (in order to obtain configuration or JWKs).
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve the key discovery endpoint
if ('/.well-known/jwks' === $uri) {
    if (null === $publicKey) {
        throw new LogicException('The key is missing; please generate it with `php generate.php`');
    }

    header('Content-type: application/json');
    echo json_encode([
        'keys' => [$publicKey],
    ]);
    exit();
}

// Serve the notifications endpoint
if ('/notifications' === $uri) {
    $events = $provider->getNotifications($_POST['notification_token']);
    file_put_contents('./last_notification_call.json', json_encode([
        'time' => date(DateTimeInterface::ATOM, time()),
        'data' => $events,
    ]));
    // let's do some backend actions related to changed user's details
    http_response_code(200);
    exit();
}

// Serve the logout endpoint
if ('/logout' === $uri) {
    file_put_contents('./last_logout_call.json', json_encode([
        'time' => date(DateTimeInterface::ATOM, time()),
        'data' => $_GET,
    ]));
    // let's do some backend actions once the session is confirmed as terminated
    http_response_code(200);
    exit();
}

if (!isset($_GET['code'])) {
    $authUriBuilder = $provider->createAuthUriBuilder()
        ->withScope(
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
            Scope::OfflineAccess,
            Scope::PaymentAccounts,
            Scope::PhoneNumber,
            Scope::Titles,
            Scope::UpdatedAt,
            // Scope::Verification, //not required in the example
            Scope::ZoneInfo,
        )
        ->withResponseType(ResponseType::Code) // You can go the implicit way here if you want
        ->withCodeChallengeMethod(CodeChallengeMethod::S256)
        ->withAcrValue(AcrValue::Loa2);

    // Save the state at the client side
    $_SESSION['state'] = $authUriBuilder->getState();

    // Perform the redirect
    header('Location: ' . $authUriBuilder->getAuthorizationUri());
    exit();
}

$clientState = $_SESSION['state'] ?? null;

if (null === $clientState || $clientState !== $_GET['state']) {
    exit('State mismatch! Rejected.');
}

$client = $provider->getClient($_GET['code']);

$tokenPair = $client->getTokenPair();

$accessToken = $tokenPair->accessTokenString;
$refreshToken = $tokenPair->refreshTokenString;
$idToken = $tokenPair->idTokenString;
$scope = $tokenPair->scope;
$expiresAt = $tokenPair->expiresAt;

$scopePlainList = array_map(
    fn (Scope $scope): string => $scope->value,
    $scope,
);

$output = [
    'tokens' => [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken ?? 'Not set becase offline_access scope is not requested',
        'id_token' => $idToken,
        'scope' => implode(' ', $scopePlainList),
        'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        'id_token_decoded' => base64_decode(explode('.', $tokenPair->idTokenString)[1]),
        'meaningful_part' => json_decode(base64_decode(explode('.', $tokenPair->idTokenString)[1]), true),
    ],
    'token_info' => $client->getTokenInfo(),
    'profile' => $client->getProfile(),
    'userinfo' => $client->getUserInfo(),
];

$logoutRequest = $provider->createLogoutUriBuilder($idToken)->getLogoutRequest();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Authentication data:</h2>
    <pre><?php print_r($output); ?></pre>
    <br />
    <br />
    <h2>Logout form:</h2>
    <form action="<?php echo $logoutRequest->uri; ?>" method="POST"
        target="_blank">
        <div>
            <label for="id_token_hint">ID Token Hint:</label>
            <input id="id_token_hint" type="text" name="id_token_hint"
                value="<?php echo $logoutRequest->idTokenHint; ?>" />
        </div>
        <br>
        <div>
            <label for="post_logout_redirect_uri">Post-logout redirect URI:</label>
            <input id="post_logout_redirect_uri" type="text" name="post_logout_redirect_uri"
                value="<?php echo $logoutRequest->postLogoutRedirectUri; ?>" />
        </div>
        <br>
        <div>
            <label for="session_state">Session state:</label>
            <input id="session_state" type="text" name="session_state"
                value="<?php echo $logoutRequest->sessionState; ?>" />
        </div>
        <br>
        <div>
            <input type="submit" value="Go">
        </div>
    </form>
</body>

</html>