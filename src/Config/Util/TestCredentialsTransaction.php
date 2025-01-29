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

namespace Crehler\TpayShopwarePayment\Config\Util;

use Crehler\TpayShopwarePayment\Config\TpayTransactionConfigStruct;

class TestCredentialsTransaction
{
    public static function getTestTransactionData(): TpayTransactionConfigStruct
    {
        return (new TpayTransactionConfigStruct())
            ->setAmount(100)
            ->setGroup(150)
            ->setEmail('test@example.com')
            ->setName('TEST MERCHANT CREDENTIALS BY SHOPWARE')
            ->setDescription('TEST MERCHANT CREDENTIALS BY SHOPWARE');
    }
}
