<?php

declare(strict_types=1);

/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   05 maj 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crehler\TpayShopwarePayment\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Crehler\TpayShopwarePayment\Component\TpayPayment\BankList\TpayBankListInterface;
use Crehler\TpayShopwarePayment\Component\TpayPayment\BankList\TpayBankStruct;
use Crehler\TpayShopwarePayment\CrehlerTpayShopwarePayment;

class SalesChannelContextSwitch implements EventSubscriberInterface
{
    public function __construct(
        private readonly TpayBankListInterface $bankListService,
        #[Autowire('@customer.repository')]
        private readonly EntityRepository $customerRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextSwitchEvent::class => 'onSalesChannelContextSwitch'
        ];
    }

    public function onSalesChannelContextSwitch(SalesChannelContextSwitchEvent $event): void
    {
        $context = $event->getContext();
        $customer = $event->getSalesChannelContext()->getCustomer();
        $selectedBankId = $event->getRequestDataBag()->getInt('tpayBank');

        if (empty($selectedBankId)) {
            $selectedBankId = (int) $this->requestStack->getCurrentRequest()->get('tpayBank');
        }
        if ($customer === null || empty($selectedBankId)) {
            return;
        }

        $bank = new TpayBankStruct(
            $this->bankListService->getBankList($event->getSalesChannelContext())
                ->offsetGet($selectedBankId)
        );


        $this->customerRepository->update([
            [
                'id' => $customer->getId(),
                'customFields' => [CrehlerTpayShopwarePayment::CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK => $bank->jsonSerialize()]
            ]
        ], $context);
    }
}
