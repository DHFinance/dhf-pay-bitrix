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
 * Проверяется настройка и активность платежной системы для старой версии счетов:
 * - переходим в подраздел “CRM” -> “Настройки” -> “Способы оплаты”;
 * - в блоке “Платежная система” отображается платежная система DHFinance и статус системы равен “Y”;
 */
Scenario("1.3. Paysystem for old invoices should exist", async ({I, paysystems}) => {
    await paysystems.paysystemInCrm(config.PAYSYSTEM_CODE);
});

/**
 * Проверяется настройка и активность платежной системы для новой версии счетов:
 * - открываем раздел “Центр продаж”;
 * - открываем пункт “Платежные системы”;
 * - во всплывающем окне в блоке у платежной системы DHFinance проставлена галочка, которая обозначает успешное подключение и активность платежной системы
 */
Scenario("1.4. Paysystem for new invoices should exist", async ({I, paysystems}) => {
    await paysystems.paysystemInSalehub(config.PAYSYSTEM_CODE, config.WAIT_SECONDS);
});
