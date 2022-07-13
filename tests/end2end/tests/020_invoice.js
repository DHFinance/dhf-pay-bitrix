Feature('2. Old invoices payment');

let prevBaseCurrency;

Before(async ({I, loginAs}) => {
    await loginAs('admin');

    prevBaseCurrency = await I.setInvoiceCurrency('CSP');
});

After(async ({I, loginAs}) => {
    await loginAs('admin');

    prevBaseCurrency = await I.setInvoiceCurrency(prevBaseCurrency);
});

Scenario('2.1. Old invoice payment', async ({I, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = I.generateRandomInt(5, 25);
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    I.logout();
    await invoiceStep.tryToPay(publicUrl, amount);

});

Scenario('2.2. There should be an error for invoices less than 2.5 CSPR', async ({I, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = 1.5;
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    I.logout();

    await invoiceStep.tryToPay(
        publicUrl,
        amount,
        await I.grabLanguage() === 'ru'
            ? 'Минимальная сумма для оплаты: 2.5 CSPR'
            : 'Minimum amount for payment: 2.5 CSPR'
    );

});

