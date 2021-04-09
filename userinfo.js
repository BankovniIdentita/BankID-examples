/* CALLING USERINFO/PROFILE EXAMPLE */

import axios from 'axios';

// Set Userinfo / Profile URL
const userInfoEndpoint = 'https://oidc.sandbox.bankid.cz/userinfo';

// You must have a valid access token
const accessToken = 'Access token obtained through login';

const fetchUserinfo = async () => {
  // Pass access token to authorization header
  const headers = {
    Authorization: 'Bearer ' + accessToken,
  };

  try {
    const res = await axios(userInfoEndpoint, { headers });

    // Retrieved userinfo / profile data
    console.log(res.data);
  } catch (ex) {
    // handle errors
    console.error(ex);
  }
};

fetchUserinfo();
