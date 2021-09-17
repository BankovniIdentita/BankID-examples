import express from 'express'
import { promises as fs } from 'fs'
import { client } from './client.js'

let jwks
try {
  jwks = JSON.parse((await fs.readFile('./keys/jwks.json')).toString())
} catch (_) {
  console.error('Please generate a key pair by running yarn generate:keys first.')
  process.exit(1)
}

const app = express()

// Publishes key set with public key to verify request object signature
app.get('/jwks', async (req, res) => res.json(jwks))

// Calls request object signature EP
app.get('/ros', async (req, res) => res.json(await client.ros()))

// Uploads file to be signed
app.get('/upload/:uri', async (req, res) => res.json(await client.upload(req.params.uri)))

// Starts BankID authorization flow by redirecting end user to authorization EP
app.get('/auth/:uri', (req, res) => res.redirect(client.authUri(req.params.uri)))

// Callback EP used as redirect URI
// Exchanges authorization code and verifies tokens
app.get('/callback', async (req, res) => res.json(await client.token(req.query.code)))

app.listen(3000, () =>
  console.info('➡️  Go to http://localhost:3000 to initiate document sign flow')
)
