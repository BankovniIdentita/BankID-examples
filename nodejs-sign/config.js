// update these values from sandbox credentials page on BankID developer portal
// https://developer.bankid.cz

export const CLIENT_ID = 'your-app-client-id'
export const CLIENT_SECRET = 'your-app-client-secret'

// add http://localhost:3000 to your app's redirect URIs in dev portal
export const REDIRECT_URI = 'http://localhost:3000/callback'

// change issuer to https://oidc.bankid.cz/ for production
export const BANKID_ISSUER = 'https://oidc.sandbox.bankid.cz/'
export const BANKID_JWKS = `${BANKID_ISSUER}.well-known/jwks`
