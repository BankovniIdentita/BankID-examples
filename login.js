import express from 'express';
import qs from 'qs';
import jwt from 'jsonwebtoken';
import Axios from 'axios';

const CLIENT_ID = '7261040c-a2b6-4688-8389-4dc9d77a1ba6';
const CLIENT_SECRET =
  'AL77jB6tZJMJOacM6ZUBWTk021G0YKC9uGleWYlWFlTk-GtfCh6s09xXkGLi8DFMGaZZ9MpfIHBGtyoxfwQ_dtA';
const SCOPE = 'openid';

const app = express();

app.get('/', async (req, res) => {
  if (!req.query.code) {
    res.redirect(
      'https://core-idp.staging.ci.bankd.cz/auth?' +
        qs.stringify({
          redirect_uri: 'http://localhost:3000/',
          response_type: 'code',
          scope: SCOPE,
          response_mode: 'query',
          client_id: CLIENT_ID,
        })
    );
  }

  try {
    const tokens = await Axios.post(
      'https://core-idp.staging.ci.bankd.cz/token',
      qs.stringify({
        code: req.query.code,
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
        grant_type: 'authorization_code',
      })
    );
    const accessToken = jwt.decode(tokens.data.access_token);
    res.send(`Hello user with ID ${accessToken.payload.sub}!`);
  } catch (ex) {
    console.error(ex.response.data);
  }
});

app.listen(3000);
