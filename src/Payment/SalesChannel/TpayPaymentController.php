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

namespace Tpay\ShopwarePayment\Payment\SalesChannel;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenStruct;
use Shopware\Core\Checkout\Payment\Exception\TokenExpiredException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tpay\ShopwarePayment\Payment\SalesChannel\Page\TpayCheckPaymentPage;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class TpayPaymentController extends StorefrontController
{
    protected EntityRepository $tpayPaymentTokenRepository;
    protected EntityRepository $orderTransactionRepository;

    public function __construct(
        EntityRepository $tpayPaymentTokenRepository,
        EntityRepository $orderTransactionRepository,
        protected GenericPageLoader $genericLoader,
        protected TokenFactoryInterfaceV2 $tokenFactory
    ) {
        $this->tpayPaymentTokenRepository = $tpayPaymentTokenRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    #[Route(path: '/tpay/payment/return-url/{tokenId}', name: 'tpay.payment.return-url', methods: ['GET'])]
    public function createReturnUrl(?string $tokenId, RequestDataBag $dataBag, Request $request, SalesChannelContext $context): RedirectResponse
    {
        if (null === $tokenId) {
            throw new \Exception('Token is empty');
        }

        $tokenEntity = $this->getToken($tokenId, $context->getContext());

        if (null === $tokenEntity) {
            throw new \Exception('Token not found');
        }

        $paymentToken = $this->parseToken($tokenEntity->get('token'));
        $transactionId = $paymentToken->getTransactionId();

        $isOrderPaid = $this->isOrderPaid($transactionId, $context->getContext());

        if ($isOrderPaid === true) {
            $parameter = ['_sw_payment_token' => $tokenEntity->get('token')];

            return $this->redirectToRoute('tpay.finalize.transaction', $parameter);
        } else {
            $parameter = ['transactionId' => $transactionId];

            return $this->redirectToRoute('tpay.payment.check-payment', $parameter);
        }
    }

    private function getToken(string $tokenId, Context $context): ?ArrayEntity
    {
        return $this->tpayPaymentTokenRepository->search(new Criteria([$tokenId]), $context)->first();
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

    private function isOrderPaid(string $transactionId, Context $context): ?bool
    {
        $criteria = new Criteria([$transactionId]);

        /** @var OrderTransactionEntity $transaction */
        $transaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        $stateName = $transaction->getStateMachineState()->getTechnicalName();

        if ($stateName === OrderTransactionStates::STATE_PAID) {
            return true;
        } elseif ($stateName === OrderTransactionStates::STATE_OPEN) {
            return false;
        } else {
            return null;
        }
    }

    #[Route(path: '/tpay/payment/result-url/{tokenId}', name: 'tpay.payment.result-url', methods: ['GET'])]
    public function createResultUrl(?string $tokenId, RequestDataBag $dataBag, Request $request, SalesChannelContext $context): Response
    {
        if (null === $tokenId) {
            throw new \Exception('Token is empty');
        }

        $tokenEntity = $this->getToken($tokenId, $context->getContext());

        if (null === $tokenEntity) {
            throw new \Exception('Token not found');
        }

        $parameter = ['_sw_token' => $tokenEntity->get('token')];

        return $this->redirectToRoute('action.tpay.webhook.notify', $parameter);
    }

    #[Route(path: '/tpay/payment/check-payment', name: 'tpay.payment.check-payment', methods: ['GET'])]
    public function checkPayment(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $transactionId = $request->get('transactionId');

        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = TpayCheckPaymentPage::createFrom($page);

        $page->setTransactionId($transactionId);
        $page->setOrderId($this->getOrderId($transactionId, $salesChannelContext->getContext()));

        return $this->renderStorefront('@TpayShopwarePayment/storefront/page/tpay/check-payment.html.twig', ['page' => $page]);
    }

    private function getOrderId(string $transactionId, Context $context): ?string
    {
        $criteria = new Criteria([$transactionId]);

        /** @var OrderTransactionEntity $transaction */
        $transaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        if ($transaction instanceof OrderTransactionEntity) {
            return $transaction->getOrderId();
        }

        return null;
    }
}
