<?php declare(strict_types=1);
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

namespace Tpay\ShopwarePayment\Config;


use Tpay\ShopwarePayment\Config\Exception\TpayConfigInvalidException;

class TpayConfigStructValidator
{
    /**
     * @throws TpayConfigInvalidException
     */
    public static function validate(TpayConfigStruct $generalStruct): void
    {
        try {
            $generalStruct->getMerchantId();
        } catch (\TypeError $error) {
            throw new TpayConfigInvalidException('MerchantId');
        }

        try {
            $generalStruct->getMerchantSecret();
        } catch (\TypeError $error) {
            throw new TpayConfigInvalidException('MerchantSecret');
        }

        try {
            $generalStruct->getMerchantTransactionApiKey();
        } catch (\TypeError $error) {
            throw new TpayConfigInvalidException('MerchantTransactionApiKey');
        }

        try {
            $generalStruct->getMerchantTransactionApiPassword();
        } catch (\TypeError $error) {
            throw new TpayConfigInvalidException('MerchantTransactionApiPassword');
        }
    }
}
