Feature('Оплата новых счетов');

Before(async ({I, login}) => {
    await login.login(process.env.login, process.env.password);
});

Scenario('Оплата нового счета', async ({I, login, smartInvoiceStep, smartInvoicePublicPage}) => {

    I.amOnPage('/crm/');

    const amount = I.generateRandomInt(5, 25);
    const invoice = await smartInvoiceStep.create(amount);

    const publicUrl = await smartInvoiceStep.getPublicUrl(invoice)

    login.logout();
    await smartInvoicePublicPage.tryToPay(publicUrl, amount);

});

Scenario('Наличие ошибки для сумм < 2.5 CSPR', async ({I, login, smartInvoiceStep, smartInvoicePublicPage}) => {

    const amount = 1.5;
    const invoice = await smartInvoiceStep.create(amount);

    const publicUrl = await smartInvoiceStep.getPublicUrl(invoice)

    login.logout();
    await smartInvoicePublicPage.tryToPay(publicUrl, amount, 'Минимальная сумма для оплаты: 2.5 CSPR');

});

