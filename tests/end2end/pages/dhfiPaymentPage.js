const {I} = inject();

module.exports = {

    async check(expectedAmount) {

        /**
         * First navigation without browser cache leads to empty page
         * with errors in browser console: net::ERR_HTTP2_PROTOCOL_ERROR 200
         * @see ../tests/smartInvoice.js
         */
        I.see(`Amount`);
        I.see('Comment');
        I.see(`${expectedAmount} CSPR`);

    }
}
