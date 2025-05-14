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

namespace Tpay\ShopwarePayment\Util\Payments;

use Tpay\ShopwarePayment\Payment\BankTransferPaymentHandler;

class BankTransfer extends Payment
{
    public function __construct()
    {
        $this->name = 'Bank Transfer';
        $this->position = -90;
        $this->handlerIdentifier = BankTransferPaymentHandler::class;
        $this->translations = [
            'de-DE' => [
                'name' => 'Schnelle Online-Überweisung',
                'description' => '',
            ],
            'en-GB' => [
                'name' => 'Online bank transfer',
                'description' => '',
            ],
            'pl-PL' => [
                'name' => 'Szybki przelew online',
                'description' => '',
            ],
        ];
        $this->afterOrderEnabled = true;
    }
}
