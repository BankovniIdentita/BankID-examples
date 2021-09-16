# BankID NodeJS signing example

This is an example of using [BankID](https://developer.bankid.cz/) with server-side JavaScript and [OIDC code flow](https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowSteps) to sign a PDF document.

## Setup

- Ensure you have [NodeJS](https://nodejs.org/en/) v14 or newer
- Ensure you have [Yarn](https://yarnpkg.com/) installed (can be substituted with NPM)
- Run `yarn` to install dependencies
- Run `yarn start:sign` to start example signing application

## Signing example

[sign.js](/sign.js) uses [Express HTTP server](https://expressjs.com/) with [Passport authentication middleware](http://www.passportjs.org/) and [openid-client](https://github.com/panva/node-openid-client). This example sets up a document to be signed by the end-user through BankID.

You will need to create an app in the [BankID developer portal](https://developer.bankid.cz). Configure the app sandbox to allow redirect URI `http://localhost:3000/callback`.

Make sure to update `CLIENT_ID` and `CLIENT_SECRET` in `config.js` with values from the sandbox

Run this example by executing `yarn start:sign` and navigating to https://localhost:3000/.
