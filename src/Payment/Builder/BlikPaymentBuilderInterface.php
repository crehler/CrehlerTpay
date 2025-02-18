<?php

declare(strict_types=1);

/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   23 cze 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crehler\TpayShopwarePayment\Payment\Builder;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface BlikPaymentBuilderInterface extends PaymentBuilderInterface
{
    public function createBlikTransaction(SyncPaymentTransactionStruct $paymentTransactionStruct, SalesChannelContext $salesChannelContext, CustomerEntity $customer, string $blikCode);
}
