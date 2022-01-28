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
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Tpay\ShopwarePayment\Config\TpayTransactionConfigStruct;
use Tpay\ShopwarePayment\Payment\Exception\MissingRequiredBankIdException;
use Tpay\ShopwarePayment\TpayShopwarePayment;

class BankTransferPaymentBuilder extends AbstractPaymentBuilder
{
    public function createTransaction(SyncPaymentTransactionStruct $transaction, SalesChannelContext $salesChannelContext, CustomerEntity $customer): array
    {
        $tpayTransaction = parent::createTransaction($transaction, $salesChannelContext, $customer);
        $tpayTransaction['url'] = $this->handleUrl($tpayTransaction['url']);

        return $tpayTransaction;
    }

    protected function getTpayTransactionConfig(
        SyncPaymentTransactionStruct $transaction,
        OrderEntity $order,
        CustomerEntity $customer,
        SalesChannelContext $salesChannelContext
    ): TpayTransactionConfigStruct {
        $tpayTransactionConfig = parent::getTpayTransactionConfig($transaction, $order, $customer, $salesChannelContext);
        $bankId = (int) $customer->getCustomFields()[TpayShopwarePayment::CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK]['id'];

        if (!$bankId) {
            $exceptionMessage = (new MissingRequiredBankIdException())->getMessage();
            $this->logger->error('Missing required bank id.' . PHP_EOL . $exceptionMessage, $transaction->jsonSerialize());
            throw new AsyncPaymentProcessException(
                $transaction->getOrderTransaction()->getId(),
                $exceptionMessage
            );
        }
        $tpayTransactionConfig->setGroup($bankId);

        return $tpayTransactionConfig;
    }

    private function handleUrl(string $url): string
    {
        if ($this->config->isRedirectDirectlyToTheBank()) {
            $url = str_replace('?gtitle=', '?title=', $url);
        }

        return $url;
    }
}
