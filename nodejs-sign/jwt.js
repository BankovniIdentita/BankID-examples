import { promises as fs } from 'fs'
import { CompactEncrypt } from 'jose/jwe/compact/encrypt'
import { parseJwk } from 'jose/jwk/parse'
import { createRemoteJWKSet } from 'jose/jwks/remote'
import { SignJWT } from 'jose/jwt/sign'
import { jwtVerify } from 'jose/jwt/verify'
import { BANKID_ISSUER, BANKID_JWKS } from './config.js'

const bankIdJwks = createRemoteJWKSet(new URL(BANKID_JWKS))

/**
 * Verifies and decodes JWT using BankID JWKS
 * @param token JWT to be verified
 * @returns     Verified & decoded JWT payload
 */
export async function verifyJwt(token) {
  const { payload } = await jwtVerify(token, bankIdJwks, {
    issuer: BANKID_ISSUER,
  })
  return payload
}

/**
 * Signs a JWT with generated private key stored in ./keys/private.json
 * @param token JWT to be signed
 * @returns     Signed JWT
 */
export async function signJwt(token) {
  const jwk = JSON.parse((await fs.readFile('./keys/private.json')).toString())
  const key = await parseJwk(jwk)
  return new SignJWT(token).setProtectedHeader({ alg: jwk.alg, kid: jwk.kid }).sign(key)
}

const encode = TextEncoder.prototype.encode.bind(new TextEncoder())

/**
 * Encrypts a JWT using RSA-OAEP-256 with provided public JWK
 * @param token JWT to be encrpyted
 * @param jwk   Public JWK to encrypt with
 * @returns     Encrypted JWT
 */
export async function encryptJwt(token, jwk) {
  const header = {
    alg: 'RSA-OAEP-256',
    enc: 'A256GCM',
    kid: jwk.kid,
  }

  const key = await parseJwk(jwk, header.alg)
  return new CompactEncrypt(encode(token)).setProtectedHeader(header).encrypt(key)
}
