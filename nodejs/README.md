# BankID NodeJS code grant example

This is an example of using [BankID](https://developer.bankid.cz/) with server-side JavaScript and [OIDC code flow](https://openid.net/specs/openid-connect-core-1_0.html#CodeFlowSteps).

## Setup

- Ensure you have [NodeJS](https://nodejs.org/en/) v14 or newer
- Ensure you have [Yarn](https://yarnpkg.com/) installed (can be substituted with NPM)
- Navigate into the [nodejs](/nodejs) directory
- Run `yarn install` to install dependencies
- Run `yarn start:login` or `yarn start:kyc` to start example applications

## Connect (login) example

[login.js](/nodejs) uses [Express HTTP server](https://expressjs.com/) with [Passport authentication middleware](http://www.passportjs.org/) and [openid-client](https://github.com/panva/node-openid-client). This example uses authentication only and does not call userinfo or profile EndPoints. This shows how to use the Connect product on it's own.

Make sure to update `CLIENT_ID` and `CLIENT_SECRET` with values provided to you through the [BankID developer portal](https://developer.bankid.cz/).

```javascript
const CLIENT_ID = '72fda011-0479-4a4c-9fff-0a6c7f584e1e';
const CLIENT_SECRET =
  'TsGlMQro488YSwc0h9NWydZAqHir13PPW2cDMEQBcLgFvaGOnXzOt9MWBhTDBEU7PaXtn9H7Y0QHdcVZolJOsg';
const SCOPE = 'openid';

const bankidIssuer = await Issuer.discover('https://oidc.sandbox.bankid.cz/');
const bankidClient = new bankidIssuer.Client({
  client_id: CLIENT_ID,
  client_secret: CLIENT_SECRET,
  redirect_uris: ['http://localhost:3000/'],
  response_types: ['code'],
  id_token_signed_response_alg: 'PS512',
});
```

Authorized routes are protected with `passport.authenticate('bankid')` and `sub` from `id_token` is available via `req.user`

```javascript
app.get('/', passport.authenticate('bankid'), (req, res) => res.json(req.user));
```

You can try this example by running `yarn start:login` in the [nodejs](/nodejs) directory and navigating to https://localhost:3000/.

## Identify KYC (userinfo and profile) example

This example is the same as the **Login** example, but it calls `/userinfo` and `/profile` EndPoints ([documentation](https://developer.bankid.cz/docs/api/bankid-for-sep)) during the authentication process.

```javascript
async (tokenSet, done) => {
  const userinfo = await bankidClient.userinfo(tokenSet.access_token);
  const profile = await axios.get(bankidIssuer.profile_endpoint, {
    headers: { Authorization: 'Bearer ' + tokenSet.access_token },
  });
  return done(null, { userinfo, profile: profile.data });
};
```

You can try this example by running `yarn start:identify` in the [nodejs](/nodejs) directory and navigating to https://localhost:3000/.
