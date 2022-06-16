const assert = require("assert");
const {config} = require("../lib/config");

Feature("Module install and settings");

Before(async ({I, login}) => {
    await login.login(process.env.login, process.env.password);
});

Scenario("Check module " + config.MODULE_NAME + " installed)", async ({I, modules}) => {
    await modules.installed(config.MODULE_NAME);
});

Scenario("Check currency " + config.CURRENCY_CODE, async ({I, paysystems}) => {
    await paysystems.currencyExists(config.CURRENCY_CODE);
});

/**
 * Проверяется настройка и активность платежной системы для старой версии счетов:
 * - переходим в подраздел “CRM” -> “Настройки” -> “Способы оплаты”;
 * - в блоке “Платежная система” отображается платежная система DHFinance и статус системы равен “Y”;
 */
Scenario("Paysystem for old invoices", async ({I, paysystems}) => {
    await paysystems.paysystemInCrm(config.PAYSYSTEM_CODE);
});

/**
 * Проверяется настройка и активность платежной системы для новой версии счетов:
 * - открываем раздел “Центр продаж”;
 * - открываем пункт “Платежные системы”;
 * - во всплывающем окне в блоке у платежной системы DHFinance проставлена галочка, которая обозначает успешное подключение и активность платежной системы
 */
Scenario("Paysystem for new invoices", async ({I, paysystems}) => {
    await paysystems.paysystemInSalehub(config.PAYSYSTEM_CODE, config.WAIT_SECONDS);
});

After(({I, login}) => {
    login.logout();
});
