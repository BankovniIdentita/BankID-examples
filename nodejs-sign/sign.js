import { renderFile } from 'ejs'
import express from 'express'
import { promises as fs } from 'fs'
import { client, getRequestObject } from './client.js'
import { encryptJwt, signJwt, verifyJwt } from './jwt.js'

let jwks
try {
  jwks = JSON.parse((await fs.readFile('./keys/jwks.json')).toString())
} catch (_) {
  console.error('Please generate a key pair by running yarn generate:keys first.')
  process.exit(1)
}

const app = express()
app.set('views', './views')
app.engine('html', renderFile)
app.use(express.static('public'))

// Publishes key set with public key to verify request object signature
app.get('/jwks', async (req, res) => res.json(jwks))

// Index page with link to start sign flow
app.get('/', (req, res) => res.render('index.html'))

// Calls request object signature EP
app.get('/ros', async (req, res) => {
  const requestObject = getRequestObject()
  const signedRequestObject = await signJwt(requestObject)
  const encryptedRequestObject = await encryptJwt(signedRequestObject, await client.encryptionKey())
  const response = await client.ros(encryptedRequestObject)
  const uuid = response.upload_uri?.split('/').pop()
  const requestUri = response.request_uri
  res.render('ros.html', { requestObject, encryptedRequestObject, response, uuid, requestUri })
})

// Uploads file to be signed
app.get('/upload/:uri/:uuid', async (req, res) => {
  const uploadUri = `https://api.bankid.cz/dev-portal-fileservice/api/v1/files/prepared/${req.params.uuid}`
  const response = await client.upload(uploadUri)
  const authUri = client.authUri(req.params.uri)
  res.render('upload.html', { uploadUri, requestUri: req.params.uri, response, authUri })
})

// Callback EP used as redirect URI
// Exchanges authorization code and verifies tokens
app.get('/callback', async (req, res) => {
  const code = req.query.code
  const tokens = await client.token(code)

  if (tokens.error) {
    return res.render('error.html', { error: tokens.response })
  }

  const verifiedIdToken = await verifyJwt(tokens.id_token)
  res.render('callback.html', { code, tokens, verifiedIdToken })
})

app.listen(3000, () =>
  console.info('➡️  Go to http://localhost:3000 to initiate document sign flow')
)
