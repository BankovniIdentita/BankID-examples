# Czech Bank iD examples

This project is dedicated to code examples for integration on the [Czech Bank iD](https://www.bankid.cz) solution. Samples can be tested directly against the Sandbox, which is available through the Bank iD [Developer Portal](https://developer.bankid.cz).

Identity services make it easy to integrate authentication using the banks' identities involved in the project into the Service Provider's applications. The end-user can thus perform authentication using the same authentication means to access his bank's banking services.

> With a simple implementation based on the OpenID Connect framework, the developer has the opportunity to get over 5 million clients of Czech banks into their application.

We will gradually add examples in various programming languages to this repository.

**Please always check the implementation against [specs](https://github.com/BankovniIdentita/bankid-api-docs) in order to be fully compliant, some of our examples are old and not updated.**

**Available Authentication examples:**

- [CURL](/curl) Bare-bones shell example which uses CURL and other command line tools
- [Java](/java) Discovery, authentication, token exchange and data transfer
- [ASP.NET Core](/aspnet) Code Flow using BankID as well as Profile and UserInfo calls
- [NodeJS](/nodejs) Code Flow using server-side NodeJS. These examples show Connect as well as Identify usage with popular NodeJS Express framework.
- [Browser JavaScript](/javascript) Simple Implicit Flow using in-browser JavaScript. This flow uses Connect to login and later calls `/userinfo` EndPoint to acquire email of the end-user.
- [PHP](/php) Discovery, authentication, token exchange and data transfer

**External examples:**
- [Online registration via Bank iD for Koha](https://gitlab.com/open-source-knihovna/online-registration-bank-id-for-koha) Plugin for Koha library software

**Available Electronic Signature examples:**

- [NodeJS-sign](/nodejs-sign) Server-side JavaScript example of end to end signing of a PDF document
- [ASP.NET Core](/aspnet-sign)
- [PHP](https://github.com/BankovniIdentita/bankid-php-client)

Example of Signing implementation is also available in Bank iD Demo repository.
