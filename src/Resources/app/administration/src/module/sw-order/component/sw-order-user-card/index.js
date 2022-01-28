import template from './sw-order-user-card.html.twig';

const { Component } = Shopware;

Component.override('sw-order-user-card', {
    template,

    computed : {
        isTpayPayment() {
            const customFields = this.currentOrder.transactions.last().customFields;

            return customFields !== null && customFields.hasOwnProperty('tpay_shopware_payment_transaction_id') && customFields.tpay_shopware_payment_transaction_id.length > 0;
        }
    }
});
