const assert = require("assert");
const {config} = require("../lib/config");

Feature("Module install and settings");

Before(async ({I}) => { // or Background
    await I.login(process.env.login, process.env.password);
});

Scenario("Check module " + config.MODULE_NAME + " installed)", async ({I}) => {
    await I.checkModuleInstalling(config.MODULE_NAME);
});

Scenario("Check currency " + config.CURRENCY_CODE, async ({I}) => {
    await I.checkCurrency(config.CURRENCY_CODE);
});

Scenario("Paysystem for old invoices", async ({I}) => {
    await I.checkPaySystemsOld();
});

Scenario("Paysystem for new invoices", async ({I}) => {
    await I.checkPaySystemsNew();
});

After(({I}) => {
    I.logout();
});
