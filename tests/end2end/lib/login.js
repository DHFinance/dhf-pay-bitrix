const {I} = inject();

module.exports = {

    /**
     * locators
     */
    form: ".bx-admin-auth-form",
    fields: {
        login: 'USER_LOGIN',
        password: 'USER_PASSWORD'
    },
    submitButton: "Login",

    /**
     * Urls
     */
    loginUrl: "/bitrix/admin/index.php#authorize",
    logoutUrl: "/bitrix/admin/index.php?logout=yes&lang=ru",

    async login(login, password) {
        I.say("Авторизация")
        I.amOnPage(this.loginUrl);

        await within(this.form, () => {
            I.fillField(this.fields.login, login);
            I.fillField(this.fields.password, password);
            I.click(this.submitButton);
        });
    },

    logout() {
        I.say("Выход")
        I.amOnPage(this.logoutUrl);
    },
}
