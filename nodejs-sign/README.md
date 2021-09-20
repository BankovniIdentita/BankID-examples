# BankID NodeJS signing example

This is an example of using [BankID](https://developer.bankid.cz/) with server-side JavaScript and [OIDC code flow](https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowSteps) to sign a PDF document.

## Setup

- Ensure you have [NodeJS](https://nodejs.org/en/) v14 or newer
- Ensure you have [Yarn](https://yarnpkg.com/) installed (can be substituted with NPM)
- Run `yarn` to install dependencies
- Run `yarn generate:keys` to generate a key pair to sign request object with
- Create an app on [BankID developer portal](https://developer.bankid.cz) and configure its sandbox:
  - Allow redirect URI `http://localhost:3000/callback`
  - Configure JWKS URI to contain generated public key (see below)
- Add client ID and client secret from sandbox credentials in dev portal to `config.js`
- Run `yarn start:sign` to start the example signing app

## Signing example

[sign.js](/sign.js) uses [Express HTTP server](https://expressjs.com/) with [openid-client](https://github.com/panva/node-openid-client). This example sets up a document to be signed by the end user through BankID, redirects end user to authorize the request and receives the signed document.

You will need to create an app in the [BankID developer portal](https://developer.bankid.cz). Configure the app sandbox to allow redirect URI `http://localhost:3000/callback`.

Configure JWKS URI to point to a key set containing the previously generated public key. You can deploy this app publicly and configure JWKS URI to `${baseUrl}/jwks`. Alternatively, you might copy the contents of `http://localhost:3000/jwks` to a raw pastebin and point the JWKS URI there.

Make sure to update `CLIENT_ID` and `CLIENT_SECRET` in `config.js` with values from sandbox credentials of your app in dev portal.

Run this example by executing `yarn start:sign` and navigating to https://localhost:3000/.
