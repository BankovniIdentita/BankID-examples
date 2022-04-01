# BankID OpenId Connect Provider - Examples

## Authorization at OP

The first big thing to configure in this library is the way of the authorization at OP.

This library covers the "auth code" flow of OpenId Connect protocol. This means that the authentication scenario assumes 3 steps:
1. RP (Relying Party, basically your software) redirects the Client to OP (OpenId Connect Provider, it's BankId at our case)
2. OP redirects the Client back to RP with a special code
3. RP exchanges this code to a token set

The last step (the code exchange) requires the RP (your software) to authorize at OP. This is possible to do with 3 options:
- [plain secret authorization](./PLAIN_SECRET.md) (basic security level, simple)
- [OP's JWK-based authorization](./OP_JWK.md) (better security level, still quite simple)
- [own JWK-based authorization](./OWN_JWK.md) (advanced security)

Please follow the appropriate articles to know more about each scenario.

## Logging out of OP

You may need to terminate the authorization session on OP's side, whether by End User's request or based on your concerns.

In order to do so, you need to follow the next steps:
1. RP generates a set of data to create the logout form
2. End User submits the form and executes the POST HTTP request to OP (BankId) side
3. OP terminates the session and redirects the End User back to RP's special endpoint (post-logour redirect uri)
4. RP does the remaining actions based on the confirmed session termination

To know more, see [back-channel logout](./LOGOUT.md) article.

## Restoring the session from the saved data

Once the End User is authorized at your application, he may go and return in some time. It's possible not to request the authorization again, if the previously obtained data is stored properly.

1. Let the End User to authorize
2. Save the token in some way
3. Once the End User returns, create the BankId Client again with his data.

The logic is described in [session restore](./SESSION_RESTORE.md) chapter.

## Refreshing the expired token

It may happen that the time span between the initial authorization and the current moment is more than the allowed access token life time. In this case it's necessary to refresh the short-living access token using a long-living refresh token.

The procedure is described in [refresh token](./REFRESH_TOKEN.md) paragraph.

## Receiving the notifications

When something is changed in End User's details at OP's side, you may need to know it happened. For example, you'd like to keep the phone number of End User in the actual state. In order to do so, you have to arrange an endpoint that is ready to accept the notifications.

To know more, see [notifications](./NOTIFICATIONS.md) chapter.

## Custom HTTP client and PSR-compatible caching

By default, nothing of this list is required for the library to operate properly. But in order to adjust its' behaviour in the desired way, you can pass some of the preconfigured dependencies to the initial constructor.

- [custom PSR-compatible HTTP client](./PSR_HTTP.md)
- [custom PSR-compatible cache](./PSR_CACHE.md)