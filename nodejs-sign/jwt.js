import { createRemoteJWKSet } from 'jose/jwks/remote'
import { jwtVerify } from 'jose/jwt/verify'
import { BANKID_ISSUER, BANKID_JWKS } from './config.js'

const bankIdJwks = createRemoteJWKSet(new URL(BANKID_JWKS))

export async function verifyJwt(token) {
  const { payload } = await jwtVerify(token, bankIdJwks, {
    issuer: BANKID_ISSUER,
  })
  return payload
}
