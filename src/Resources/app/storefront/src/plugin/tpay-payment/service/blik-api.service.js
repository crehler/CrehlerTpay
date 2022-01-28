import StoreApiClient from 'src/service/store-api-client.service';

export default class BlikApiService {

    constructor() {
        this._httpClient = new StoreApiClient();
    }

    sendBlikTransaction(data, callback) {
        this._httpClient.post(window.router['store-api.tpay.blik-payment.register-transaction'], data , callback);
    }

    sendBlikTransactionAgain(data, callback) {
        this._httpClient.post(window.router['store-api.tpay.blik-payment.register-transaction-again'], data , callback);
    }

    checkPaymentState(orderId, callback) {
        this._httpClient.post(window.router['store-api.tpay.blik-payment.check-payment-state'], JSON.stringify({ orderId: orderId }) , callback);
    }

}
