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
        I.click(locate('[data-role="salescenter-starter"')
            .as('Кнопка «Продажи в SMS» на вкладке таймлайна SMS/WhatsApp')
        );

        const lang = await I.grabLanguage();
        const elements = lang === 'ru' ? {
            sidepanelTitle: 'CRM.Оплата',
            chooseOnlineCheckout: 'Выберите онлайн кассу',
            customerChatMessage: 'Клиент получит сообщение в чат',
            selectProductsToOrder: 'Выберите товары для оплаты',
            paysystemsAreOnline: 'Платёжные системы работают',
            dhFinancePaymentMethod: 'DHFinance',
            sendButton: 'Отправить',
            paymentLinkRegexp: /^Ссылка для оплаты (?<url>http[^\n]+)$/sm,
        } : {
            sidepanelTitle: 'CRM.Payment',
            chooseOnlineCheckout: 'Select online cash register',
            customerChatMessage: 'Customer receives a chat message',
            selectProductsToOrder: 'Select products to order',
            paysystemsAreOnline: 'Payment systems are online',
            dhFinancePaymentMethod: 'DHFinance',
            sendButton: 'Send',
            paymentLinkRegexp: /^Payment link: (?<url>http[^\n]+)$/sm,
        };

        const sidepanelIframe = '.side-panel-iframe';
        I.waitForElement(sidepanelIframe);
        await within({frame: sidepanelIframe}, async () => {
            I.waitForElement('.ui-page-slider-workarea', 5);
            I.see(elements.sidepanelTitle);
            I.waitForText(elements.chooseOnlineCheckout);

            const collapsableBlock = locate('.salescenter-app-payment-by-sms-item');
            I.seeElement(collapsableBlock
                .withText(elements.customerChatMessage)
                .as('Клиент получит сообщение в чат')
            );
            I.seeElement(collapsableBlock
                .withText(elements.selectProductsToOrder)
                .as('Выберите товары для оплаты')
            );
            I.seeElement(collapsableBlock
                .withText(elements.paysystemsAreOnline)
                .as('Платёжные системы работают')
            );

            I.see(elements.dhFinancePaymentMethod);

            I.click(elements.sendButton);
        });
        I.waitForInvisible(sidepanelIframe, 5);

        //#region Вытащим ссылку, появившуюся из поля ввода сообщения
        const textWithLink = await I.grabValueFrom('#smart_invoice_details_c1_timeline_sms');
        const textWithLinkRegexp = elements.paymentLinkRegexp;
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
