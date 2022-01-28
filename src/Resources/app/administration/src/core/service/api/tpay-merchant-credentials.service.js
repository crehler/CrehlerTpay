/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   23 kwi 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const ApiService = Shopware.Classes.ApiService;


export default class TpayMerchantCredentialsService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'tpay') {
        super(httpClient, loginService, apiEndpoint);
    }

    validateMerchantCredentials(merchantId, merchantSecret, merchantTrApiKey, merchantTrApiPass) {
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/validate-merchant-credentials`,
                { merchantId, merchantSecret, merchantTrApiKey, merchantTrApiPass },
                {
                    headers: this.getBasicHeaders()
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

}
