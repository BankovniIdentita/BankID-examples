# Czech BankID examples

This project is dedicated to code examples for integration on the [Czech BankID](https://www.bankid.cz) solution. Samples can be tested directly against the Sandbox, which is available through the BankID [Developer Portal](https://developer.bankid.cz).

Identity services make it easy to integrate authentication using the banks' identities involved in the project into the Service Provider's applications. The end-user can thus perform authentication using the same authentication means to access his bank's banking services.

> With a simple implementation based on the OpenID Connect framework, the developer has the opportunity to get over 5 million clients of Czech banks into their application.

We will gradually add examples in various programming languages to this repository.

**Available Authentication examples:**

- [CURL](/curl) Bare-bones shell example which uses CURL and other command line tools
- [Java](/java) Discovery, authentication, token exchange and data transfer
- [ASP.NET Core](/aspnet) Code Flow using BankID as well as Profile and UserInfo calls
- [NodeJS](/nodejs) Code Flow using server-side NodeJS. These examples show Connect as well as Identify usage with popular NodeJS Express framework.
- [Browser JavaScript](/javascript) Simple Implicit Flow using in-browser JavaScript. This flow uses Connect to login and later calls `/userinfo` EndPoint to acquire email of the end-user.
- [PHP](/php) Discovery, authentication, token exchange and data transfer

**Available Electronic Signature examples:**

- [NodeJS-sign](/nodejs-sign) Server-side JavaScript example of end to end signing of a PDF document
- [ASP.NET Core](/aspnet-sign)
- [PHP](/php)