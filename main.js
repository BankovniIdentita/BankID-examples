/* COMPOSING LOGIN URL EXAMPLE */

// BankID sandbox auth endpoint
const authEndpoint = 'https://oidc.sandbox.bankid.cz/auth';

// Configuration of scopes from BankID dev portal
const scopes = [
  'openid',
  'profile.birthnumber',
  'profile.phonenumber',
  'profile.zoneinfo',
  'profile.gender',
  'profile.titles',
  'profile.name',
  'profile.birthplaceNationality',
  'profile.locale',
  'profile.idcards',
  'profile.maritalstatus',
  'profile.verification',
  'profile.legalstatus',
  'profile.email',
  'profile.paymentAccounts',
  'profile.addresses',
  'profile.birthdate',
  'profile.updatedat',
];

// Query parameters for the auth call
const authUriParams = {
  client_id: 'Your client ID generated on the development portal',
  state: 'Optional state value you want to pass on',
  scope: scopes.join(' '),
  // Redirect URI to your application
  redirect_uri: 'http://localhost:5500',
  // reponse_type 'token' for implicit flow
  response_type: 'token',
};

// Query parameters in URL query string format
const uriParams = new URLSearchParams(authUriParams);

// Complete auth URI
const authUri = `${authEndpoint}?${uriParams}`;

// Get the login button
const loginButton = document.querySelector('#login');

// Change login button href to authUri
loginButton.href = authUri;

/* CALLING USERINFO/PROFILE EXAMPLE */

// Get the code block in html
const codeBlock = document.querySelector('code');

// Set Userinfo / Profile URL
const userInfoEndpoint = 'https://oidc.sandbox.bankid.cz/userinfo';

// Obtain access_token from URL fragment
const hash = window.location.hash.substring(1);
const params = new URLSearchParams(hash);
const accessToken = params.get('access_token');

const fetchUserinfo = async () => {
  // Pass access token to authorization header
  const headers = {
    Authorization: 'Bearer ' + accessToken,
  };

  try {
    const res = await fetch(userInfoEndpoint, { headers });
    // Retrieved userinfo / profile data in object format
    const json = await res.json();
    console.log(json);

    // Fill code block in HTML with data in JSON format for preview purposes
    codeBlock.innerHTML = JSON.stringify(json, null, 2);
  } catch (ex) {
    // handle errors
    console.error(ex);
  }
};

// Call userinfo if we received fragment data (we logged in)

if (hash) {
  fetchUserinfo();
}
