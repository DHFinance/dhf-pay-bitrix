Feature('payment');

Scenario('test something', ({ I }) => {
    I.amOnPage('/bitrix/admin/index.php#authorize');

    within('.bx-admin-auth-form', () => {
        I.fillField('USER_LOGIN', 'admin');
        I.fillField('USER_PASSWORD', 'kq-fkxL_T#<_6Y,-0w');
        I.click('button');
    });

    I.amOnPage('/bitrix/admin/partner_modules.php?lang=ru');

    I.see("citrus.dhfi");

    "//*[@id=\"upd_partner_modules_all\"]/tbody/tr[1]/td[7]"

   // I.pause('GitHub');
});
