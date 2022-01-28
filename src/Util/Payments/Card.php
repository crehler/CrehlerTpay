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


use Tpay\ShopwarePayment\Payment\CardPaymentHandler;

class Card extends Payment
{
    public const ID = 103;

    public function __construct()
    {
        $this->name = 'Card';
        $this->position = -92;
        $this->handlerIdentifier = CardPaymentHandler::class;
        $this->translations = [
            'de-DE' => [
                'name' => 'Kartenzahlung',
                'description' => '',
            ],
            'en-GB' => [
                'name' => 'Card payment',
                'description' => '',
            ],
            'pl-PL' => [
                'name' => 'Płatność kartą',
                'description' => '',
            ],
        ];
        $this->afterOrderEnabled = true;
    }
}
