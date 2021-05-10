import express from 'express';
import passport from 'passport';
import session from 'express-session';
import { Strategy, Issuer } from 'openid-client';

const CLIENT_ID = 'ff5f4db0-e950-4722-abda-30c31a0a4bf0';
const CLIENT_SECRET =
  'ECBEJf19p1z18ERYLEdRDl--tVgx0d8o7xT5xJ275vaQ84eN3Y5IRGrWnrTQLMHJ9G-UI03tmfuTMWSOO-L-KQ';
const SCOPE = 'openid';

const bankidIssuer = await Issuer.discover(
  'https://core-idp.staging.ci.bankd.cz/'
);
const bankidClient = new bankidIssuer.Client({
  client_id: CLIENT_ID,
  client_secret: CLIENT_SECRET,
  redirect_uris: ['http://localhost:3000/'],
});

passport.use(
  'bankid',
  new Strategy(
    {
      client: bankidClient,
      params: {
        scope: 'openid profile.email',
      },
    },
    async (tokenSet, done) => {
      console.warn(tokenSet, done);

      // this throws 500
      const userinfo = await bankidClient.userinfo(tokenSet.access_token);
      console.warn(bankidIssuer);
      return done(null, userinfo);
    }
  )
);

passport.serializeUser((user, done) => done(null, user));
passport.deserializeUser((user, done) => done(null, user));

const app = express();

app.use(
  session({
    secret: 'secret',
    resave: false,
    saveUninitialized: false,
  })
);
app.use(passport.initialize());
app.use(passport.session());

app.get('/', passport.authenticate('bankid'), (req, res) => res.send(req.user));

app.listen(3000);
