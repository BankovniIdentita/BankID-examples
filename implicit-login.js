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
  redirect_uri: 'Redirect URI to your application',
  state: 'Optional state value you want to pass on',
  scope: scopes.join(' '),
  // reponse_type 'token' for implicit flow, 'code' for authorization code flow
  response_type: 'token',
};

// Query parameters in URL query string format
const uriParams = new URLSearchParams(authUriParams);

// Complete auth URI
const authUri = `${authEndpoint}?${uriParams}`;
