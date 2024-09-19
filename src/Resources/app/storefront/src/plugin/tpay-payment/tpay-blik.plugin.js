import Iterator from 'src/helper/iterator.helper';
import Plugin from 'src/plugin-system/plugin.class';
import PluginManager from 'src/plugin-system/plugin.manager';
import BlikApiService from './service/blik-api.service';
import BlikModalUtil from './Util/blik-modal.util';


const BLIK_MASK = new RegExp('^[0-9][0-9][0-9]+\\s[0-9][0-9][0-9]$');
const BLIK_DURATION_TIMEOUT = 500;

export default class TpayBlikPlugin extends Plugin {

    static options = {
        paymentMethodId: '',
        changedPayment: false,
        blikTransactionUrl: '',
        blikCheckStatusUrl: '',
        submitButtonSelector: '#confirmFormSubmit',
        blikCodeInputSelector: '.blik--input',
        pseudoModalAdditionalClass: 'blik-modal',
        modalChangeMethodButtonSelector: '.blik--modal-change-method-btn',
        modalInvalidCodeSelector: '.blik--modal-invalid-code',
        modalWaitCodeSelector: '.blik--modal-wait-state',
        modalErrorSelector: '.blik--modal-error-state',
        modalSuccessSelector: '.blik--modal-success-state',
        isInvalidClass: 'is-invalid',
        tos: {
            tosContainerSelector: '.confirm-tos'
        }

    }

    init() {
        this.$blikButtonSubmit = this.el.querySelector(this.options.submitButtonSelector);
        this.$allRequiredTosPositions = document.querySelectorAll(this.options.tos.tosContainerSelector + ' [required="required"]');
        this.$blikCodeInput = this.el.querySelector(this.options.blikCodeInputSelector);
        this.$blikModal = document.querySelector('.blik--modal');
        this.$isOrderCreated = false;

        this._errors = [];
        this._blikApiClient = new BlikApiService();

        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('submit', this._onSubmitForm.bind(this));
        this.$blikButtonSubmit.addEventListener('click', this._sendBlik.bind(this))

        Iterator.iterate(this.$allRequiredTosPositions, (tos) => {
            tos.addEventListener('change', () => {
                !tos.checked ? tos.classList.add(this.options.isInvalidClass) : tos.classList.remove(this.options.isInvalidClass);
            });

            tos.addEventListener('invalid', e => {
                e.target.scrollIntoView({behavior: 'smooth', block: 'center'})
            });
        })
    }

    _onSubmitForm(e) {
        e.preventDefault();
    }

    _sendBlik() {
        if (!this._tosValidation()) return;

        const pseudoModal = new BlikModalUtil(this.$blikModal.outerHTML);

        pseudoModal.open(this._onOpenBlik.bind(this));

        this.$blikModal = pseudoModal.getModal();

        if (!this._blikCodeValidation()) {
            this.$blikCodeInput.classList.add(this.options.isInvalidClass);

            this.$blikModal.querySelector('.blik--modal').classList.add(this.options.isInvalidClass);
        } else {
            if (this.options.changedPayment === false) {
                this._blikApiClient.sendBlikTransaction(new FormData(this.el), this._handleBlikTransaction.bind(this));
            } else {
                this._blikApiClient.sendBlikTransactionAgain(new FormData(this.el), this._handleBlikTransaction.bind(this));
            }
        }

        this.$blikModal.classList.add(this.options.pseudoModalAdditionalClass);
        this.$blikModal.querySelector('.modal-dialog').classList.add('modal-dialog-centered');

        if (!this.$isOrderCreated) {
            this.$blikModal.querySelector(this.options.modalChangeMethodButtonSelector).addEventListener('click', () => {
                pseudoModal.close();
            });
        }
    }

    _onOpenBlik() {
        this._registerModalEvents(this.$blikModal);

        PluginManager.initializePlugins();
    }

