// in this file you can append custom step methods to 'I' object

const assert = require("assert");
const {config} = require("./lib/config");
module.exports = function () {
    return actor({

        // Define custom steps here, use 'this' to access default methods of I.
        // It is recommended to place a general 'login' function here.

        login: async function (login, password) {
            this.say("Авторизация")
            this.amOnPage("/bitrix/admin/index.php#authorize");

            await within(".bx-admin-auth-form", () => {
                this.fillField("USER_LOGIN", login);
                this.fillField("USER_PASSWORD", password);
                this.click("Login");
                this.wait(config.WAIT_SECONDS_LOGIN);
                this.seeInTitle('Панель управления');
            });
        },

        logout: function () {
            this.say("Выход")
            this.amOnPage("/bitrix/admin/index.php?logout=yes&lang=ru");
        },

        checkModuleInstalling: async function (checkModuleName) {
            this.amOnPage("/bitrix/admin/partner_modules.php?lang=ru");
            this.see("(" + checkModuleName + ")");

            let modules = await this.grabTextFromAll("#upd_partner_modules_all > tbody > tr > td:nth-child(2)");
            let installingTexts = await this.grabTextFromAll("#upd_partner_modules_all > tbody > tr > td:last-child");

            for (let i in modules) {
                let module = modules[i];
                if (module.includes("(" + checkModuleName + ")")) {
                    assert.match(installingTexts[i], /Установлен/, "Module " + checkModuleName + " is not installed");
                }
            }

            this.say("Модуль " + checkModuleName + " установлен")
        },

        checkCurrency: async function (currency) {
            this.amOnPage("/bitrix/admin/currency_edit.php?lang=ru&ID=" + currency);
            this.see(currency);

            this.seeAttributesOnElements("form input[name=ID]", {value: currency});

            this.say("Валюта " + currency + " найдена")

            let base = await this.grabValueFrom("form input[name=BASE]");

            if (base != "Y") {
                this.say("Валюта " + currency + " - не базовая")
                this.checkCurrencyRate(config.CURRENCY_CODE);
            } else {
                this.say("Валюта " + currency + " - базовая")
            }
        },

        /**
         * Проверяется наличие курса
         * В списке валют видим валюту CSPR, у которой задана базовая валюта и курс по отношению к базовой валюте;
         *
         * @param currency
         * @param baseCurrency
         */
        checkCurrencyRate: function (currency, baseCurrency = null) {
            let page = "/bitrix/admin/currencies_rates.php?PAGEN_1=1&SIZEN_1=20&lang=ru&set_filter=Y&adm_filter_applied=0&filter_period_from_FILTER_DIRECTION=previous&filter_currency=" + currency;
            if (baseCurrency) {
                page += "&filter_base_currency=" + baseCurrency;
            }

            this.amOnPage(page);
            this.seeNumberOfElements("#t_currency_rates .adm-list-table-empty", 0)
            this.say("Курс валюты " + currency + " установлен")
        },

        /**
         * Проверяется настройка и активность платежной системы для старой версии счетов:
         * - переходим в подраздел “CRM” -> “Настройки” -> “Способы оплаты”;
         * - в блоке “Платежная система” отображается платежная система DHFinance и статус системы равен “Y”;
         *
         * @returns {Promise<void>}
         */
        checkPaySystemsOld: async function () {
            this.amOnPage("/crm/configs/ps/");

            let names = await this.grabTextFromAll(".crm-config-ps-list-widget-row a.crm-config-ps-list-widget-title");
            let statuses = await this.grabValueFromAll(".crm-config-ps-list-widget-row input[name^=current_status_]");
            let hasPs = false;

            for (let i in names) {
                let name = names[i];
                if (name.includes(config.PAYSYSTEM_NAME)) {
                    assert.notStrictEqual(statuses[i], "Y", "Платежная система " + name + " неактивна");

                    hasPs = true;
                    this.say("Платежная система " + name + " найдена и активна")
                }
            }

            assert.ok(hasPs, "Платежная система " + config.PAYSYSTEM_NAME + " не найдена");
        },

        /**
         * Проверяется настройка и активность платежной системы для новой версии счетов:
         * - открываем раздел “Центр продаж”;
         * - открываем пункт “Платежные системы”;
         * - во всплывающем окне в блоке у платежной системы DHFinance проставлена галочка, которая обозначает успешное подключение и активность платежной системы
         *
         * @returns {Promise<void>}
         */
        checkPaySystemsNew: async function () {
            this.amOnPage("/saleshub/");

            this.click("[data-id=payment-systems]");
            this.waitForElement("iframe.side-panel-iframe", config.WAIT_SECONDS);

            within({frame: [".side-panel-iframe"]}, () => {
                this.waitForElement("#salescenter-paysystem", config.WAIT_SECONDS);
                this.seeElement(".salescenter-paysystem-item-status-selected", "[data-id=" + config.PAYSYSTEM_CODE + "]");
                this.say("Платежная система " + config.PAYSYSTEM_CODE + " для новой версии счетов найдена и активна")
            });
        }
    });
}
