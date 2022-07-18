# End-to-end testing

 NodeJS LTS with npm 8.12. Is required for work

## Installation:
1. Copy `.env.example` to `.env`,
2. Specify test Bitrix24 URL, admin login and password in `.env` file,
3. Proceed `npm install`

## Required settings of Bitrix24 for running tests

- Create «CRM + Internet-shop» in the «Shop» section
- Configure the SMS provider. Without it, a link to the payment can’t be generated,
- Set payment methods for the old and new accounts: for contacts and for companies,
- Payment method should be called «DHFinance», on the payment pages tests are oriented to that name.

## Running

- `npm run codeceptjs`: runs tests in console with visible browser window. Test results are output to the console upon competition.
- `npm run codeceptjs:ui`: opens UI to run all or seperate tests in browser window. Test results are in UI, click on a particular test to see its results in depth.  
- `npm run codeceptjs:headless` runs tests in console only with no browser window. Test results are output to the console upon competition. 
