const {I, dhfiPaymentPage} = inject();

module.exports = {

    async fetchCompany() {
        const result = await I.callRest('crm.company.list', {
            order: {"DATE_CREATE": "ASC"},
            select: ["ID", "TITLE"]
        });
        I.assertLengthAboveThan(result, 0);
        return result[0];
    },

    /**
     *
     * @param {{id: number, url: string}} invoice
     * @returns {Promise<string>}
     */
    async getPublicUrl(invoice) {

        I.amOnPage(invoice.url);

        /**
         * Меню Действия → Ссылка на счет
         */
        I.click('#crm_invoice_toolbar_leftMenu', '.bx-crm-view-menu');
        // noinspection JSUnresolvedFunction
        I.executeScript(() => generateExternalLink(BX("crm_invoice_toolbar_leftMenu")));

        I.waitForElement('#generated-link');
        return I.grabValueFrom('#generated-link');
    },

    async create(amount = 5) {

        const company = await this.fetchCompany();
        const product = await I.fetchOneProduct();

        const personTypes = await I.callRest("crm.persontype.list");
        const companyType = personTypes.find(t => t.NAME === 'CRM_COMPANY');

        const paysystems = await I.callRest("crm.paysystem.list");
        const companyPaysystems = paysystems.filter(p => p.PERSON_TYPE_ID == companyType.ID);
        I.assertLengthAboveThan(companyPaysystems, 0);

        const id = await I.callRest('crm.invoice.add', {
            fields: {
                PERSON_TYPE_ID: companyType.ID,
                UF_COMPANY_ID: company.ID,
                PAY_SYSTEM_ID: companyPaysystems[0].ID,
                ORDER_TOPIC: `Тестовый счет от ${(new Date()).toLocaleString()}`,
                STATUS_ID: "N",
                PRODUCT_ROWS: [
                    {"ID": 0, "PRODUCT_ID": product.ID, "PRODUCT_NAME": "Тестовый товар", "QUANTITY": 1, "PRICE": amount},
                ],
            }
        });

        return {
            id,
            url: `/crm/invoice/show/${id}/`,
        };
    },

    /**
     *
     * @param {string} url
     * @param {number} amount
     * @param {string} expectedError
     */
    async tryToPay(url, amount = 5, expectedError = undefined) {
        I.amOnPage(url);
        I.seeElement(locate('.crm-invoice-payment-system').as('Оплатить через'));

        const formattedAmount = amount.toFixed(2).replace('.', ',').replace(',00', '');
        I.see(`${formattedAmount} CSPR`, '.crm-invoice-payment-total-sum');

        const dhfiMethodBlock = locate('.crm-invoice-payment-system-image-block')
            .withText('DHFinance')
            .as('Способ оплаты DHFinance');

        I.seeElement(dhfiMethodBlock);
        I.click(dhfiMethodBlock);

        const paysystemResponse = locate('.crm-invoice-payment-client-template')
            .inside('.crm-invoice-payment-system-template')
            .as('Ответ платежной системы');

        I.waitForElement(paysystemResponse);

        if (expectedError) {
            I.see(expectedError, paysystemResponse);
        } else {
            I.click(locate('.btn.btn-success')
                .as('Кнопка «Оплатить»')
                .inside(paysystemResponse));

            await dhfiPaymentPage.check(amount);
        }

    },
}
