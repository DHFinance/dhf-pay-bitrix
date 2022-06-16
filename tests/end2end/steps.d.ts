/// <reference types='codeceptjs' />
type steps_file = typeof import('./steps_file.js');
type login = typeof import('./lib/login.js');
type modules = typeof import('./lib/modules.js');
type paysystems = typeof import('./lib/paysystems.js');
type invoiceStep = typeof import('./steps/invoice.js');
type smartInvoiceStep = typeof import('./steps/smartInvoice.js');
type smartInvoicePublicPage = typeof import('./pages/smartInvoicePublicPage.js');
type Rest = import('./helpers/rest_helper.js');
type ChaiWrapper = import('codeceptjs-chai');

declare namespace CodeceptJS {
  interface SupportObject { I: I, current: any, login: login, modules: modules, paysystems: paysystems, invoiceStep: invoiceStep, smartInvoiceStep: smartInvoiceStep, smartInvoicePublicPage: smartInvoicePublicPage }
  interface Methods extends Rest, Puppeteer, ChaiWrapper {}
  interface I extends ReturnType<steps_file>, WithTranslation<Rest>, WithTranslation<ChaiWrapper> {}
  namespace Translation {
    interface Actions {}
  }
}
