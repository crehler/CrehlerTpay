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

namespace Tpay\ShopwarePayment\Component\TpayPayment;


use tpayLibs\src\_class_tpay\PaymentBlik;

class TpayBasicApi extends PaymentBlik
{

    public function __construct(int $merchantId, string $merchantSecret, string $apiKey, string $apiPass)
    {
        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;
        $this->trApiKey = $apiKey;
        $this->trApiPass = $apiPass;
        parent::__construct();
    }
}
