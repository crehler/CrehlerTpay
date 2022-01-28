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

namespace Tpay\ShopwarePayment\Util;


use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Tpay\ShopwarePayment\Util\Payments\Payment;


class PaymentMethodUtil
{
    /** @var EntityRepositoryInterface  */
    private $paymentRepository;

    /** @var EntityRepositoryInterface */
    private $ruleRepository;

    /** @var EntityRepositoryInterface */
    private $currencyRepository;

    public function __construct(
        EntityRepositoryInterface $paymentRepository,
        EntityRepositoryInterface $ruleRepository,
        EntityRepositoryInterface $currencyRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->ruleRepository = $ruleRepository;
        $this->currencyRepository = $currencyRepository;
    }

    public function getTpayPaymentMethodsPreparedToUpdate(TpayPaymentsCollection $collection, Context $context): ?TpayPaymentsCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('handlerIdentifier', $collection->getAllHandlerIdentifiers()));

        $tPayPaymentsMethodsAlreadyInstalled = $this->paymentRepository->search($criteria, $context)->getEntities();

        return $this->getTpayPaymentMethodsCollection($collection, $tPayPaymentsMethodsAlreadyInstalled, $context);
    }

    public function getTpayPaymentMethodsIds(TpayPaymentsCollection $collection, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('handlerIdentifier', $collection->getAllHandlerIdentifiers()));

        return $this->paymentRepository->searchIds($criteria, $context)->getIds();
    }

    private function getTpayPaymentMethodsCollection(TpayPaymentsCollection $collection, EntityCollection $tPayPaymentsMethodsAlreadyInstalled, Context $context): TpayPaymentsCollection
    {
        $ruleId = (new RuleUtil($this->ruleRepository, $this->currencyRepository, $context))->getRuleId();

        if ($tPayPaymentsMethodsAlreadyInstalled->count() === 0) {
            $collection->setRuleIdToCollection($ruleId);

            return $collection;
        }

        /** @var PaymentMethodEntity $data */
        foreach ($tPayPaymentsMethodsAlreadyInstalled as $data)
        {
            /** @var Payment $payment */
            foreach ($collection as $payment) {
                if ($data->getHandlerIdentifier() === $payment->getHandlerIdentifier()) {
                    $payment->addDynamicallyId($data->getId());
                }

                if (empty($data->getAvailabilityRuleId())) {
                    $payment->setAvailabilityRuleId($ruleId);
                }
            }
        }
        return $collection;
    }
}
