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

import template from './sw-plugin-config.html.twig';


Shopware.Component.override('sw-plugin-config', {
    template,

    inject: ['TpayMerchantCredentialsService'],

    methods: {
        tpayTestMerchantCredentials() {

        }
    }

});
