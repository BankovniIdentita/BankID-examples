import { promises as fs } from 'fs'
import { fromKeyLike } from 'jose/jwk/from_key_like'
import { generateKeyPair } from 'jose/util/generate_key_pair'

// generate a key pair used to sign request object

const alg = 'ES512'
const { publicKey, privateKey } = await generateKeyPair(alg)

const publicJwk = {
  kid: 'key-1',
  use: 'sig',
  ...(await fromKeyLike(publicKey)),
}
const jwks = { keys: [publicJwk] }
const priv = { alg, ...(await fromKeyLike(privateKey)) }

await fs.rm('./keys', { recursive: true, force: true })
await fs.mkdir('./keys')
await fs.writeFile('./keys/jwks.json', JSON.stringify(jwks))
await fs.writeFile('./keys/private.json', JSON.stringify(priv))
