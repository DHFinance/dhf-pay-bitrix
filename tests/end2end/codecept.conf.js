const { setHeadlessWhen, setCommonPlugins } = require('@codeceptjs/configure');

// turn on headless mode when running with HEADLESS=true environment variable
// export HEADLESS=true && npx codeceptjs run
setHeadlessWhen(process.env.HEADLESS);

// enable all common plugins https://github.com/codeceptjs/configure#setcommonplugins
setCommonPlugins();

require('dotenv').config({ path: '.env' });

exports.config = {
  tests: './tests/*_test.js',
  output: './output',
  helpers: {
    Puppeteer: {
      url: process.env.url || "https://dhfi.s2.citruspro.ru",
      show: true,
      windowSize: '1200x900'
    }
  },
  include: {
    I: './steps_file.js',
    login: "./lib/login.js",
    modules: "./lib/modules.js",
    paysystems: "./lib/paysystems.js",
  },
  bootstrap: null,
  mocha: {},
  name: 'end2end'
}
