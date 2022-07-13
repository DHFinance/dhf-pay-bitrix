const { I, dhfiPaymentPage } = inject();

module.exports = {

    container: ".order-pay-method-item-container",
    paymentMethodTitle: "DHFinance",

    async tryToPay(url, expectedAmount, expectedError = undefined) {

        I.amOnPage(url);

        I.say("Checking invoice page")
        I.seeElement(locate('.order-payment-method-container').as('Select payment method'));
        const dhfiPaymentBlock = locate(this.container)
            .withText(this.paymentMethodTitle)
            .as('Payment method: ' + this.paymentMethodTitle)
        ;
        I.seeElement(dhfiPaymentBlock);

        I.say("Checking payment page")

        const payButton = locate('.btn.btn-primary')
            .inside(dhfiPaymentBlock)
            .as('Pay button');
        I.click(payButton);
        I.waitForInvisible(payButton, 5);

        const error = locate('.alert-danger').as('error message');
        if (expectedError) {
            I.see(expectedError, error);
        } else {
            I.dontSeeElement(error);

            await dhfiPaymentPage.check(expectedAmount);
        }
    }

}
