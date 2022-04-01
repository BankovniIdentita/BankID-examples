# BankID OpenId Connect Provider - Documentation

## Overview

This library is inspired by Fluent Interface concept, that is supposed to be something easy to understand and configure. At least, easier that the alternative approach (e.g. "to pass an array of configs").

Choose where to start:
* [quickstart](./QUICKSTART.md)
* [examples](./examples/README.md)

## 3rd party libraries used

1. web-token/jwt-easy - the library that provides a shiny cool way to simply validate the signature against the exposed JWKs.

## To run locally [to be removed]

1. Ensure your PC is capable of running PHP 8.1
2. `composer install`
3. `composer run start`
4. Go to `http://localhost:3000` and follow the redirect
5. Allow the access and all the required scopes
6. Observe the unwrapped data and some of the real API calls
