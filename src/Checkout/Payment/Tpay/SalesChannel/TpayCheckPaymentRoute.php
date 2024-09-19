<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class TpayCheckPaymentRoute extends AbstractTpayCheckPaymentRoute
{
    public function __construct(private readonly EntityRepository $orderTransactionRepository)
    {
    }

    public function getDecorated(): AbstractTpayCheckPaymentRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/tpay-payment-check',
        name: 'store-api.tpay-payment-check',
        methods: ['POST']
    )]
    public function checkPaymentState(Request $request, SalesChannelContext $context): TpayCheckPaymentRouteResponse
    {
        $transactionId = $request->get('transactionId');

        if ($transactionId === null) {
            return new TpayCheckPaymentRouteResponse(false, false);
        }

        $isOrderPaid = $this->isOrderPaid($transactionId, $context->getContext());

        $responseData = match ($isOrderPaid) {
            true => [
                'waiting' => false,
                'success' => true,
            ],
            false => [
                'waiting' => true,
                'success' => false
            ],
            default => [
                'waiting' => false,
                'success' => false,
            ],
        };

        return new TpayCheckPaymentRouteResponse($responseData['success'], $responseData['waiting']);
    }

    private function isOrderPaid(string $transactionId, Context $context): ?bool
    {
        $criteria = new Criteria([$transactionId]);
        $criteria->addAssociation('stateMachineState');

        /** @var OrderTransactionEntity $transaction */
        $transaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        $stateName = $transaction->getStateMachineState()?->getTechnicalName();

        if ($stateName === OrderTransactionStates::STATE_PAID) {
            return true;
        }

        if ($stateName === OrderTransactionStates::STATE_OPEN) {
            return false;
        }

        return null;
    }
}
