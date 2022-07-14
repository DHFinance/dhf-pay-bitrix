# End-to-end testing

 NodeJS LTS with npm 8.12. Is required for work

## Installation:
1. Copy `.env.example` to `.env`,
2. Mark in `.env` URL of the test Bitrix24, admin’s login and password,
3. Proceed `npm install`

## Required settings of Bitrix24 for running tests

- Create «CRM + Internet-shop» in the «Shop» section
- Configure the SMS provider. Without it, a link to the payment can’t be generated,
- Set payment methods for the old and new accounts: for contacts and for companies,
- Payment method should be called «DHFinance», on the payment pages tests are oriented to that name.

## Running
- `npm run codeceptjs` or `npm run codeceptjs:ui`
- `npm run codeceptjs:headless` if there is no need to show the  browser window
