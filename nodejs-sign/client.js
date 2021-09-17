import Axios from 'axios'
import FormData from 'form-data'
import { promises as fs } from 'fs'
import { Issuer } from 'openid-client'
import qs from 'qs'
import { v4 } from 'uuid'
import {
  BANKID_ISSUER,
  CLIENT_ID,
  CLIENT_SECRET,
  FILENAME,
  FILE_PATH,
  REDIRECT_URI,
} from './config.js'
import { encryptJwt, signJwt, verifyJwt } from './jwt.js'

// Parse OpenID configuration using openid-client
const bankidIssuer = await Issuer.discover(BANKID_ISSUER)

// Request object required by BankID to initiate sign flow
// see https://developer.bankid.cz/docs/api/bankid-for-sep#operations-Sign-post_ros
export const getRequestObject = () => ({
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
      document_hash: '92c9609710f188c28ff37832be00849851366813c7a8e6fdac4bc7a088b624b1',
      hash_alg: '2.16.840.1.101.3.4.2.1',
      document_title: 'Test PDF document',
      document_subject: 'Testing sign with BankID',
      document_language: 'en',
      document_author: 'Daniel Kessl',
      document_size: 9785,
      document_pages: 1,
      document_uri: 'http://něco', // TODO verify if this is needed
      document_created: '2018-12-29T10:46:53+01:00',
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
  /**
   * Fetches a BankID public key usable for encryption
   * @returns Public key with use == 'enc' | error object
   */
  encryptionKey: async function () {
    try {
      const { data } = await Axios.get(bankidIssuer.jwks_uri)
      const key = data.keys?.find((key) => key.use === 'enc')
      return key
    } catch (error) {
      return { error, response: error.response?.data }
    }
  },

  /**
   * Calls BankID ROS EP to initiated sign flow
   * @params requestObject Request object (https://developer.bankid.cz/docs/api/bankid-for-sep#operations-Sign-post_ros)
   * @returns              ROS response with request_uri, upload_uri & expiration | error object
   */
  ros: async function (requestObject) {
    const signedRequestObject = await signJwt(requestObject)
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
      return { error, response: error.response?.data }
    }
  },

  /**
   * Uploads file in FILE_PATH to upload URI received from ROS EP
   * @param uploadUri upload_uri received from ROS EP response
   * @returns         void | error object
   */
  upload: async function (uploadUri) {
    const file = await fs.readFile(FILE_PATH)
    const data = new FormData()
    data.append('file', file, { filename: FILENAME })

    try {
      await Axios.post(uploadUri, data, { headers: data.getHeaders() })
    } catch (error) {
      return { error, response: error.response?.data }
    }
  },

  /**
   * Constructs authorization URI to initiate sign flow
   * @param requestUri request_uri from ROS EP response
   * @returns          Authorization URI
   */
  authUri: function (requestUri) {
    const params = qs.stringify(
      {
        request_uri: requestUri,
        redirect_uri: REDIRECT_URI,
      },
      {
        addQueryPrefix: true,
      }
    )
    return `${bankidIssuer.authorize_endpoint}${params}`
  },

  /**
   * Calls BankID token EP and exchanges authorization code for tokens
   * @param code Authorization code
   * @returns    Verified & decoded ID token | error object
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
      return { error, response: error.response?.data }
    }
  },
}
