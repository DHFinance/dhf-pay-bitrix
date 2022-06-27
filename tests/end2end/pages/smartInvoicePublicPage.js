const { I, dhfiPaymentPage } = inject();

module.exports = {

    container: ".order-pay-method-item-container",
    paymentMethodTitle: "DHFinance",

    async tryToPay(url, expectedAmount, expectedError = undefined) {

        I.amOnPage(url);

        I.say("Проверяем страницу счета")
        I.seeElement(locate('.order-payment-method-container').as('Выберите способ оплаты'));
        const dhfiPaymentBlock = locate(this.container)
            .withText(this.paymentMethodTitle)
            .as('Способ оплаты: ' + this.paymentMethodTitle)
        ;
        I.seeElement(dhfiPaymentBlock);

        I.say("Проверяем страницу оплаты")

        const payButton = locate('.btn.btn-primary')
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
