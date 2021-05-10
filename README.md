# Czech BankID examples

This project is dedicated to code examples for integration on
the [Czech Banking Identity](https://www.bankid.cz) solution (Czech BankID).
Samples can be tested directly against the Sandbox, which is available
through the BankID [Developer Portal](https://developer.bankid.cz).

Banking identity services make it easy to integrate authentication using
the banks' identities involved in the project into the Service Provider's
applications. The end-user can thus perform authentication using the
same authentication means to access his bank's banking services.

> With a simple implementation based on the OpenID Connect framework,
> the developer has the opportunity to get 5 million clients of Czech
> banks into their application.

We will gradually add examples in various programming languages to this
repository.

**Available examples:**

- [Java](/java) discovery, authentication, token exchange, and data transfer
- [Browser JavaScript](/javascript) Simple Implicit Flow using in-browser JavaScript. This flow uses Connect to login and later calls `/userinfo` EndPoint to acquire email of the end-user.
- [NodeJS](/nodejs) Code Flow using server-side NodeJS. These examples show Connect as well as Identify usage with popular NodeJS Express framework.
- [CURL](/curl) Bare-bones shell example which uses CURL and other command line tools
