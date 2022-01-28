/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   29 kwi 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from './tpay-test-merchant-credentials-button.html.twig';

const {Component, Mixin} = Shopware;

const TPAY_CONFIG_NAMESPACE = 'TpayShopwarePayment.config.';


Component.register('tpay-test-merchant-credentials-button', {
    template: template,

    inject: ['TpayMerchantCredentialsService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSuccess: false,
            fields: []
        }
    },

    methods: {
        tpayTestMerchantCredentials() {
            this.isLoading = true;
            this.fields = this.$parent.$parent.$parent.$children;

            const merchantId = this.getValue('merchantId');
            const merchantSecret = this.getValue('merchantSecret')
            const merchantTrApiKey = this.getValue('merchantTrApiKey')
            const merchantTrApiPass = this.getValue('merchantTrApiPass')

            this.TpayMerchantCredentialsService.validateMerchantCredentials(merchantId, merchantSecret, merchantTrApiKey, merchantTrApiPass)
                .then((response) => {
                    this.isLoading = false;
                    if (response.success === false) {
                        this.onInvalidData(response.code);
                        return;
                    }
                    if (!response.credentialsValid) {
                        this.onError();
                        return;
                    }
                    this.onSuccess();
                }).catch(() => {
                this.isLoading = false;
                this.onError();
            })

        },

        onSuccess() {
            const that = this;

            this.isSuccess = true;
            this.createNotificationSuccess({
                title: this.$tc('tpay-shopware-payment.config.successTestMerchantCredentialsNotificationTitle'),
                message: this.$tc('tpay-shopware-payment.config.successTestMerchantCredentialsNotificationMessage'),
                autoClose: true
            });
            setTimeout(() => that.isSuccess = false, 2000);
        },

        onError() {
            this.createNotificationError({
                title: this.$tc('tpay-shopware-payment.config.errorTestMerchantCredentialsNotificationTitle'),
                message: this.$tc('tpay-shopware-payment.config.errorTestMerchantCredentialsNotificationMessage'),
                autoClose: true
            });
        },

        onInvalidData(code) {
            let message = '';
            switch (code) {
                case 'merchantId':
                    message = this.$tc('tpay-shopware-payment.config.emptyMerchantId');
                    break;
                case 'merchantSecret':
                    message = this.$tc('tpay-shopware-payment.config.emptyMerchantSecret');
                    break;
                case 'merchantTrApiKey':
                    message = this.$tc('tpay-shopware-payment.config.emptyMerchantTrApiKey');
                    break;
                case 'merchantTrApiPass':
                    message = this.$tc('tpay-shopware-payment.config.emptyMerchantTrApiPass');
                    break;
                default:
                    message = code;
            }
            this.createNotificationError({
                title: this.$tc('tpay-shopware-payment.config.invalidTestMerchantCredentialsNotificationTitle'),
                message: message,
                autoClose: true
            });
        },

        getFieldByName(field, name) {
            return field.$children[0].$attrs.name === TPAY_CONFIG_NAMESPACE + name;
        },

        getValue(name) {
            const field = this.fields.find((field) => {
                return this.getFieldByName(field, name)
            })
            if (typeof field.currentValue === 'undefined' || "" === field.currentValue) {
                return field.$attrs.placeholder;
            }

            return field.currentValue;
        }
    }
});
