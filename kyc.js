import express from 'express';
import passport from 'passport';
import BankId from 'passport-bankid';

const bankId = new BankId({
  redirectUri: 'http://localhost:3000/',
  clientId: process.env.CLIENT_ID,
  clientSecret: process.env.CLIENT_SECRET,
  sandbox: true,
  scope: 'profile.name profile.addresses',
});

passport.use(bankId);

const app = express();

app.get('/', passport.authenticate('bankid'), async (req, res) => {
  const profile = bankId.loadProfile(req.user.accessToken);
  const address = profile.addresses[0];
  res.json({
    name: `${profile.given_name} ${profile.family_name}!`,
    address: `${address.street}, ${address.city}, ${address.zipcode}`,
  });
});

app.listen(3000);
