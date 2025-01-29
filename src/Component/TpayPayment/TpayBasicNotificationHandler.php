<?php

declare(strict_types=1);

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

namespace Crehler\TpayShopwarePayment\Component\TpayPayment;

use tpayLibs\src\_class_tpay\Notifications\BasicNotificationHandler;
use tpayLibs\src\_class_tpay\Utilities\TException;
use tpayLibs\src\_class_tpay\Validators\PaymentTypes\PaymentTypeBasic;

class TpayBasicNotificationHandler extends BasicNotificationHandler
{
    public function __construct($merchantId, $merchantSecret)
    {
        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function checkPayment($response = 'TRUE'): array
    {

        $res = $this->getResponse(new PaymentTypeBasic());

        $this->setTransactionID($res['tr_id']);
        $checkMD5 = $this->isMd5Valid(
            $res['md5sum'],
            number_format($res['tr_amount'], 2, '.', ''),
            $res['tr_crc']
        );

        if ($this->validateServerIP === true && $this->isTpayServer() === false) {
            throw new TException('Request is not from secure server');
        }
        if ($checkMD5 === false) {
            throw new TException('MD5 checksum is invalid');
        }

        return $res;
    }

    /**
     * Check md5 sum to validate tpay response.
     * The values of variables that md5 sum includes are available only for
     * merchant and tpay system.
     *
     * @param string $md5sum md5 sum received from tpay
     * @param float $transactionAmount transaction amount
     * @param string $crc transaction crc
     *
     * @return bool
     */
    private function isMd5Valid($md5sum, $transactionAmount, $crc): bool
    {
        if (!is_string($md5sum) || strlen($md5sum) !== 32) {
            return false;
        }

        return ($md5sum === md5($this->merchantId . $this->transactionID .
                $transactionAmount . $crc . $this->merchantSecret));
    }
}
