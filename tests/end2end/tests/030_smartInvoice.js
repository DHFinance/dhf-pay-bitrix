Feature('3. New invoices payment');

Before(async ({I, loginAs}) => {
    await loginAs('admin');
});

Scenario('3.1. New invoice payment', async ({I, smartInvoiceStep, smartInvoicePublicPage}) => {

    I.amOnPage('/crm/');

    const amount = I.generateRandomInt(5, 25);
    const invoice = await smartInvoiceStep.create(amount);

    const publicUrl = await smartInvoiceStep.getPublicUrl(invoice)

    I.logout();
    await smartInvoicePublicPage.tryToPay(publicUrl, amount);

});

Scenario('3.2. There should be an error for invoices less than 2.5 CSPR', async ({I, smartInvoiceStep, smartInvoicePublicPage}) => {

    const amount = 1.5;
    const invoice = await smartInvoiceStep.create(amount);

    const publicUrl = await smartInvoiceStep.getPublicUrl(invoice)

    I.logout();
    await smartInvoicePublicPage.tryToPay(
        publicUrl,
        amount,
        await I.grabLanguage() === 'ru'
            ? 'Минимальная сумма для оплаты: 2.5 CSPR'
            : 'Minimum amount for payment: 2.5 CSPR'
    );

});

