# BankID CURL examples

This is an example of using [BankID](https://developer.bankid.cz/) with [CURL](https://curl.se/) to achieve Connect (login), and Identify (userinfo and profile).

Companion [Postman Collection](https://documenter.getpostman.com/view/13505299/TzRSgnJZ) is also available for you to follow along.

## Requirements

- You will need the [CURL](https://curl.se/) HTTP client. You can also import CURL calls into [Postman](https://www.postman.com/).
- We will be using [OIDC Debugger](https://oidcdebugger.com) for handling redirects
  - Configure `https://oidcdebugger.com/debug` redirect_uri for your application in [BankID](https://developer.bankid.cz/)

Our examples will use the following keys:

- Implicit Flow: Client Id = `191ede14-968a-4cca-b2c9-6f2a1f595515`
- Code Flow: Client Id = `23bb49e7-97e8-4cb8-8dc0-15db1486b788`, Client Secret = `SRH8KlLbppd4iT391M5lh-rWN6cGruhV1qaSi8Jo0kE2h8DrjSMERzYnCIpIMH5dsFPiW8yI5DC5GjzVH6Qqow`

## 1-a. Authentication using Implicit Flow

Get access token for a Single Page Application or similar client which cannot securely store Client Secret.

First, we will need to construct authorization URL. You can copy the authorization URL from the "Examples" section in Credentials tab in your application in the [Developer Portal](https://developer.bankid.cz/). Do note the `client_id` query parameter, which you will have to update to the client id issued to you.

Once you have the authorization URI constructed, open it in a browser and copy the access token

```url
https://oidc.sandbox.bankid.cz/auth?client_id=191ede14-968a-4cca-b2c9-6f2a1f595515&redirect_uri=https%3A%2F%2Foidcdebugger.com%2Fdebug&scope=profile.birthnumber%20profile.phonenumber%20profile.zoneinfo%20profile.gender%20openid%20profile.titles%20profile.name%20profile.birthplaceNationality%20profile.locale%20profile.idcards%20profile.maritalstatus%20profile.legalstatus%20profile.email%20profile.paymentAccounts%20profile.addresses%20profile.birthdate%20profile.updatedat&response_type=token&state=BankID%20works%21&nonce=2b13acea-ea17-4399-ab2e-f748bd636ce4&prompt=login&display=page&acr_values=loa2
```

## 1-b. Authentication using Authorization Code Flow

#### Get code token

Get access token for a server side application in which you can securely store the Client Secret.

Construct and authorization URL in the same way as for Implicit Flow, but make sure to configure your application to use Code Flow on the [Developer Portal](https://developer.bankid.cz/) and use `response_type=code` query parameter.

Once you have the authorization URI constructed, open it in a browser and copy the code token

```url
https://oidc.sandbox.bankid.cz/auth?client_id=23bb49e7-97e8-4cb8-8dc0-15db1486b788&redirect_uri=https%3A%2F%2Foidcdebugger.com%2Fdebug&scope=profile.birthnumber%20profile.phonenumber%20profile.zoneinfo%20profile.gender%20openid%20profile.titles%20profile.name%20profile.birthplaceNationality%20profile.locale%20profile.idcards%20profile.maritalstatus%20profile.legalstatus%20profile.email%20profile.paymentAccounts%20profile.addresses%20profile.birthdate%20profile.updatedat&response_type=code&state=BankID%20works%21&nonce=fd68ae30-15d5-4937-be1f-f0a5ac071422&prompt=login&display=page&acr_values=loa2
```

#### Exchange code token for access token

To get access token from code token, copy the `code` from OIDC Debugger and use it in following CURL call

```shell
curl --location --request POST 'https://oidc.sandbox.bankid.cz/token' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'grant_type=authorization_code' \
--data-urlencode 'client_id=23bb49e7-97e8-4cb8-8dc0-15db1486b788' \
--data-urlencode 'client_secret=SRH8KlLbppd4iT391M5lh-rWN6cGruhV1qaSi8Jo0kE2h8DrjSMERzYnCIpIMH5dsFPiW8yI5DC5GjzVH6Qqow' \
--data-urlencode 'redirect_uri=https://oidcdebugger.com/debug' \
--data-urlencode 'code=gezIJd4sNTEAHvqN_6MVwb'
```

Example response:

```json
{
  "access_token": "eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiJmYjdjZTg2Ni1lZWJkLTRlMmEtYjM4OC01MWJjMjUwOGU4ZDciLCJhenAiOiIyM2JiNDllNy05N2U4LTRjYjgtOGRjMC0xNWRiMTQ4NmI3ODgiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjIwNjYxMDQ0LCJpYXQiOjE2MjA2NTc0NDQsImp0aSI6IjEyZDQxZTc1LWZlMTgtNGJhNi05YTA0LWE5MTNiZTIxZDgwYSJ9.Qk9a_ogyMokjlnaLXQUGvTC5_5SQ1QX1ooWcRPSwiefuxS-Q3h7ZJQIULjwIhok7y9OpUe1r9ZvnFnFb8wSgu26Ntk9A6Ww_dwB_NifFgJc50KxD8cOQCHDGDWhP_3kuFdglVwMlJ6QC4FfQUh_U2ZAGP95msDNJj7w4mlzL4jSMUcVXgTvEM1O0X6l4Y8MUaNipuUixfsfnB02bagntTyWJmKjS9SjrGmk0Xq0oSybCLGRwnughFkzctafxoJn5veSkvsVe7dkYc5lx5sKNvvUc8UQfm_kj4reZe5Jrn2irGuWRlEexguwnYVXD7EO5Z3_dwRI-CRKPYl-44OK2dA",
  "token_type": "Bearer",
  "expires_in": 3599,
  "scope": "profile.birthnumber profile.phonenumber profile.zoneinfo openid profile.gender profile.titles profile.birthplaceNationality profile.name profile.idcards profile.locale profile.maritalstatus profile.legalstatus profile.email profile.paymentAccounts profile.addresses profile.birthdate profile.updatedat",
  "id_token": "eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiJmYjdjZTg2Ni1lZWJkLTRlMmEtYjM4OC01MWJjMjUwOGU4ZDciLCJhdWQiOiIyM2JiNDllNy05N2U4LTRjYjgtOGRjMC0xNWRiMTQ4NmI3ODgiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjIwNjU4MDQ0LCJpYXQiOjE2MjA2NTc0NDQsIm5vbmNlIjoiZmQ2OGFlMzAtMTVkNS00OTM3LWJlMWYtZjBhNWFjMDcxNDIyIiwianRpIjoiMjY2N2Q3YTUtNThjYS00MGY5LTgyMTctZjM4OWFmMzA4NzVkIn0.w0WDy2_2ilVIsFONxyGruyh4fVVoUMHhvV_j4R0YFFSZe8MgwUfMG9c22qyCmkEYJexlPSf7U4YeGEEf0rh4kzqhl8BQ-zX6VMokxYYsWMg0JJPqsk6Yt1C7q0s7BMQqJmmGKbqMmdijfT0vz3Qjj_RML9Y0N9kogRwC-YKXXZ5DCITd4PVjAUk8gy7YzflaAQwSf7YoHkLqlzHkKjcGYNVn-Qb9MjkfaMQEIN0-wEeOl2azabljHlePpTKqGOXpJMFHXxc-lJj8573KLBnd0rPyO_mzAHbInoZCHHHLfOJrqfbL_U7TsFc8ezgIf5RhCbkXi-oDnN0Byl0cuIFVHg"
}
```

## Call UserInfo

UserInfo is a standard OpenID Connect EndPoint which is fast and contains basic user information (if the user approved it).

```shell
curl --location --request GET 'https://oidc.sandbox.bankid.cz/userinfo' \
--header 'Authorization: Bearer eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiJmYjdjZTg2Ni1lZWJkLTRlMmEtYjM4OC01MWJjMjUwOGU4ZDciLCJhenAiOiIyM2JiNDllNy05N2U4LTRjYjgtOGRjMC0xNWRiMTQ4NmI3ODgiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjIwNjYxMDQ0LCJpYXQiOjE2MjA2NTc0NDQsImp0aSI6IjEyZDQxZTc1LWZlMTgtNGJhNi05YTA0LWE5MTNiZTIxZDgwYSJ9.Qk9a_ogyMokjlnaLXQUGvTC5_5SQ1QX1ooWcRPSwiefuxS-Q3h7ZJQIULjwIhok7y9OpUe1r9ZvnFnFb8wSgu26Ntk9A6Ww_dwB_NifFgJc50KxD8cOQCHDGDWhP_3kuFdglVwMlJ6QC4FfQUh_U2ZAGP95msDNJj7w4mlzL4jSMUcVXgTvEM1O0X6l4Y8MUaNipuUixfsfnB02bagntTyWJmKjS9SjrGmk0Xq0oSybCLGRwnughFkzctafxoJn5veSkvsVe7dkYc5lx5sKNvvUc8UQfm_kj4reZe5Jrn2irGuWRlEexguwnYVXD7EO5Z3_dwRI-CRKPYl-44OK2dA'
```

Example response.

```json
{
  "sub": "fb7ce866-eebd-4e2a-b388-51bc2508e8d7",
  "txn": "197e84b8-65f7-4bae-86eb-911e0f20223e",
  "verified_claims": {
    "claims": {
      "name": "Jan Novák",
      "given_name": "Jan",
      "family_name": "Novák",
      "gender": "male",
      "birthdate": "1970-08-01"
    }
  },
  "name": "Jan Novák",
  "given_name": "Jan",
  "family_name": "Novák",
  "nickname": "Fantomas",
  "preferred_username": "JanN",
  "email": "J.novak@email.com",
  "email_verified": true,
  "gender": "male",
  "birthdate": "1970-08-01",
  "zoneinfo": "Europe/Prague",
  "locale": "cs_CZ",
  "phone_number": "+420123456789",
  "phone_number_verified": false,
  "updated_at": 15681884330
}
```

## Call Profile

Profile EndPoint is specific to BankID and contains additional info about the end-user. It is also generally slower than UserInfo and should be called only when required.

```shell
curl --location --request GET 'https://oidc.sandbox.bankid.cz/profile' \
--header 'Authorization: Bearer eyJraWQiOiJycC1zaWduIiwiYWxnIjoiUFM1MTIifQ.eyJzdWIiOiJmYjdjZTg2Ni1lZWJkLTRlMmEtYjM4OC01MWJjMjUwOGU4ZDciLCJhenAiOiIyM2JiNDllNy05N2U4LTRjYjgtOGRjMC0xNWRiMTQ4NmI3ODgiLCJpc3MiOiJodHRwczpcL1wvb2lkYy5zYW5kYm94LmJhbmtpZC5jelwvIiwiZXhwIjoxNjIwNjYxMDQ0LCJpYXQiOjE2MjA2NTc0NDQsImp0aSI6IjEyZDQxZTc1LWZlMTgtNGJhNi05YTA0LWE5MTNiZTIxZDgwYSJ9.Qk9a_ogyMokjlnaLXQUGvTC5_5SQ1QX1ooWcRPSwiefuxS-Q3h7ZJQIULjwIhok7y9OpUe1r9ZvnFnFb8wSgu26Ntk9A6Ww_dwB_NifFgJc50KxD8cOQCHDGDWhP_3kuFdglVwMlJ6QC4FfQUh_U2ZAGP95msDNJj7w4mlzL4jSMUcVXgTvEM1O0X6l4Y8MUaNipuUixfsfnB02bagntTyWJmKjS9SjrGmk0Xq0oSybCLGRwnughFkzctafxoJn5veSkvsVe7dkYc5lx5sKNvvUc8UQfm_kj4reZe5Jrn2irGuWRlEexguwnYVXD7EO5Z3_dwRI-CRKPYl-44OK2dA'
```

Example response:

```json
{
  "idcards": [
    {
      "type": "ID",
      "description": "Občanský průkaz",
      "country": "CZ",
      "number": "123456789",
      "valid_to": "2023-10-11",
      "issuer": "Úřad městské části Praha 4",
      "issue_date": "2013-10-10"
    }
  ],
  "addresses": [
    {
      "type": "PERMANENT_RESIDENCE",
      "street": "Havlíčkova",
      "buildingapartment": "1064",
      "streetnumber": "3",
      "city": "Kladno 3",
      "zipcode": "27203",
      "country": "CZ",
      "ruian_reference": "18676"
    },
    {
      "type": "SECONDARY_RESIDENCE",
      "street": "Bezručova",
      "buildingapartment": "1215",
      "streetnumber": "33",
      "city": "Dolní Studénky",
      "zipcode": "78820",
      "country": "CZ",
      "ruian_reference": "11632"
    }
  ],
  "verified_claims": {
    "claims": {
      "given_name": "Jan",
      "family_name": "Novák",
      "gender": "male",
      "birthdate": "1970-08-01",
      "birthnumber": "7008010147",
      "birthplace": "Praha 4",
      "primary_nationality": "AT",
      "maritalstatus": "MARRIED",
      "addresses": [
        {
          "type": "PERMANENT_RESIDENCE",
          "street": "Havlíčkova",
          "buildingapartment": "1064",
          "streetnumber": "3",
          "city": "Kladno 3",
          "zipcode": "27203",
          "country": "CZ",
          "ruian_reference": "18676"
        },
        {
          "type": "SECONDARY_RESIDENCE",
          "street": "Bezručova",
          "buildingapartment": "1215",
          "streetnumber": "33",
          "city": "Dolní Studénky",
          "zipcode": "78820",
          "country": "CZ",
          "ruian_reference": "11632"
        }
      ],
      "idcards": [
        {
          "type": "ID",
          "description": "Občanský průkaz",
          "country": "CZ",
          "number": "123456789",
          "valid_to": "2023-10-11",
          "issuer": "Úřad městské části Praha 4",
          "issue_date": "2013-10-10"
        }
      ]
    }
  },
  "nationalities": ["CZ", "AT"],
  "paymentAccounts": ["CZ6550511833147245714362", "CZ3650514812229966227653"],
  "sub": "fb7ce866-eebd-4e2a-b388-51bc2508e8d7",
  "txn": "197e84b8-65f7-4bae-86eb-911e0f20223e",
  "given_name": "Jan",
  "family_name": "Novák",
  "gender": "male",
  "birthdate": "1970-08-01",
  "birthnumber": "7008010147",
  "age": 50,
  "majority": true,
  "birthplace": "Praha 4",
  "primary_nationality": "AT",
  "maritalstatus": "MARRIED",
  "email": "J.novak@email.com",
  "phone_number": "+420123456789",
  "pep": false,
  "limited_legal_capacity": false,
  "updated_at": 15681884330
}
```
