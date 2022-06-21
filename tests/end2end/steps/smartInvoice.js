const assert = require("assert");
const {I} = inject();

module.exports = {

    /**
     *
     * @param {{id: number, url: string}} invoice
     * @returns {Promise<string>}
     */
    async getPublicUrl(invoice) {

        I.say("Создаём ссылку на счёт");
        I.amOnPage(invoice.url);

        const acceptPaymentButton = locate('.crm-entity-widget-content-block-inner-pay-button').as('Кнопка «Принять оплату»');
        I.waitForElement(acceptPaymentButton);
        I.seeElement(acceptPaymentButton);

        I.click(locate('#crm_scope_timeline_c_smart_invoice_1__sms').as('Вкладка SMS/WhatsApp в таймлайне'));
        I.click(locate('.crm-entity-stream-content-sms-button')
            .withText('Продажи в SMS')
            .as('Кнопка «Продажи в SMS»')
        );

        const sidepanelIframe = '.side-panel-iframe';
        I.waitForElement(sidepanelIframe);
        await within({frame: sidepanelIframe}, async () => {
            I.waitForElement('.ui-page-slider-workarea', 5);
            I.see('CRM.Оплата');
            I.waitForText('Выберите онлайн кассу');

            const collapsableBlock = locate('.salescenter-app-payment-by-sms-item');
            I.seeElement(collapsableBlock
                .withText('Клиент получит сообщение в чат')
                .as('Клиент получит сообщение в чат')
            );
            I.seeElement(collapsableBlock
                .withText('Выберите товары для оплаты')
                .as('Выберите товары для оплаты')
            );
            I.seeElement(collapsableBlock
                .withText('Платёжные системы работают')
                .as('Платёжные системы работают')
            );

            // @todo Проверить наличие DHFinance в списке ПС

            I.click('Отправить');
        });
        I.waitForInvisible(sidepanelIframe, 5);

        //#region Вытащим ссылку, появившуюся из поля ввода сообщения
        const textWithLink = await I.grabValueFrom('#smart_invoice_details_c1_timeline_sms');
        const textWithLinkRegexp = /^Ссылка для оплаты (?<url>http[^\n]+)$/sm;
        assert.match(textWithLink, textWithLinkRegexp, "Сcылка на оплату не найдена");
        const {groups} = textWithLink.match(textWithLinkRegexp);
        //#endregion

        return groups.url;
    },

    async create(amount = 5) {
        const entity = await this.getEntity();

        const product = await I.fetchOneProduct();

        const invoice = (await I.callRest('crm.item.add', {
            entityTypeId: entity.ID,
            fields: {
                title: `Тестовый счет от ${(new Date()).toLocaleString()}`,
            }
        })).item;

        await I.callRest('crm.item.productrow.set', {
            ownerType: entity.SYMBOL_CODE_SHORT,
            ownerId: invoice.id,
            currencyId: 'CSPR',
            productRows: [
                {
                    productId: product.ID,
                    productName: "Тестовый товар",
                    quantity: 1,
                    price: amount,
                }
            ],
        });

        return {
            id: invoice.id,
            url: `/crm/type/${entity.ID}/details/${invoice.id}/`,
        };
    },

    async getEntity() {
        if (this.entity) {
            return this.entity;
        }

        const entityTypes = await I.callRest('crm.enum.ownertype');
        this.entity = entityTypes.find((entityType) => {
            return entityType.SYMBOL_CODE === 'SMART_INVOICE';
        });

        return this.entity;
    },
}
