const { I, dhfiPaymentPage } = inject();

module.exports = {

    container: ".order-pay-method-item-container",
    paymentMethodTitle: "DHFinance",

    async tryToPay(url, expectedAmount, expectedError = undefined) {

        I.amOnPage(url);

        I.say("Проверяем страницу счета")
        I.see('Выберите способ оплаты');
        const dhfiPaymentBlock = locate(this.container)
            .withText(this.paymentMethodTitle) // @todo Способ оплаты должен быть назван так: добавить в README!
            .as('Способ оплаты: ' + this.paymentMethodTitle)
        ;
        I.seeElement(dhfiPaymentBlock);

        I.say("Проверяем страницу оплаты")

        const payButton = locate('.btn')
            .withText('Оплатить')
            .inside(dhfiPaymentBlock)
            .as('Кнопка «Оплатить»');
        I.click(payButton);
        I.waitForInvisible(payButton);

        const error = locate('.alert-danger').as('cообщение об ошибке');
        if (expectedError) {
            I.see(expectedError, error);
        } else {
            I.dontSeeElement(error);

            await dhfiPaymentPage.check(expectedAmount);
        }
    }

}