    _registerModalEvents(modal) {
        const modalBlikInput = modal.querySelector(this.options.blikCodeInputSelector);
        modal.querySelector(this.options.submitButtonSelector).addEventListener('click', () => {
            const invalidCodeStep = modal.querySelector(this.options.modalInvalidCodeSelector)

            if (!this._blikCodeValidation(modalBlikInput)) {
                modalBlikInput.classList.add(this.options.isInvalidClass);
                invalidCodeStep.classList.add(this.options.isInvalidClass);

            } else {
                modalBlikInput.classList.remove(this.options.isInvalidClass);
                this.$blikModal.querySelector('.blik--modal').classList.remove(this.options.isInvalidClass);
                const data = new FormData(this.el);
                data.set('blikCode', modalBlikInput.value)

                if (this._notPaidOrderId) {
                    data.append('orderId', this._notPaidOrderId);
                    data.append('paymentMethodId', this.options.paymentMethodId);
                    this._blikApiClient.sendBlikTransactionAgain(data, this._handleBlikTransaction.bind(this));
                } else {
                    this._blikApiClient.sendBlikTransaction(data, this._handleBlikTransaction.bind(this));
                }
            }
        });
    }

    _handleBlikTransaction(response) {
        const json = JSON.parse(response);

        if (json.orderId) {
            this.$isOrderCreated = true;
            [...this.$blikModal.querySelectorAll(this.options.modalChangeMethodButtonSelector)]
                .forEach(el => {
                        el.classList.remove('d-none')
                        el.addEventListener('click', () => {
                            window.location.replace(`/account/order/edit/${json.orderId}`)
                        })
                    }
                );
        }

        if (!this._handleErrors(json)) {
            return false
        } else if (json.success && json.orderId) {
            this._finishUrl = json.finishUrl;
            this._orderId = json.orderId;
            setTimeout(() => {
                this._blikApiClient.checkPaymentState(json.orderId, this._handleCheckPaymentState.bind(this));
            }, BLIK_DURATION_TIMEOUT)
        }
    }

    _handleCheckPaymentState(response) {
        const json = JSON.parse(response);

        if (!this._handleErrors(json)) {
            return false;
        } else if (json.waiting) {
            setTimeout(() => {
                this._blikApiClient.checkPaymentState(this._orderId, this._handleCheckPaymentState.bind(this));
            }, BLIK_DURATION_TIMEOUT)
        } else {
            if (json.success) {
                this.$blikModal.classList.add('is--success');
                this.$blikModal.querySelector('.blik--modal-message-success').classList.remove('is--hidden');
                this.$blikModal.querySelector('.blik--modal-message-wait').classList.add('is--hidden');
                setTimeout(() => {
                    window.location.replace(this._finishUrl);
                }, 5000);
            }
        }
    }

    _handleErrors(json) {
        if (json.waiting) {
            return true;
        } else if (typeof json.error !== 'undefined' && json.error.length > 0) {
            this._errors += json.error;
            return false;
        } else if (!json.success && !json.blikCodeValid) {
            this._notPaidOrderId = json.orderId;
            this.$blikModal.querySelector('.blik--modal').classList.add(this.options.isInvalidClass, 'is-blik-invalid');
            return false;
        }
        return true
    }

    _tosValidation() {
        if (this.$allRequiredTosPositions.length === 0) {
            return true;
        }
        Iterator.iterate(this.$allRequiredTosPositions, (tos) => {
            !tos.checked ? tos.classList.add(this.options.isInvalidClass) : tos.classList.remove(this.options.isInvalidClass);
        })

        const invalidTos = [];
        [].forEach.call(this.$allRequiredTosPositions, (tos) => {
            if (tos.classList.contains(this.options.isInvalidClass)) {
                invalidTos.push(true)
            }
        });

        return invalidTos.length === 0;
    }

    _blikCodeValidation(input) {
        if (typeof input === 'undefined') {
            input = this.$blikCodeInput;
        }
        if (input.value.length === 0) {
            input.classList.add(this.options.isInvalidClass);
            return false;
        }

        return BLIK_MASK.test(input.value);
    }
}
