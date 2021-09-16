import Axios from 'axios'
import { Issuer } from 'openid-client'
import qs from 'qs'
import { BANKID_ISSUER, CLIENT_ID, CLIENT_SECRET, REDIRECT_URI } from './config.js'
import { verifyJwt } from './jwt.js'

const bankidIssuer = await Issuer.discover(BANKID_ISSUER)
const bankidClient = new bankidIssuer.Client({
  client_id: CLIENT_ID,
  scope: 'openid',
  response_types: ['code'],
  redirect_uris: [REDIRECT_URI],
})

export const client = {
  authUri: bankidClient.authorizationUrl(),

  token: async function (code) {
    const codeRequest = {
      code,
      grant_type: 'authorization_code',
      client_id: CLIENT_ID,
      client_secret: CLIENT_SECRET,
      redirect_uri: REDIRECT_URI,
    }
    const { data } = await Axios.post(bankidIssuer.token_endpoint, qs.stringify(codeRequest))
    const idToken = await verifyJwt(data.id_token)
    return idToken
  },
}
