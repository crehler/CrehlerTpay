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

namespace Tpay\ShopwarePayment\Payment\Builder;


use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Tpay\ShopwarePayment\Config\TpayTransactionConfigStruct;
use Tpay\ShopwarePayment\Util\Payments\Card;

class CardPaymentBuilder extends AbstractPaymentBuilder
{
    protected function getTpayTransactionConfig(SyncPaymentTransactionStruct $transaction, OrderEntity $order, CustomerEntity $customer, SalesChannelContext $salesChannelContext): TpayTransactionConfigStruct
    {
        $tpayTransactionConfig = parent::getTpayTransactionConfig($transaction, $order, $customer, $salesChannelContext);
        $tpayTransactionConfig->setGroup(Card::ID);

       return $tpayTransactionConfig;
    }
}
