# Installation and configuration instructions
## Brief description on installation and configuration 

The algorithm for installing and configuring the module after installation

1. **Installation algorithm, if module is not published on the marketplace:** 
`http://<website address>/bitrix/admin/update_system_partner.php?addmodule=<module code>`: 
    * website address – domen of Bitrix24 portal; 
    * module code - `citrus.dhfi`;
* Example link with module code: `http://<website address>/bitrix/admin/update_system_partner.php?addmodule=citrus.dhfi`;

2. **Client** installs module on **B24 Box from Bitris marketplace site management**:
- **CSPR** currency is created automatically. 
CSPR currency rate can be managed from module configuration [Currencies](https://training.bitrix24.com/support/training/course/index.php?COURSE_ID=178&LESSON_ID=23084&LESSON_PATH=17520.18658.4245.1143.23084);
- **For the old version of accounts Client** creates and configures payment system for payers:
* Configuration manual is desrcibed in [Bitrix24 official documentation](https://helpdesk.bitrix24.com/open/5872347/);
* Go to <details><summary>CRM → More → Settings → CRM Settings</summary><img alt="CRM → More → Settings → CRM Settings" src="./.docs/crm-settings.jpg"/></details>
* Choose <details><summary>Payment option → Payment systems</summary><img alt="Payment option → Payment systems" src="./.docs/payment-systems.jpg"/></details>
* Click on  *Create payment system*;
* In the form of creation of the payment system we fill in the lines:
* *Name* - `DHFinance`;
* *Handler* – Choose `DHFinance (dhfi)`;
 * *Active* – put in checkbox;
* *Client type* – creation of 2 payment systems is required, which will be specified by choosing the given feature, for one we choose *Contact*, for the second *Company*;
* *API key* – fill in the shop API key from DHFI service;
* *DHFI api server* – fill in the address of the API interaction service;
* Is required to create *2 payment systems* depending on the type of payers to whom the invoicing is made:
* Payment system for *Contacts*;
* Payment system for *Companies*;
* The given configuration is used for the old version of accounts;
* <details><summary>Example if filling in the form of payment system</summary><img alt="Example of filling in the form of the payment system" src="./.docs/image2.png"/></details>

- **For the new version of accounts: Client** creates and configures payment method in the “Sales center” section of the portal:
* Configuration instruction with detailed description is in [Bitrix24 official documentation](https://helpdesk.bitrix24.com/open/9613777/);
* <details><summary>Client goes to the section “Sales center” and clicks on “Payment systems”</summary><img alt="Sales center → Payment systems" src="./.docs/sales-center.png"/></details>
* <details><summary>Chooses in the subsection “Other payment system” system “DHFinance”</summary><img alt="Select paysystem" src="./.docs/select-paysystem.jpg"/></details>
* Fills in the configurations to activate the payment method;
* Saves changes;
3. Performs the necessary settings for data exchange via the API: in the payment system settings should be indicated the shop ID and API key from  [pay.dhfi.online](https://pay.dhfi.online/)

4. Payment system DHFI, after activation and correct configuration, is shown depending on the choosen settings in:
* Public page of the invoice (Illustration 2):
* Payment methods in the Shop **Bitrix24** on the website page (Illustration 3).

## Detailed description of the algorhythm of creation, sending and processing of the payment 

* Video examples of using the invoicing module:
* [Old invoices](https://user-images.githubusercontent.com/444489/178686899-9e67a3fe-945b-487a-8ce9-e5a84f961aab.webm)
 * [Sales in sms (Receive payment)](https://user-images.githubusercontent.com/444489/178687137-21a84b67-55dd-44a2-844a-5ce234c4edd0.webm)
* After the  **Client** has made an invoice on **Bitrix24** , the public link of an invoice is send to the customer via the choosen communication channel;
* The customer opens a given invoice link to choose the payment method and proceeding the payment;
* In a block “Pay Using” we can see an icon and the name of the payment method of **Module** - “DHFinance”;
* On the invoice page the customer chooses the payment system “DHFinance” and clicks on “Pay” or the icon of the payment system:
* Depends on the choosen type of the invoice;
* Module sends data request with the parameters to the side of the payment system “DHFI”
* Parameters are filled in from **Bitrix24**:
* Sum of the payment;
* Unique account identificator;
* The portal receives via API the ready-made formed link to the invoice;
* The customer is addressed to the page of the payment, a link to which the portal has received as the answer to the request of the parameters mentioned and described above;
* The customer proceeds payment on the side of DHFI;
* DHFI, after the payment is received, sends to the portal the information on the completed invoice via CSPR on the DHFI;
* Status of the invoice in the case of received data of the payment from DHFI, is changed to “Close invoice”.

## Illustrations

<details>
<summary> Illustraton 1 – Example of filling in the form of the payment system for the old accounts </summary>

![Illustration 1 – Example of filling in the form for the old accounts](./.docs/image2.png)

</details>

<details>
<summary>Illustration 2 – Public page of the invoice (Old account version)</summary>

![Illustration 2 – Public page of the invoice (Old account version)](./.docs/image1.png)

</details>

<details>
<summary>Illustration 3 – Public payment page (New accounts version – sales in chats)</summary>

![Illustration 3 – Public payment page (New accounts version – sales in chats)](./.docs/image3.png)

</details>

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
