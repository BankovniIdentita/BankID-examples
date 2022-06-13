import 'dotenv/config'
import { renderFile } from 'ejs'
import express from 'express'
import cookieParser from 'cookie-parser'
import { promises as fs } from 'fs'
import { client } from './client.js'
import { encryptJwt, signJwt, verifyJwt } from './jwt.js'
import { getDocumentNameAndPath } from './utils/getDocumentNameAndPath.js'
import { getRequestObject } from './utils/getRequestObject.js'

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
app.use(cookieParser())

// Publishes key set with public key to verify request object signature
app.get('/jwks', async (req, res) => res.json(jwks))

// Index page with link to start sign flow
app.get('/', (req, res) => res.render('index.html'))

// Calls request object signature EP
app.get('/ros', async (req, res) => {
  const requestObject = getRequestObject()
  const signedRequestObject = await signJwt(requestObject)
  const encryptedRequestObject = await encryptJwt(signedRequestObject, await client.encryptionKey())
  const { data, error } = await client.ros(encryptedRequestObject)
  const uuid = data?.upload_uri?.split('/').pop()
  const uuids = data?.upload_uris && Object.values(data?.upload_uris).map(val => val.split('/').pop())

  // set uploadUris to cookies to be able to upload files
  res.cookie('uploadUris', btoa(JSON.stringify(data?.upload_uri || data?.upload_uris)), { maxAge: 900000, httpOnly: true })

  const authUri = client.authUri(`${data?.request_uri}`)
  res.render('ros.html', { requestObject, encryptedRequestObject, data, error, uuid, uuids, authUri })
})

// Uploads file to be signed
app.get('/upload/:uri/:uuids', async (req, res) => {
  const authUri = client.authUri(req.params.uri)
  const uploadResponses = await Promise.all(req.params.uuids.split(',').map(async (uuid) => {
    const uploadUri = `${process.env.UPLOAD_BASE_URI}/dev-portal-fileservice/api/v1/files/prepared/${uuid}`

    const {fileName, filePath} = getDocumentNameAndPath(uploadUri, req.cookies)
    const response = await client.upload(uploadUri, fileName, filePath)

    return { uploadUri, response, error: response?.error }
  }))

  res.render('upload.html', { uploadResponses, requestUri: req.params.uri, authUri })
})

// Callback EP used as redirect URI
// Exchanges authorization code and verifies tokens
app.get('/callback', async (req, res) => {
  const code = req.query.code
  const { data, error } = await client.token(code)

  if (error) {
    return res.render('error.html', { error })
  }

  const verifiedIdToken = await verifyJwt(data.id_token)
  res.render('callback.html', { code, tokens: data, verifiedIdToken })
})

app.listen(3000, () =>
  console.info('➡️  Go to http://localhost:3000 to initiate document sign flow')
)
