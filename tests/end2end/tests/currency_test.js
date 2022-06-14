const {config} = require("../lib/config");

Feature("Currency CSPR");

Scenario("Check currency " + config.CURRENCY_CODE, ({I}) => {
    I.login(process.env.login, process.env.password);

    I.checkCurrency(config.CURRENCY_CODE);
});
