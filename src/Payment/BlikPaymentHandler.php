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

namespace Tpay\ShopwarePayment\Payment;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Tpay\ShopwarePayment\Payment\Builder\BlikPaymentBuilderInterface;
use Tpay\ShopwarePayment\Payment\Exception\InvalidBlikCodeException;
use tpayLibs\src\_class_tpay\Utilities\Util;

class BlikPaymentHandler implements SynchronousPaymentHandlerInterface
{
    use TpayResponseHandlerTrait;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    public function __construct(private readonly BlikPaymentBuilderInterface $blikPaymentBuilder, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        Util::$loggingEnabled = false;

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            throw PaymentException::syncProcessInterrupted(
                $transaction->getOrderTransaction()->getId(),
                CartException::customerNotLoggedIn()->getMessage()
            );
        }

        $responseBlik = $this->blikPaymentBuilder->createBlikTransaction($transaction, $salesChannelContext, $customer, $dataBag->getDigits('blikCode'));

        if (isset($responseBlik['result']) && (int)$responseBlik['result'] !== 1) {
            if ($responseBlik['err'] === 'ERR63') {
                throw new InvalidBlikCodeException();
            }
            $this->tpayResponseError($responseBlik, $transaction);
        }
    }

    /**
     * @inheritDoc
     */
    public function finalize(AsyncPaymentTransactionStruct $transaction, Request $request, SalesChannelContext $salesChannelContext): void
    {
        /**
         * @See Tpay\ShopwarePayment\Payment\FinalizePaymentController
         * Nothing to do here.
         */
    }
}
