const {config} = require("../lib/config");

Feature("1. Validate module installation and settings");

Before(async ({I, loginAs}) => {
    await loginAs('admin');
});

Scenario(`1.1. Module ${config.MODULE_NAME} should be installed`, async ({I, modules}) => {
    await modules.installed(config.MODULE_NAME);
});

Scenario(`1.2. Currency ${config.CURRENCY_CODE} should exist`, async ({I, paysystems}) => {
    await paysystems.currencyExists(config.CURRENCY_CODE);
});

/**
 * Paysystem settings and activity is checked for old CRM invoices:
 * - navigating to “CRM” -> “Settings” -> “Payment methods”;
 * - In “Payment system” block DHFinance paysystem should be displayed with status “Y”;
 */
Scenario("1.3. Paysystem for old invoices should exist", async ({I, paysystems}) => {
    await paysystems.paysystemInCrm(config.PAYSYSTEM_CODE);
});

/**
 * Paysystem settings and activity is checked for new CRM invoices::
 * - navigating to “Sales Center”;
 * - opening “Payment systems”;
 * - should see a popup containing DHFinance paysystem with checked checkbox, it means paysystem is active and successfuly setup
 */
Scenario("1.4. Paysystem for new invoices should exist", async ({I, paysystems}) => {
    await paysystems.paysystemInSalehub(config.PAYSYSTEM_CODE, config.WAIT_SECONDS);
});
