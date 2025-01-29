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

namespace Crehler\TpayShopwarePayment\Util\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Crehler\TpayShopwarePayment\CrehlerTpayShopwarePayment;
use Crehler\TpayShopwarePayment\Util\PaymentMethodUtil;
use Crehler\TpayShopwarePayment\Util\Payments\BankTransfer;
use Crehler\TpayShopwarePayment\Util\Payments\Blik;
use Crehler\TpayShopwarePayment\Util\TpayPaymentsCollection;

class ActivateDeactivate
{
    public function __construct(
        #[Autowire('@custom_field.repository')]
        private readonly EntityRepository $customFieldRepository,
        #[Autowire('@payment_method.repository')]
        private readonly EntityRepository $paymentMethodRepository,
        private readonly PaymentMethodUtil $paymentMethodUtil
    ) {
    }

    public function activate(Context $context): void
    {
        $this->setPaymentMethodsIsActive(true, $context);
        $this->activateOrderTransactionCustomField($context);
    }

    private function setPaymentMethodsIsActive(bool $active, Context $context): void
    {
        $tpayPaymentMethodsCollection = new TpayPaymentsCollection([
            new Blik(),
            new BankTransfer()
        ]);

        $tpayPaymentMethodsIds = $this->paymentMethodUtil->getTpayPaymentMethodsIds($tpayPaymentMethodsCollection, $context);

        $updateData = [];
        foreach ($tpayPaymentMethodsIds as $id) {
            $updateData[] = [
                'id' => $id,
                'active' => $active,
            ];
        }

        $this->paymentMethodRepository->update($updateData, $context);
    }

    private function activateOrderTransactionCustomField(Context $context): void
    {
        $customFieldIds = $this->getCustomFieldIds($context);

        if ($customFieldIds->getTotal() !== 0) {
            return;
        }

        $this->customFieldRepository->upsert(
            [
                [
                    'name' => CrehlerTpayShopwarePayment::ORDER_TRANSACTION_CUSTOM_FIELDS_TPAY_TRANSACTION_ID,
                    'type' => CustomFieldTypes::TEXT,
                ],
                [
                    'name' => CrehlerTpayShopwarePayment::CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK,
                    'type' => CustomFieldTypes::JSON,
                ]
            ],
            $context
        );
    }

    private function getCustomFieldIds(Context $context): IdSearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter(
            'name',
            [
                CrehlerTpayShopwarePayment::ORDER_TRANSACTION_CUSTOM_FIELDS_TPAY_TRANSACTION_ID,
                CrehlerTpayShopwarePayment::CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK
            ]
        ));

        return $this->customFieldRepository->searchIds($criteria, $context);
    }

    public function deactivate(Context $context): void
    {
        $this->setPaymentMethodsIsActive(false, $context);
        $this->deactivateOrderTransactionCustomField($context);
    }

    private function deactivateOrderTransactionCustomField(Context $context): void
    {
        $customFieldIds = $this->getCustomFieldIds($context);

        if ($customFieldIds->getTotal() === 0) {
            return;
        }

        $ids = array_map(static fn($id) => ['id' => $id], $customFieldIds->getIds());

        $this->customFieldRepository->delete($ids, $context);
    }
}
