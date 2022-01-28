import TpayMerchantCredentialsService from "../core/service/api/tpay-merchant-credentials.service";

const { Application } = Shopware;

Application.addServiceProvider('TpayMerchantCredentialsService', (container) => {
    const initContainer = Application.getContainer('init');

    return new TpayMerchantCredentialsService(initContainer.httpClient, container.loginService);
})
