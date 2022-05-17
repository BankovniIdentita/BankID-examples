import { getRequestObject } from './getRequestObject.js'

export const getDocumentNameAndPath = (uploadUri, cookies) => {
  let documentObject

  // parse request object from cookies
  const requestObject = getRequestObject()

  // parse uploadUris from cookies
  const uploadUris = JSON.parse(atob(cookies.uploadUris))

  // get file uuid
  const fileUuid = uploadUri.split("/").pop()

  if (typeof uploadUris === "string") {
    // one uploadUri as a string
    documentObject = requestObject.structured_scope.documentObject
  } else {
    // multiple upload uris as an object

    // find documentId in requestObject documents
    const documentId = Object.entries(uploadUris).find((uu) => uu[1].split("/").pop() == fileUuid)[0]

    // find document ID by uuid and parse name and path
    documentObject = requestObject.structured_scope.documentObjects.documents.find(doc => doc.document_id === documentId)
  }

  const fileName = documentObject.document_uri.split("/").pop()
  const filePath = `./files/${fileName}`

  return { fileName, filePath }
}
