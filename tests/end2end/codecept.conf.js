const { setHeadlessWhen, setCommonPlugins } = require('@codeceptjs/configure');

// turn on headless mode when running with HEADLESS=true environment variable
// export HEADLESS=true && npx codeceptjs run
setHeadlessWhen(process.env.HEADLESS);

// enable all common plugins https://github.com/codeceptjs/configure#setcommonplugins
setCommonPlugins();

require('dotenv').config({ path: '.env' });

exports.config = {
  tests: './tests/*.js',
  output: './output',
  //translation: "ru-RU",
  helpers: {
    Rest: {
      require: './helpers/rest_helper.js',
    },
    Puppeteer: {
      url: process.env.url || "https://dhfi.s2.citruspro.ru",
      show: true,
      windowSize: '1200x900'
    },
    ChaiWrapper : {
      require: "codeceptjs-chai"
    }
  },
  include: {
    I: './steps_file.js',
    modules: "./lib/modules.js",
    paysystems: "./lib/paysystems.js",

    invoiceStep: './steps/invoice.js',
    smartInvoiceStep: './steps/smartInvoice.js',

    smartInvoicePublicPage: './pages/smartInvoicePublicPage.js',
    dhfiPaymentPage: './pages/dhfiPaymentPage.js',
  },
  plugins: {
    autoLogin: {
      enabled: true,
      saveToFile: true,
      inject: 'loginAs',
      users: {
        admin: {
          login: I => I.loginAsAdmin(),
          check: I => I.isLoggedIn('admin'),
        },
      },
    },
  },
  bootstrap: null,
  mocha: {},
  name: 'end2end'
}

