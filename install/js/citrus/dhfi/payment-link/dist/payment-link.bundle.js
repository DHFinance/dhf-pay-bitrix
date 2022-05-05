this.BX = this.BX || {};
(function (exports,main_core,main_popup) {
    'use strict';

    //import * as BX from 'main.core';
    var instance;
    var PaymentLink = /*#__PURE__*/function () {
      function PaymentLink() {
        babelHelpers.classCallCheck(this, PaymentLink);
      }

      babelHelpers.createClass(PaymentLink, [{
        key: "showForDetail",

        /**
         * @todo There should be another way to get invoice id?
         */
        value: function showForDetail() {
          var regex = /^invoice_(\d+)/;
          var editorId = BX.CrmProductEditor.getDefault().getId();
          var matches = editorId.match(regex);

          if (matches) {
            this.show(+matches[1], document.getElementById('crm_invoice_toolbar_document'));
          } else {
            this.handleError(new Error(main_core.Loc.getMessage('CITRUS_DHFI_PAYMENT_LINK_FAILED_TO_DETECT_INVOICE_ID')));
          }
        }
      }, {
        key: "show",
        value: function show(invoiceId, bindElement) {
          var _this = this;

          if (!main_core.Type.isInteger(invoiceId)) {
            console.warn('Unexpected invoiceId', {
              invoiceId: invoiceId
            });
            return;
          }

          BX.showWait(); // @todo cache results

          main_core.ajax.runAction('citrus:dhfi.integration.Invoice.getLink', {
            json: {
              invoiceId: invoiceId
            }
          }).then(function (_ref) {
            var data = _ref.data;

            _this.showPopup(data.url, bindElement);
          })["catch"](function (response) {
            _this.handleError(response);
          });
        }
      }, {
        key: "notify",
        value: function notify(message) {
          BX.UI.Notification.Center.notify({
            content: message,
            position: BX.UI.Notification.Position.TOP_CENTER
          });
        }
      }, {
        key: "handleError",
        value: function handleError(err, userMessage) {
          if (err instanceof Error) {
            err = err.message;
          } else if (babelHelpers["typeof"](err) === 'object' && err !== null && 'errors' in err) {
            err = err.errors[0].message;
          } // eslint-disable-next-line no-console


          console.error(err);
          this.notify(userMessage || err);
        }
      }, {
        key: "showPopup",
        value: function showPopup(url, bindElement) {
          BX.closeWait();
          new main_popup.Popup({
            id: 'citrus.dhfi-link',
            bindElement: bindElement,
            closeByEsc: true,
            minWidth: 430,
            autoHide: true,
            events: {
              onClose: function onClose(e) {
                e.getTarget().destroy();
              }
            },
            content: "\n            <div class=\"ui-ctl ui-ctl-wa\">\n                <input class=\"ui-ctl-element\"\n                       type=\"text\"\n                       value=\"".concat(url, "\"\n                       id=\"citrus-dhfi-generated-link\"\n                >\n                <button class=\"crm-invoice-edit-url-link-icon\" title=\"Copy to clipboard\" id=\"citrus-dhfi-clipboard-copy\" style=\"visibility: hidden\"></button>\n            </div>\n            ")
          }).show();
          var copyToClipboardButton = document.getElementById('citrus-dhfi-clipboard-copy');

          if (copyToClipboardButton) {
            BX.clipboard.bindCopyClick(copyToClipboardButton, {
              text: url
            });
            copyToClipboardButton.click();
          }

          var link = document.getElementById('citrus-dhfi-generated-link');

          if (link) {
            link.focus();
            link.select();
          }
        }
      }], [{
        key: "getInstance",
        value: function getInstance() {
          if (!instance) {
            instance = new PaymentLink();
          }

          return instance;
        }
      }]);
      return PaymentLink;
    }();
    main_core.Reflection.namespace('BX.Citrus.DHFi').PaymentLink = PaymentLink;

    exports.PaymentLink = PaymentLink;

}((this.BX[''] = this.BX[''] || {}),BX,BX.Main));
//# sourceMappingURL=payment-link.bundle.js.map
