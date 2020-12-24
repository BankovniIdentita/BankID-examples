#!/bin/bash

# Register an application, configure sandbox and open the authorization url from example
xdg-open "https://core-idp.staging.ci.bankd.cz/auth?client_id=7261040c-a2b6-4688-8389-4dc9d77a1ba6&redirect_uri=https%3A%2F%2Foidcdebugger.com%2Fdebug&scope=profile.birthnumber%20profile.zoneinfo%20profile.gender%20openid%20profile.titles%20profile.name%20profile.birthplaceNationality%20profile.locale%20profile.updated_at%20profile.idcards%20profile.maritalstatus%20profile.phone_number%20profile.legalstatus%20profile.email%20profile.paymentAccounts%20profile.addresses%20profile.birthdate&response_type=token&state=BankID%20works%21&nonce=81b9e1bb-bc97-435f-8ca9-6e87d23bc5db&prompt=login&display=page&acr_values=loa2"

# Copy the access token and call the userinfo endpoint
ACCESS_TOKEN="eyJraWQiOiJyc2ExIiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiIyNjBlMmVmYS04NWQxLTQwOWQtYWQzMi1kMzZjNWIzNmUxYjUiLCJhenAiOiI3MjYxMDQwYy1hMmI2LTQ2ODgtODM4OS00ZGM5ZDc3YTFiYTYiLCJpc3MiOiJodHRwczpcL1wvY29yZS1pZHAuc3RhZ2luZy5jaS5iYW5rZC5jelwvIiwiZXhwIjoxNjA2OTAwNDY5LCJpYXQiOjE2MDY4OTY4NjksImp0aSI6ImYwNzIyMmJhLWFhMDAtNDUwNy05MDE3LWE2OWFhOTZiNWZjZSJ9.DDG7I6xLZ8QwLMupErB1o9hTPIPNdtqYCj0W_H6NK5iIbpU-LIcZ199rizL0H6KyNeNj94xU4Kt7lNgsFx3ycK7MIfNdz1QLGMxAsEmg-co8F3lgRZ-CO3Pa6ehtXi91EF7_VQgB9EGM0bYyGnrU5jB5jlotT2RoaWqH2hn7LioNwjunU_HRKJ4AjRc2MIkWoW8aDPWMdcS0ntS5g5XeJ9aOe8ZOKIOZjINzbXLz05BLdGoi3LA8ZxJSBI4omM1k7U4oJI9Q8Cr74Yq2aDlxy78R7yDOFH5Bj8ikAjJzI1lHiSc1imUvw9n5Yep2XNYXgq_Bs4P2QWdJCmRSVbleww"
USERINFO=$(curl --location --request GET 'https://core-idp.staging.ci.bankd.cz/userinfo' --header 'Authorization: Bearer $ACCESS_TOKEN')

# Print phone number
echo "$RESP" | jq '.phone_number'