<?php

declare(strict_types=1);

/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   07 maj 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tpay\ShopwarePayment\Payment;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenStruct;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentFinalizeException;
use Shopware\Core\Checkout\Payment\Exception\CustomerCanceledAsyncPaymentException;
use Shopware\Core\Checkout\Payment\Exception\InvalidTransactionException;
use Shopware\Core\Checkout\Payment\Exception\TokenExpiredException;
use Shopware\Core\Checkout\Payment\Exception\UnknownPaymentMethodException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tpay\ShopwarePayment\TpayShopwarePayment;

class TpayPaymentService
{
    public function __construct(
        private readonly TokenFactoryInterfaceV2 $tokenFactory,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger
    ) {
    }


    /**
     * @throws AsyncPaymentFinalizeException
     * @throws CustomerCanceledAsyncPaymentException
     * @throws InvalidTransactionException
     * @throws TokenExpiredException
     * @throws UnknownPaymentMethodException
     */
    public function finalizeTransaction(
        string $paymentToken,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {
        $paymentTokenStruct = $this->parseToken($paymentToken);
        $transactionId = $paymentTokenStruct->getTransactionId();
        $context = $salesChannelContext->getContext();
        $paymentTransactionStruct = $this->getPaymentTransactionStruct($transactionId, $context);

        if ($paymentTokenStruct->getException() !== null) {
            $this->logger->warning('Token has expired.', $paymentTransactionStruct->jsonSerialize());
            return new RedirectResponse($paymentTokenStruct->getErrorUrl());
        }

        return new RedirectResponse($this->router->generate('frontend.checkout.finish.page', ['orderId' => $paymentTransactionStruct->getOrder()->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    /**
     * @throws TokenExpiredException
     */
    private function parseToken(string $token): TokenStruct
    {
        $tokenStruct = $this->tokenFactory->parseToken($token);

        if ($tokenStruct->isExpired()) {
            throw new TokenExpiredException($tokenStruct->getToken());
        }

        return $tokenStruct;
    }

    /**
     * @throws InvalidTransactionException
     */
    private function getPaymentTransactionStruct(string $orderTransactionId, Context $context): AsyncPaymentTransactionStruct
    {
        $criteria = new Criteria([$orderTransactionId]);
        $criteria->addAssociation('order');
        /** @var OrderTransactionEntity|null $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        if ($orderTransaction === null) {
            throw new InvalidTransactionException($orderTransactionId);
        }

        return new AsyncPaymentTransactionStruct($orderTransaction, $orderTransaction->getOrder(), '');
    }

    public function process(array $notification, string $paymentToken, SalesChannelContext $salesChannelContext): Response
    {
        $paymentTokenStruct = $this->parseToken($paymentToken);
        $transactionId = $paymentTokenStruct->getTransactionId();
        $context = $salesChannelContext->getContext();
        $transactionStruct = $this->getPaymentTransactionStruct($transactionId, $context);

        $this->addTpayTransactionId($transactionStruct, $notification['tr_id'], $context);

        return $this->handlePaymentStatus($notification, $transactionStruct, $context);
    }

    private function addTpayTransactionId(
        AsyncPaymentTransactionStruct $transaction,
        string $tpayTransactionId,
        Context $context
    ): void {
        $data = [
            'id' => $transaction->getOrderTransaction()->getId(),
            'customFields' => [
                TpayShopwarePayment::ORDER_TRANSACTION_CUSTOM_FIELDS_TPAY_TRANSACTION_ID => $tpayTransactionId,
            ],
        ];
        $this->orderTransactionRepository->update([$data], $context);
    }

    private function handlePaymentStatus(array $notification, AsyncPaymentTransactionStruct $transaction, Context $context): Response
    {
        $transactionId = $transaction->getOrderTransaction()->getId();
        if ($notification['tr_status'] === 'CHARGEBACK') {
            $this->transactionStateHandler->refund($transactionId, $context);

            return new Response('TRUE');
        }

        $paidAmount = (int)$notification['tr_paid'] * 100;
        $orderAmount = (int)$transaction->getOrder()->getAmountTotal() * 100;

        if ($paidAmount < $orderAmount || $notification['tr_error'] === 'surcharge') {
            $this->transactionStateHandler->payPartially($transactionId, $context);

            return new Response('TRUE');
        }

        if (
            ($notification['tr_error'] === 'none' || $notification['tr_error'] === 'overpay')
            && $notification['tr_status'] === 'TRUE'
        ) {
            $this->transactionStateHandler->paid($transactionId, $context);

            return new Response('TRUE');
        }

        return new Response("FALSE");
    }
}
