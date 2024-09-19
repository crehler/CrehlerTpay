import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class TpayPaymentCheckPlugin extends Plugin {

    static options = {
        transactionId: '',
        successUrl: '',
        failUrl: '',
        checkUrl: '',
        waitingTime: 2,
        waitingSuccessText: "",
        waitingFailText: "",
    }

    init() {
        this._errors = [];
        this._client = new HttpClient();
        this.checksCount = 0;
        this.totalChecksCount = Math.floor(((this.options.waitingTime * 60000) / 10000));

        this._checkOrderPayment();
        this._checkPaymentStateInterval();
        this._hideChangePaymentFormButton();
    }

    _checkPaymentStateInterval() {
        const intervalTime = 10000;
        this.loopInterval = setInterval(this._loop.bind(this), intervalTime);
    }

    _loop() {
        this._checkOrderPayment();

        if (this.checksCount === this.totalChecksCount) {
            clearInterval(this.loopInterval);
            this._changeButtonText(this.options.waitingFailText);
            this._changeLocation(this.options.failUrl);
        }
    }

    _checkOrderPayment() {
        this.checksCount++;

        this._client.post(this.options.checkUrl, JSON.stringify({ transactionId: this.options.transactionId }) , this._showPaymentState.bind(this));
    }

    _showPaymentState(response) {
        let status = JSON.parse(response);

        if (status.success) {
            this._changeButtonText(this.options.waitingSuccessText);
            this._changeLocation(this.options.successUrl);
        } else if (!status.waiting) {
            this._changeButtonText(this.options.waitingFailText);
            this._changeLocation(this.options.failUrl);
        }
    }

    _changeButtonText(text) {
        console.log(text);
        this.el.querySelector('[data-info-text]').innerText = text;
    }

    _changeLocation(url) {
        setTimeout(() => {
            window.location.href = url;
        }, 1500)
    }

    _hideChangePaymentFormButton() {
        const waitingTime = this._getWaitingTimeFromPluginConfig();
        const halfIntervalTime = Math.floor(((waitingTime * 60) / 2) * 1000);
        const button = this.el.querySelector('.change-payment-method-btn');

        setTimeout(() => {
            button.style.display = "block";
        }, halfIntervalTime)
    }

    _getWaitingTimeFromPluginConfig() {
        const optionsString = this.el.dataset.tpayPaymentCheckOptions;
        const optionsObject = JSON.parse(optionsString);

        return optionsObject.waitingTime;
    }
}
