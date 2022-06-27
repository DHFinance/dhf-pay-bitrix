const assert = require("assert");
const {I} = inject();

module.exports = {
    locate: {
        names: "#upd_partner_modules_all > tbody > tr > td:nth-child(2)",
        installingTexts: "#upd_partner_modules_all > tbody > tr > td:last-child",
    },
    url: "/bitrix/admin/partner_modules.php",

    async installed(checkModuleName) {
        I.amOnPage(this.url);
        I.see("(" + checkModuleName + ")");

        let modules = await I.grabTextFromAll(this.locate.names);
        let installingTexts = await I.grabTextFromAll(this.locate.installingTexts);

        const lang = await I.grabLanguage();
        const regexp = lang === 'ru' ? /Установлен/ : /Installed/;

        for (let i in modules) {
            let module = modules[i];
            if (module.includes("(" + checkModuleName + ")")) {
                assert.match(installingTexts[i], regexp, "Module " + checkModuleName + " is not installed");
            }
        }
    },
}
