# BankID OpenId Connect Provider

A toolset to perform the authentication and authorization at BankId service. Supports OpenId Connect protocol.

## Features
- Simple: you only have to create the Provider, a core entity
- Extendable: you can adjust/extend the dependencies, if you wish
- PSR-compatible: feel free to pass you own PSR-compatible HTTP client (Guzzle one fits well) or PSR-compatible cache provider. Or don't, the library will work the either way.

## Documentation

See [documentation](documentation/README.md).

## Referenced libraries
- [`web-token/jwt-easy`](https://web-token.spomky-labs.com) (is used to validate the JWTs against exposed keys)
