// in this file you can append custom step methods to 'I' object

const assert = require("assert");
module.exports = function () {
    return actor({

        // Define custom steps here, use 'this' to access default methods of I.
        // It is recommended to place a general 'login' function here.

        login: async function (login, password) {
            this.amOnPage("/bitrix/admin/index.php#authorize");

            await within(".bx-admin-auth-form", () => {
                this.fillField("USER_LOGIN", login);
                this.fillField("USER_PASSWORD", password);
                this.click("Login");
                this.wait(1);
                this.seeInTitle('Панель управления');
            });
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
        },

        checkCurrency(currency) {
            this.amOnPage("/bitrix/admin/currency_edit.php?lang=ru&ID=" + currency);
            this.see(currency);

            this.seeAttributesOnElements("form input[name=ID]", {value: currency});
        }
    });
}
