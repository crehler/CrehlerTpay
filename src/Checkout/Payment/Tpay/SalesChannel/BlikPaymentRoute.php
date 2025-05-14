<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractSetPaymentOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\OrderService;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Checkout\Payment\Exception\InvalidOrderException;
use Shopware\Core\Checkout\Payment\Exception\PaymentProcessException;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Checkout\Payment\Exception\UnknownPaymentMethodException;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractHandlePaymentMethodRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Shopware\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener;
use Shopware\Storefront\Framework\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Tpay\ShopwarePayment\Payment\Builder\BlikPaymentBuilder;
use Tpay\ShopwarePayment\Payment\Exception\InvalidBlikCodeException;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class BlikPaymentRoute extends AbstractBlikPaymentRoute
{
    public function __construct(
        private readonly Router $router,
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly AbstractHandlePaymentMethodRoute $handlePaymentMethodRoute,
        private readonly AbstractOrderRoute $orderRoute,
        private readonly AbstractContextSwitchRoute $contextSwitchRoute,
        private readonly SalesChannelContextServiceInterface $contextService,
        private readonly AbstractSetPaymentOrderRoute $setPaymentOrderRoute
    ) {
    }

    public function getDecorated(): AbstractBlikPaymentRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/tpay/blik-payment/register-transaction',
        name: 'store-api.tpay.blik-payment.register-transaction',
        methods: ['POST']
    )]
    public function registerTransaction(RequestDataBag $dataBag, Request $request, SalesChannelContext $context): BlikPaymentTransactionRouteResponse
    {
        if (!$context->getCustomer()) {
            throw new CustomerNotLoggedInException();
        }

        $orderId = '';
        try {
            $this->addAffiliateTracking($dataBag, $request->getSession());
            $orderId = $this->orderService->createOrder($dataBag, $context);
            $finishUrl = $this->router->generate('frontend.checkout.finish.page', ['orderId' => $orderId]);

            $this->paymentService->handlePaymentByOrder($orderId, $dataBag, $context, $finishUrl);
            $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);

            return new BlikPaymentTransactionRouteResponse(true, $orderId, $finishUrl);
        } catch (InvalidBlikCodeException $exception) {
            return new BlikPaymentTransactionRouteResponse(false, $orderId, null, false, $exception->getMessage());
        } catch (ConstraintViolationException | Error) {
        } catch (AsyncPaymentProcessException | InvalidOrderException | SyncPaymentProcessException | UnknownPaymentMethodException $e) {
            $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
            throw $e;
        }

        $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
        return new BlikPaymentTransactionRouteResponse(false, $orderId, null);
    }

    #[Route(
        path: '/store-api/tpay/blik-payment/register-transaction-again',
        name: 'store-api.tpay.blik-payment.register-transaction-again',
        methods: ['POST']
    )]
    public function registerTransactionAgain(RequestDataBag $dataBag, Request $request, SalesChannelContext $context): BlikPaymentTransactionRouteResponse
    {
        $orderId = $dataBag->getAlnum('orderId');
        $changedPayment = $dataBag->get('changedPayment');

        if ($changedPayment == true) {
            $finishUrl = $this->router->generate('frontend.checkout.finish.page', [
                'orderId' => $orderId,
                'changedPayment' => true,
            ]);

            $errorUrl = $this->router->generate('frontend.account.edit-order.page', ['orderId' => $orderId]);

            /** @var OrderEntity|null $order */
            $order = $this->orderRoute->load($request, $context, new Criteria([$orderId]))->getOrders()->first();

            if ($context->getCurrency()->getId() !== $order->getCurrencyId()) {
                $this->contextSwitchRoute->switchContext(
                    new RequestDataBag([SalesChannelContextService::CURRENCY_ID => $order->getCurrencyId()]),
                    $context
                );

                $context = $this->contextService->get(
                    new SalesChannelContextServiceParameters(
                        $context->getSalesChannelId(),
                        $context->getToken(),
                        $context->getContext()->getLanguageId()
                    )
                );
            }

            $setPaymentRequest = new Request();
            $setPaymentRequest->request->set('orderId', $orderId);
            $setPaymentRequest->request->set('paymentMethodId', $request->get('paymentMethodId'));
            $this->setPaymentOrderRoute->setPayment($setPaymentRequest, $context);
        } else {
            $finishUrl = $this->router->generate('frontend.checkout.finish.page', ['orderId' => $orderId]);

            $errorUrl = $this->router->generate('frontend.checkout.finish.page', [
                'orderId' => $orderId,
                'paymentFailed' => true,
            ]);
        }

        $handlePaymentRequest = new Request();
        $handlePaymentRequest->request->set('orderId', $request->get('orderId'));
        $handlePaymentRequest->request->set('finishUrl', $finishUrl);
        $handlePaymentRequest->request->set('errorUrl', $errorUrl);
        $handlePaymentRequest->request->set('blikCode', $request->get('blikCode'));

        try {
            $routeResponse = $this->handlePaymentMethodRoute->load($handlePaymentRequest, $context);
            $response = $routeResponse->getRedirectResponse();
            $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
        } catch (InvalidBlikCodeException $exception) {
            return new BlikPaymentTransactionRouteResponse(false, $orderId, null, false, $exception->getMessage());
        } catch (PaymentProcessException) {
            $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
            return new BlikPaymentTransactionRouteResponse(false, $orderId, null);
        }
        if ($response === null) {
            $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
            return new BlikPaymentTransactionRouteResponse(true, $orderId, $finishUrl);
        }
        $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
        return new BlikPaymentTransactionRouteResponse(false, $orderId, null);
    }

    #[Route(
        path: '/store-api/tpay/blik-payment/check-payment-state',
        name: 'store-api.tpay.blik-payment.check-payment-state',
        methods: ['POST']
    )]
    public function checkPaymentState(Request $request, SalesChannelContext $context): BlikPaymentCheckRouteResponse
    {
        $criteria = new Criteria();
        $criteria->addAssociation('paymentMethod');
        $criteria->addFilter(new EqualsFilter('orderId', $request->get('orderId')));

        /** @var OrderTransactionEntity $transaction */
        $transaction = $this->orderTransactionRepository->search($criteria, $context->getContext())->last();

        $stateName = $transaction->getStateMachineState()?->getTechnicalName();

        if ($stateName === OrderTransactionStates::STATE_PAID) {
            $responseData = new BlikPaymentCheckRouteResponse(true, false);
        } elseif ($stateName === OrderTransactionStates::STATE_OPEN) {
            $responseData = new BlikPaymentCheckRouteResponse(null, true);
        } else {
            $responseData = new BlikPaymentCheckRouteResponse(false, false);
        }
        $request->getSession()->remove(BlikPaymentBuilder::BLIK_TRANSACTION_SESSION_KEY);
        return $responseData;
    }

    private function addAffiliateTracking(RequestDataBag $dataBag, SessionInterface $session): void
    {
        $affiliateCode = $session->get(AffiliateTrackingListener::AFFILIATE_CODE_KEY);
        $campaignCode = $session->get(AffiliateTrackingListener::CAMPAIGN_CODE_KEY);
        if ($affiliateCode !== null && $campaignCode !== null) {
            $dataBag->set(AffiliateTrackingListener::AFFILIATE_CODE_KEY, $affiliateCode);
            $dataBag->set(AffiliateTrackingListener::CAMPAIGN_CODE_KEY, $campaignCode);
        }
    }
}
