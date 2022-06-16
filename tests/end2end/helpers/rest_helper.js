const Helper = require('@codeceptjs/helper');

class Rest extends Helper {

    async callRest(method, params = {}) {
        const browserHelper = this.helpers.Puppeteer || this.helpers.Playwright;
        return browserHelper.executeScript(async function ({method, params}) {
            // call Bitrix24 REST API
            return (new BX.RestClient())
                .callMethod(method, params)
                .then(result => result.data())
                .catch((err) => { throw new Error(`REST error: ${method} returned '${err.error_description()}'`); });
        }, {method, params});
    }

}

module.exports = Rest;
