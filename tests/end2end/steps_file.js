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

    });
}
