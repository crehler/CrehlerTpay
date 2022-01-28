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

namespace Tpay\ShopwarePayment\Subscriber;


use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tpay\ShopwarePayment\Component\TpayPayment\BankList\TpayBankListInterface;
use Tpay\ShopwarePayment\Util\Payments\BankTransfer;

class PagePaymentSearch implements EventSubscriberInterface
{
    /** @var TpayBankListInterface */
    private $bankListService;

    public function __construct(TpayBankListInterface $bankListService)
    {
        $this->bankListService = $bankListService;
    }

    public static function getSubscribedEvents(): array
    {
      return [
          CheckoutConfirmPageLoadedEvent::class => 'onPaymentSearchResult',
          AccountEditOrderPageLoadedEvent::class => 'onAccountEditPaymentSearchResult'
      ];
    }

    public function onPaymentSearchResult(CheckoutConfirmPageLoadedEvent $event): void
    {
        $payments = $event->getPage()->getPaymentMethods();

        $this->addBankListExtension($payments, $event->getSalesChannelContext());
    }

    public function onAccountEditPaymentSearchResult(AccountEditOrderPageLoadedEvent $event):void
    {
        $payments = $event->getPage()->getPaymentMethods();

        $this->addBankListExtension($payments, $event->getSalesChannelContext());
    }

    public function addBankListExtension(PaymentMethodCollection $payments, SalesChannelContext $salesChannelContext): void
    {
        $bankTransferHandler = (new BankTransfer())->getHandlerIdentifier();

        foreach ($payments as $payment) {
            if ($payment->getHandlerIdentifier() === $bankTransferHandler) {
                $payment->addExtension('tpayBankList', $this->bankListService->getBankList($salesChannelContext));
            }
        }
    }
}
