import { v4 } from 'uuid'
import {
  CLIENT_ID,
  FILENAME,
} from '../config.js'

// Request object required by BankID to initiate sign flow
// see https://developer.bankid.cz/docs/api/bankid-for-sep#operations-Sign-post_ros
export const getRequestObject = () => {

  const body = {
    txn: v4(),
    client_id: CLIENT_ID,
    nonce: v4(),
    state: "ahoj jsem tady ve stavu",
    response_type: 'code',
    max_age: 3600,
    scope: 'openid offline_access',
    structured_scope: {
      signObject: {
        fields: [
          // {
          //   key: 'Marketing consent - priority 1',
          //   value: 'I consent to receive marketing materials - priority 1',
          //   priority: 1,
          // },
          // {
          //   key: 'Marketing consent - priority 2',
          //   value: 'I consent to receive marketing materials - priority 2',
          //   priority: 2,
          // },

          // // 255 key & 1024 value
          // {
          //   key: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lectus mauris ultrices eros in cursus turpis massa tincidunt dui. Odio facilisis mauris sit amet massa vitae tortor condimentum la.',
          //   value: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lectus mauris ultrices eros in cursus turpis massa tincidunt dui. Odio facilisis mauris sit amet massa vitae tortor condimentum lacinia. Sit amet tellus cras adipiscing enim eu turpis egestas. Tellus pellentesque eu tincidunt tortor aliquam nulla. Pretium nibh ipsum consequat nisl vel pretium lectus quam id. Nunc mattis enim ut tellus elementum sagittis vitae. Risus commodo viverra maecenas accumsan lacus. Vestibulum mattis ullamcorper velit sed ullamcorper. Nibh nisl condimentum id venenatis a condimentum vitae. Diam sollicitudin tempor id eu nisl nunc mi. Donec pretium vulputate sapien nec sagittis. Consectetur lorem donec massa sapien faucibus et molestie ac. Nibh mauris cursus mattis molestie a iaculis. Ullamcorper velit sed ullamcorper morbi tincidunt ornare massa eget. Commodo viverra maecenas accumsan lacus vel facilisis volutpat est. Praesent semper feugiat nibh sed pulvinar pro.',
          //   priority: 5,
          // },
        ],
      },

      // documentObject data must match metadata of file being signed
      // documentObject: testDocumentObject,

      // documentObject with empty author and subject
      // documentObject: emptyDocumentObject,

      // documentObject with 5-page PDF
      // documentObject: fivePageDocumentObject,

      // documentObject with diacritics "ěščřžýáíéůúťďň"
      // documentObject: diacriticsDocumentObject,

      // multi-sign object data
      documentObjects: {
        envelope_name: "this is envelope name",
        documents: [
          {
            priority: 1,
            ...testDocumentObject
          },
          {
            priority: 2,
            ...emptyDocumentObject
          },
          // {
          //   priority: 3,
          //   ...test3DocumentObject
          // },
          // {
          //   priority: 4,
          //   ...test4DocumentObject
          // },
          // {
          //   priority: 5,
          //   ...test5DocumentObject
          // },
          // {
          //   priority: 6,
          //   ...test6DocumentObject
          // },
          // {
          //   priority: 7,
          //   ...test7DocumentObject
          // },
          // {
          //   priority: 8,
          //   ...test8DocumentObject
          // },
          // {
          //   priority: 9,
          //   ...test9DocumentObject
          // },
          // {
          //   priority: 1000,
          //   ...test10DocumentObject
          // },
          // {
          //   priority: 10,
          //   ...fivePageDocumentObject
          // },
          // {
          //   priority: 10,
          //   ...fivePageDocumentObject
          // },
          // {
          //   priority: 10,
          //   ...emptyDocumentObject
          // },
        ]
      },


    },
  }

  // 20 times push same signObject as alredy exists in the object
  if (body.structured_scope.signObject.fields.length > 0) {
    for (let i = 2; i <= 20; i++) {
      body.structured_scope.signObject.fields.push(
        {
          key: `Marketing consent - priority ${i}`,
          value: `I consent to receive marketing materials - priority ${i}`,
          priority: i,
        },
      )
    }
  }

  return body
}


