//import * as BX from 'main.core';
import {ajax as Ajax, Type, Reflection, Loc} from 'main.core';
import {Popup} from 'main.popup'
import 'ui.buttons';
import 'ui.notification';

let instance;

export class PaymentLink {
    constructor() {
    }

    static getInstance() {
        if (!instance) {
            instance = new PaymentLink();
        }
        return instance;
    }

    /**
     * @todo There should be another way to get invoice id?
     */
    showForDetail() {
        const regex = /^invoice_(\d+)/;
        const editorId = BX.CrmProductEditor.getDefault().getId();
        const matches = editorId.match(regex);
        if (matches) {
            this.show(+matches[1], document.getElementById('crm_invoice_toolbar_document'));
        } else {
            this.handleError(new Error(Loc.getMessage('CITRUS_DHFI_PAYMENT_LINK_FAILED_TO_DETECT_INVOICE_ID')))
        }
    }

    show(invoiceId, bindElement) {
        if (!Type.isInteger(invoiceId)) {
            console.warn('Unexpected invoiceId', { invoiceId });
            return;
        }
        BX.showWait();
        // @todo cache results
        Ajax.runAction('citrus:dhfi.integration.Invoice.getLink', {
            json: {
                invoiceId,
            },
        }).then(({data}) => {
            this.showPopup(data.url, bindElement);
        }).catch((response) => {
            this.handleError(response);
        });
    }

    notify(message) {
        BX.UI.Notification.Center.notify({
            content: message,
            position: BX.UI.Notification.Position.TOP_CENTER,
        });
    }

    handleError(err, userMessage) {
        if (err instanceof Error) {
            err = err.message;
        } else if (typeof err === 'object'
            && err !== null
            && 'errors' in err
        ) {
            err = err.errors[0].message;
        }

        // eslint-disable-next-line no-console
        console.error(err);
        this.notify(userMessage || err);
    }

    showPopup(url, bindElement) {

        BX.closeWait();
        (new Popup({
            id: 'citrus.dhfi-link',
            bindElement: bindElement,
            closeByEsc: true,
            minWidth: 430,
            autoHide: true,
            events: {
                onClose: (e) => {
                    e.getTarget().destroy();
                },
            },
            content: `
            <div class="ui-ctl ui-ctl-wa">
                <input class="ui-ctl-element"
                       type="text"
                       value="${url}"
                       id="citrus-dhfi-generated-link"
                >
                <button class="crm-invoice-edit-url-link-icon" title="Copy to clipboard" id="citrus-dhfi-clipboard-copy" style="visibility: hidden"></button>
            </div>
            `
        }))
            .show();

        const copyToClipboardButton = document.getElementById('citrus-dhfi-clipboard-copy');
        if (copyToClipboardButton) {
            BX.clipboard.bindCopyClick(copyToClipboardButton, {text: url});
            copyToClipboardButton.click();
        }

        const link = document.getElementById('citrus-dhfi-generated-link');
        if (link) {
            link.focus();
            link.select();
        }
    }
}

Reflection.namespace('BX.Citrus.DHFi').PaymentLink = PaymentLink;

