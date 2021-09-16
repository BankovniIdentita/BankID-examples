import express from 'express'
import { client } from './client.js'

const app = express()

// Starts BankID authorization flow by redirecting end user to authorization EP
app.get('/', (req, res) => res.redirect(client.authUri))

// Callback EP used as redirect URI
// Exchanges authorization code for tokens and verifies them
app.get('/callback', async (req, res) => {
  const code = req.query.code
  const idToken = await client.token(code)
  res.json(idToken)
})

app.listen(3000, () =>
  console.info('➡️  Go to http://localhost:3000 to initiate document sign flow')
)
