Feature('Оплата старых счетов');

Before(async ({I, loginAs}) => {
    await loginAs('admin');
});

Scenario('Оплата старого счета', async ({I, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = I.generateRandomInt(5, 25);
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    I.logout();
    await invoiceStep.tryToPay(publicUrl, amount);

});

Scenario('Наличие ошибки для сумм < 2.5 CSPR', async ({I, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = 1.5;
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    I.logout();
    await invoiceStep.tryToPay(publicUrl, amount, 'Минимальная сумма для оплаты: 2.5 CSPR');

});

