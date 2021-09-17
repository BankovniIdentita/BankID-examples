import Axios from 'axios'
import { Issuer } from 'openid-client'
import qs from 'qs'
import { v4 } from 'uuid'
import { BANKID_ISSUER, CLIENT_ID, CLIENT_SECRET, REDIRECT_URI } from './config.js'
import { encryptJwt, signJwt, verifyJwt } from './jwt.js'

// Parse OpenID configuration using openid-client
const bankidIssuer = await Issuer.discover(BANKID_ISSUER)
const bankidClient = new bankidIssuer.Client({
  client_id: CLIENT_ID,
  redirect_uris: [REDIRECT_URI],
})

// Request object required by BankID to initiate sign flow
// see https://developer.bankid.cz/docs/api/bankid-for-sep#operations-Sign-post_ros
const getRequestObject = () => ({
  txn: v4(),
  client_id: CLIENT_ID,
  nonce: v4(),
  state: v4(),
  response_type: 'code',
  max_age: 3600,
  scope: 'openid',
  structured_scope: {
    signObject: {
      fields: [
        {
          key: 'Marketing consent',
          value: 'I consent to receive marketing materials',
          priority: 1,
        },
      ],
    },
    // documentObject data must match metadata of file being signed
    documentObject: {
      document_id: 'ID123456789',
      document_hash: '1b8cfaa6ade6c28dbc9438e1f52d2a55d702f5f1a64b35354723fe8b89d651c6',
      hash_alg: '2.16.840.1.101.3.4.2.1',
      document_title: 'Filip Important Document',
      document_subject: "Mr. Aguirre's experiments with pdfmark",
      document_language: 'CS',
      document_author: 'Jaziel Aguirre',
      document_size: 13389,
      document_pages: 1,
      document_uri: 'http://něco', // TODO verify if this is needed
      document_created: '2020-06-24T08:54:11+00:00',
      document_read_by_enduser: true,
      sign_area: {
        page: 0,
        'x-coordinate': 100,
        'y-coordinate': 200,
        'x-dist': 20,
        'y-dist': 15,
      },
    },
  },
})

export const client = {
  authUri: bankidClient.authorizationUrl(),

  /**
   * Fetches a BankID public key usable for encryption
   * @returns Public key with use == 'enc'
   */
  encryptionKey: async function () {
    try {
      const { data } = await Axios.get(bankidIssuer.jwks_uri)
      const key = data.keys?.find((key) => key.use === 'enc')
      return key
    } catch (error) {
      return { error, response: error.response.data }
    }
  },

  /**
   * Calls BankID token EP and exchanges authorization code for tokens
   * @param code Authorization code
   * @returns Verified & decoded ID token
   */
  token: async function (code) {
    const codeRequest = {
      code,
      grant_type: 'authorization_code',
      client_id: CLIENT_ID,
      client_secret: CLIENT_SECRET,
      redirect_uri: REDIRECT_URI,
    }

    try {
      const { data } = await Axios.post(bankidIssuer.token_endpoint, qs.stringify(codeRequest))
      return verifyJwt(data.id_token)
    } catch (error) {
      return { error, response: error.response.data }
    }
  },

  /**
   * Calls BankID ROS EP to initiated sign flow
   * @returns ROS response with request_uri, upload_uri & expiration
   */
  ros: async function () {
    const signedRequestObject = await signJwt(getRequestObject())
    const encryptedRequestObject = await encryptJwt(
      signedRequestObject,
      await client.encryptionKey()
    )

    try {
      const { data } = await Axios.post(bankidIssuer.ros_endpoint, encryptedRequestObject, {
        headers: {
          'Content-Type': 'application/jwe',
        },
      })
      return data
    } catch (error) {
      return { error, response: error.response.data }
    }
  },
}
