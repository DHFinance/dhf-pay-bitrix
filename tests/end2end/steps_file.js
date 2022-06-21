// in this file you can append custom step methods to 'I' object

module.exports = function () {
    return actor({

        // Define custom steps here, use 'this' to access default methods of I.
        // It is recommended to place a general 'login' function here.

        generateRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min) + min);
        },

        async fetchOneProduct() {
            const result = await this.callRest('crm.product.list', {
                order: {ID: 'ASC'},
                select: ["ID", "NAME", "CURRENCY_ID", "PRICE"]
            });

            this.assertLengthAboveThan(result, 0);
            return result[0];
        },

        async login(login, password) {
            this.say("Авторизация")
            this.amOnPage("/bitrix/admin/index.php#authorize");
            await within(".bx-admin-auth-form", () => {
                this.fillField('USER_LOGIN', login);
                this.fillField('USER_PASSWORD', password);
                this.click('Запомнить меня на этом компьютере');
                this.click('Login');
            });
        },

        loginAsAdmin() {
            return this.login(process.env.login, process.env.password);
        },

        async isLoggedIn(login) {
            this.amOnPage("/bitrix/admin/index.php");
            this.seeElement('#bx-panel-logout');
            const bitrixLogin = await this.grabCookie('BITRIX_SM_LOGIN');
            return login === bitrixLogin;
        },

        logout() {
            this.say("Выход")
            this.amOnPage("/bitrix/admin/index.php?logout=yes&lang=ru");
        },
    });
}
