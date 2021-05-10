# BankID NodeJS code grant example

This is an example of using [BankID](https://developer.bankid.cz/) with client-side JavaScript and [OIDC Implicit Flow](https://openid.net/specs/openid-connect-core-1_0.html#ImplicitFlowAuth).

In this example we first login using OIDC Implicit Flow which is Connect product usage and then fetch `/userinfo` EndPoint which simulates using the Identify product.

Since OpenID Connect uses redirects, you will need a development server to try this example. We use [serve](https://www.npmjs.com/package/serve) for NodeJS, but you can use what you are most familiar with (just make sure to update `redirect_uri` accordingly).

## Running the example

- Ensure you have [NodeJS](https://nodejs.org/en/) v14 or newer
- Run `npx serve` to start a development server on http://localhost:5000
- Navigate to http://localhost:5000 and Login with BankID

## Structure

Client configuration is mainly `client_id` which is provided to you through the [BankID developer portal](https://developer.bankid.cz/). `scopes` parameter specifies which data is requested from the end-user. When you are ready to go into production, update `authEndpoint` and `userInfoEndpoint` URLs according to documentation.

```javascript
// BankID sandbox auth endpoint
const authEndpoint = 'https://oidc.sandbox.bankid.cz/auth';

// Set Userinfo / Profile URL
const userInfoEndpoint = 'https://oidc.sandbox.bankid.cz/userinfo';

// Configuration of scopes from BankID dev portal
const scopes = ['openid', 'profile.email'];

// Query parameters for the auth call
const authUriParams = {
  client_id: '0c53196f-fdba-4d27-84c0-a74e00e775b6',
  state: 'Optional state value you want to pass on',
  scope: scopes.join(' '),
  // Redirect URI to your application
  redirect_uri: 'http://localhost:5000',
  // reponse_type 'token' for implicit flow
  response_type: 'token',
};
```
