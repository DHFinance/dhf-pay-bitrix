const assert = require("assert");
const {config} = require("../lib/config");

Feature("Module install");

Scenario("Check module " + config.MODULE_NAME + " installed)", async ({I}) => {
    await I.login(process.env.login, process.env.password);

    await I.checkModuleInstalling(config.MODULE_NAME);
});
