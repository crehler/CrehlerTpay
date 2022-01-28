<?php declare(strict_types=1);

namespace Tpay\ShopwarePayment\Payment\SalesChannel;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class TpayCheckPaymentController extends AbstractController
{
    /** @var EntityRepositoryInterface */
    private $orderTransactionRepository;

    public function __construct(EntityRepositoryInterface $orderTransactionRepository) {
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    /**
     * @Route("/store-api/tpay-payment-check", name="store-api.tpay-payment-check", methods={"POST"})
     */
    public function checkPaymentState(Request $request, SalesChannelContext $context ): JsonResponse
    {
        $transactionId =  $request->get('transactionId');

        if ($transactionId === null) {
            return $this->json([
                'waiting' => false,
                'success' => false,
            ]);
        }

        $isOrderPaid = $this->isOrderPaid($transactionId, $context->getContext());

        switch ($isOrderPaid) {
            case true:
                $responseData = [
                    'waiting' => false,
                    'success' => true,
                ];
                break;
            case false:
                $responseData = [
                    'waiting' => true,
                    'success' => false
                ];
                break;
            default:
                $responseData = [
                    'waiting' => false,
                    'success' => false,
                ];
                break;
        }

        return $this->json($responseData);
    }

    private function isOrderPaid(string $transactionId, Context $context): ?bool
    {
        $criteria = new Criteria([$transactionId]);

        /** @var OrderTransactionEntity $transaction */
        $transaction = $this->orderTransactionRepository->search($criteria, $context)->first();

        $stateName = $transaction->getStateMachineState()->getTechnicalName();

        if ($stateName === OrderTransactionStates::STATE_PAID) {
            return true;

        } else if ($stateName === OrderTransactionStates::STATE_OPEN) {
            return false;
        } else {
            return null;
        }
    }
}
