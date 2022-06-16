const {I} = inject();

module.exports = {

    async check(expectedAmount) {

        /**
         * Первый переход по платежной ссылке (без кеша в браузере) отображает пустую страницу
         * Сообщениями об ошибках в консоли: net::ERR_HTTP2_PROTOCOL_ERROR 200
         * @see ../tests/smartInvoice.js
         */
        I.see(`Amount`);
        I.see('Comment');
        I.see(`${expectedAmount} CSPR`);

    }
}
