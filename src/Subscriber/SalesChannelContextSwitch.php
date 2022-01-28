<?php declare(strict_types=1);
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

namespace Tpay\ShopwarePayment\Subscriber;


use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Tpay\ShopwarePayment\Component\TpayPayment\BankList\TpayBankListInterface;
use Tpay\ShopwarePayment\Component\TpayPayment\BankList\TpayBankStruct;
use Tpay\ShopwarePayment\TpayShopwarePayment;

class SalesChannelContextSwitch implements EventSubscriberInterface
{
    /** @var TpayBankListInterface */
    private $bankListService;

    /** @var EntityRepositoryInterface */
    private $customerRepository;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(TpayBankListInterface $bankListService, EntityRepositoryInterface $customerRepository, RequestStack $requestStack)
    {
        $this->bankListService = $bankListService;
        $this->customerRepository = $customerRepository;
        $this->requestStack = $requestStack;
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
                'customFields' => [TpayShopwarePayment::CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK => $bank->jsonSerialize()]
            ]
        ], $context);
    }
}