const testDocumentObject = {
  document_id: 'ID1111111111',
  document_hash: '92c9609710f188c28ff37832be00849851366813c7a8e6fdac4bc7a088b624b1',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'Test PDF document',
  document_subject: 'Testing sign with BankID',
  document_language: 'en',
  document_author: 'Daniel Kessl',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test.pdf`,
  document_created: '2018-12-29T10:46:53+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const emptyDocumentObject = {
  document_id: 'IDEmptyDocument',
  document_hash: '49d6c8e976affe9e04374232a4da060aa572374f46d4fedf429449e4567d9c1d',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: '',
  document_subject: ' ',
  document_language: 'en',
  document_author: ' ',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/empty-strings-test.pdf`,
  document_created: '2018-12-29T10:46:53+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const fivePageDocumentObject = {
  document_id: '1641710160000',
  document_hash: '569e06d46fbd3957fc9ade96ee58cd6c8ebbd0f92f92b9ad2722eb28c5481b5e',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID_5_page_document',
  document_subject: 'bankid test 5 pages',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 5,
  document_uri: `http://localhost:3000/files/bankid_5_page_document.pdf`,
  document_created: '2022-02-09T07:35:11+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const diacriticsDocumentObject = {
  document_id: 'ID123456789',
  document_hash: '5142e0421f58fa9ebdb7fbd87668cd48297d195a323e4cd60144d4891c673d03',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'ěščřžýáíéůúťďň',
  document_subject: 'ěščřžýáíéůúťďň',
  document_language: 'en',
  document_author: 'ěščřžýáíéůúťďň',
  document_size: 9785,
  document_pages: 5,
  document_uri: `http://localhost:3000/files/document_with_diacritics.pdf`,
  document_created: '2018-12-29T10:46:53+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test2DocumentObject = {
  document_id: 'ID1111111112',
  document_hash: 'aafc29b26ab732a9fcf47c2d12b1e59834e77a64bbe44b5b3fbcf878ad2fa35b',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 2',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test2.pdf`,
  document_created: '2022-05-11T08:23:04+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test3DocumentObject = {
  document_id: 'ID1111111113',
  document_hash: '23c2969762e2b12a19492a3d2e20cbf65d70507d5f4dc762cca7193e42c98c27',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 3',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test3.pdf`,
  document_created: '2022-05-11T08:23:13+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test4DocumentObject = {
  document_id: 'ID1111111114',
  document_hash: 'cb92365db8a6fb6b10f7440cc772212d5d75173d003b171d02fa87316dec7d16',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 4',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test4.pdf`,
  document_created: '2022-05-11T08:23:20+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test5DocumentObject = {
  document_id: 'ID1111111115',
  document_hash: '8fb31b636194adae79e5ebc09f08c023303fc1ab01f1a43341761db8748d28ba',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 5',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test5.pdf`,
  document_created: '2022-05-11T08:23:27+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test6DocumentObject = {
  document_id: 'ID1111111116',
  document_hash: '0bdbfa99d8a039e1b06152af4ecec24e4264ef8029a1e39d9693936a98bfb655',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 6',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test6.pdf`,
  document_created: '2022-05-11T08:23:39+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test7DocumentObject = {
  document_id: 'ID1111111117',
  document_hash: '35668049e4602ee47323d203a4e08e7e73e98960f71e06d4e590faa94b8f0ee1',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 7',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test7.pdf`,
  document_created: '2022-05-11T08:23:46+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test8DocumentObject = {
  document_id: 'ID1111111118',
  document_hash: '18499df933849eced3814f7b3925b110d7c69548283206685572ca498c5d8dd0',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 8',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test8.pdf`,
  document_created: '2022-05-11T08:23:56+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test9DocumentObject = {
  document_id: 'ID1111111119',
  document_hash: '047e5855ea70b9fa64d2519f7805a411166df261ebeecf51426bea969a303427',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 9',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test9.pdf`,
  document_created: '2022-05-11T08:24:04+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}

const test10DocumentObject = {
  document_id: 'ID11111111110',
  document_hash: '1023bf7417b86adca7486b83c1e980bd6c55de02a30b3c306c90b0bc25c93a59',
  hash_alg: '2.16.840.1.101.3.4.2.1',
  document_title: 'BankID testing document 10',
  document_subject: 'Prokop Simek BankID document',
  document_language: 'en',
  document_author: 'Prokop Simek',
  document_size: 9785,
  document_pages: 1,
  document_uri: `http://localhost:3000/files/test10.pdf`,
  document_created: '2022-05-11T08:24:12+01:00',
  document_read_by_enduser: true,
  sign_area: {
    page: 0,
    'x-coordinate': 350,
    'y-coordinate': 150,
    'x-dist': 140,
    'y-dist': 50,
  }
}
