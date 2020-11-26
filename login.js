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

app.get('/', passport.authenticate('bankid'), async (req, res) =>
  res.send(`Hello user with ID ${req.user.sub}!`)
);

app.listen(3000);
