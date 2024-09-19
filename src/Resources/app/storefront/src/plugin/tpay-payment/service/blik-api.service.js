import HttpClient from 'src/service/http-client.service';

export default class BlikApiService {

    constructor() {
        this._httpClient = new HttpClient();
    }

    sendBlikTransaction(data, callback) {
        this._httpClient.post(window.router['tpay.blik-payment.register-transaction'], data , callback);
    }

    sendBlikTransactionAgain(data, callback) {
        this._httpClient.post(window.router['tpay.blik-payment.register-transaction-again'], data , callback);
    }

    checkPaymentState(orderId, callback) {
        this._httpClient.post(window.router['tpay.blik-payment.check-payment-state'], JSON.stringify({ orderId: orderId }) , callback);
    }
}