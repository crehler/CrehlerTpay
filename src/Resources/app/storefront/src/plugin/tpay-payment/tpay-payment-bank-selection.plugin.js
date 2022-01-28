import Plugin from 'src/plugin-system/plugin.class';
import Iterator from 'src/helper/iterator.helper';
import DomAccess from 'src/helper/dom-access.helper';


export default class TpayPaymentBankSelectionPlugin extends Plugin {

    static options = {
        bankInputName: 'tpayBank',
        bankLabelSelector: '.tpay--list-item-container',
        formSelector: '#changePaymentForm',
        paymentMethodInputName: 'paymentMethodId',
        itemActiveClass: 'is--active',
        paymentId: '',
        changeTriggerSelector: '.tpay--bank-input'
    }

    init() {
        this._form = DomAccess.querySelector(document, this.options.formSelector);

        this.formAutoSubmitInstance = PluginManager.getPluginInstanceFromElement(this._form, 'FormAutoSubmit');
        this.formAutoSubmitInstance.options.changeTriggerSelectors.push(this.options.changeTriggerSelector);

        this._banks = DomAccess.querySelectorAll(this.el, this.options.bankLabelSelector);
        this._paymentInputPaymentInput = DomAccess.querySelector(
            document,
            `[name="${this.options.paymentMethodInputName}"][value="${this.options.paymentId}"]`
        );
        this._registerEvents();
    }

    _registerEvents() {
        const event = 'click';

        Iterator.iterate(this._banks, bankLabel => {
            bankLabel.addEventListener(event, this._onBankClick.bind(this))
        });
    }

    _onBankClick(e) {
        const lastActiveEl = this.el.querySelector('.' + this.options.itemActiveClass);
        e.stopPropagation();
        this._paymentInputPaymentInput.checked = true;
        if (lastActiveEl) {
            lastActiveEl.classList.remove(this.options.itemActiveClass);
        }
        e.target.parentElement.classList.add(this.options.itemActiveClass);
    }
}
