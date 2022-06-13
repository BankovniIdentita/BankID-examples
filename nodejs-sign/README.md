# BankID NodeJS signing example

This is an example of using [BankID](https://developer.bankid.cz/) with server-side JavaScript and [OIDC code flow](https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowSteps) to sign a PDF document.

## Quick Setup with Docker

Prerequisities:
- Docker

1. Create `.env` file in the root and copy & paste content from `.env.example`.
2. Set you `CLIENT_ID` and `CLIENT_SECRET` in the `.env` file.
3. Check if you have generated keypair ([Generate keys](#generate-keypair))
4. Open current folder `nodejs-sign` in a terminal and run:

```bash
docker-compose up
```

## Generate Keypair

Prerequisities:
- Node v16+
- Yarn

You will need a private and public key to run the application correctly.

In order to generate keys, you need to install dependencies:

```bash
yarn install
# or with NPM
npm install
```

To generate the keys, run:
```bash
yarn generate:keys
# or with NPM
npm run generate:keys
```

Check that the keys have been correctly generated in the `keys` folder. There should now be 2 files there - `jwks.json` and `private.json`.

You must now make your public key available online. You can use [Pastebin.com](https://pastebin.com/) to create a public key URL.

1. Copy the content of the `keys/jwks.json` file
2. Paste the content into the **New Paste** text area on [Pastebin.com](https://pastebin.com/).
3. Click **Create New Paste** button.
4. Now click on the **RAW** button and copy the URL from the address bar.
5. In the [BankID developer portal](https://developer.bankid.cz/dashboard), in your application settings, enter the URL in the **JWKs URI** field.
6. Click **Apply changes and generate credentials** button.


## Local Setup

- Ensure you have [NodeJS](https://nodejs.org/en/) v16 or newer
- Ensure you have [Yarn](https://yarnpkg.com/) installed (can be substituted with NPM)
- Run `yarn` to install dependencies
- Run `yarn generate:keys` to generate a key pair to sign request object with
- Create an app on [BankID developer portal](https://developer.bankid.cz) and configure its sandbox:
  - Allow redirect URI `https://localhost:3000/callback`
  - Configure JWKS URI to contain generated public key (see below)
- Add client ID and client secret from sandbox credentials in dev portal to `config.js`
- Run `yarn start:sign` to start the example signing app

## Signing example

[sign.js](/sign.js) uses [Express HTTP server](https://expressjs.com/) with [openid-client](https://github.com/panva/node-openid-client). This example sets up a document to be signed by the end user through BankID, redirects end user to authorize the request and receives the signed document.

You will need to create an app in the [BankID developer portal](https://developer.bankid.cz). Configure the app sandbox to allow redirect URI `https://localhost:3000/callback`.

Configure JWKS URI to point to a key set containing the previously generated public key. You can deploy this app publicly and configure JWKS URI to `${baseUrl}/jwks`. Alternatively, you might copy the contents of `http://localhost:3000/jwks` to a raw pastebin and point the JWKS URI there.

Make sure to update `CLIENT_ID` and `CLIENT_SECRET` in `config.js` with values from sandbox credentials of your app in dev portal.

Run this example by executing `yarn start:sign` and navigating to https://localhost:3000/.
