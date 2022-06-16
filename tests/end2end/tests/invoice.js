Feature('Оплата старых счетов');

Before(async ({I, login}) => {
    await login.login(process.env.login, process.env.password);
});

Scenario('Оплата старого счета', async ({I, login, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = I.generateRandomInt(5, 25);
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    login.logout();
    await invoiceStep.tryToPay(publicUrl, amount);

});

Scenario('Наличие ошибки для сумм < 2.5 CSPR', async ({I, login, invoiceStep}) => {

    I.amOnPage('/crm/invoice/list/');

    const amount = 1.5;
    const invoice = await invoiceStep.create(amount);

    const publicUrl = await invoiceStep.getPublicUrl(invoice)

    login.logout();
    await invoiceStep.tryToPay(publicUrl, amount, 'Минимальная сумма для оплаты: 2.5 CSPR');

});

