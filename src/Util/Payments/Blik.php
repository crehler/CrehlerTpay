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

namespace Tpay\ShopwarePayment\Util\Payments;


use Tpay\ShopwarePayment\Payment\BlikPaymentHandler;

class Blik extends Payment
{
    public const ID = 150;

    public function __construct()
    {
        $this->name = 'Blik';
        $this->position = -91;
        $this->handlerIdentifier = BlikPaymentHandler::class;
        $this->translations = [
            'de-DE' => [
                'name' => 'Blik',
                'description' => '',
            ],
            'en-GB' => [
                'name' => 'Blik',
                'description' => '',
            ],
            'pl-PL' => [
                'name' => 'Blik',
                'description' => '',
            ],
        ];
        $this->afterOrderEnabled = true;
    }
}
