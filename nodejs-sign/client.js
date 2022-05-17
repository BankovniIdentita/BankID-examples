import Axios from 'axios'
import FormData from 'form-data'
import { promises as fs } from 'fs'
import { Issuer } from 'openid-client'
import qs from 'qs'
import {
  BANKID_ISSUER,
  CLIENT_ID,
  CLIENT_SECRET,
  REDIRECT_URI,
} from './config.js'

// Parse OpenID configuration using openid-client
const bankidIssuer = await Issuer.discover(BANKID_ISSUER)

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
      return { error: error.response?.data }
    }
  },

  /**
   * Calls BankID ROS EP to initiate sign flow
   * @params requestObject Signed & encrypted request object (https://developer.bankid.cz/docs/api/bankid-for-sep#operations-Sign-post_ros)
   * @returns              ROS response with request_uri, upload_uri & expiration | error object
   */
  ros: async function (requestObject) {
    try {
      const { data } = await Axios.post(bankidIssuer.ros_endpoint, requestObject, {
        headers: {
          'Content-Type': 'application/jwe',
        },
      })
      return { data }
    } catch (error) {
      return { error: error.response?.data }
    }
  },

  /**
   * Uploads file in FILE_PATH to upload URI received from ROS EP
   * @param uploadUri upload_uri received from ROS EP response
   * @returns         void | error object
   */
  upload: async function (uploadUri, fileName, filePath) {
    const file = await fs.readFile(filePath)
    const data = new FormData()
    data.append('file', file, { filename: fileName })

    try {
      await Axios.post(uploadUri, data, { headers: data.getHeaders() })
    } catch (error) {
      return { error: error.response?.data }
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
   * @returns    Tokens | error object
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
      return { data }
    } catch (error) {
      return { error: error.response?.data }
    }
  },
}
