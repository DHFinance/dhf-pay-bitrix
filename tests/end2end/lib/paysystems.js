const {config} = require("./config");
const assert = require("assert");
const {I} = inject();

module.exports = {
    urls: {
        currency: "/bitrix/admin/currency_edit.php?lang=ru",
        crmPaysystems: "/crm/configs/ps/",
        saleshub: "/saleshub/"
    },

    async currencyExists(currency) {
        I.amOnPage(this.urls.currency + "&ID=" + currency);
        I.see(currency);
        I.seeAttributesOnElements("form input[name=ID]", {value: currency});
        I.say("Валюта " + currency + " найдена");
    },

    async paysystemInCrm(paysystem, checkActive = true) {
        I.amOnPage(this.urls.crmPaysystems);

        let links = await I.grabAttributeFromAll(".crm-config-ps-list-widget-row a.crm-config-ps-list-widget-title", "href");
        let hasPs = false;

        for (let i in links) {
            let link = links[i];
            await I.amOnPage(link);

            let name = await I.grabAttributeFrom("#PS_INFO input[name=NAME]", "value");
            let selected = await I.grabAttributeFrom("#PS_INFO #ACTION_FILE option[selected]", "value");
            let active = await I.grabAttributeFrom("#PS_INFO input[name=ACTIVE]", "value");

            if (selected == paysystem) {
                if (checkActive) {
                    assert.strictEqual(active, "Y", "Платежная система " + name + " неактивна");
                }

                I.say("Платежная система " + name + " (" + paysystem + ") для старой версии счетов найдена" + (checkActive ? " и активна" : ""));

                hasPs = true;
            }
        }

        assert.ok(hasPs, "Платежная система " + paysystem + " для старой версии счетов не найдена");
    },

    async paysystemInSalehub(paysystem, waitSeconds = 5) {
        I.amOnPage(this.urls.saleshub);

        I.click("[data-id=payment-systems]");
        I.waitForElement("iframe.side-panel-iframe", waitSeconds);

        await within({frame: [".side-panel-iframe"]}, () => {
            I.waitForElement("#salescenter-paysystem", waitSeconds);
            I.seeElement(".salescenter-paysystem-item-status-selected", "[data-id=" + paysystem + "]");
            I.say("Платежная система " + paysystem + " для новой версии счетов найдена и активна")
        });
    }
}
