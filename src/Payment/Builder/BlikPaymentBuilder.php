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

namespace Tpay\ShopwarePayment\Payment\Builder;


use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Config\TpayTransactionConfigStruct;
use Tpay\ShopwarePayment\Util\Locale\LocaleProvider;
use Tpay\ShopwarePayment\Util\Payments\Blik;
use tpayLibs\src\_class_tpay\Utilities\TException;

class BlikPaymentBuilder extends AbstractPaymentBuilder implements BlikPaymentBuilderInterface
{

    public const BLIK_TRANSACTION_SESSION_KEY = 'blikTransaction';

    /** @var SessionInterface  */
    private $session;

    public function __construct(
        ConfigServiceInterface $configService,
        LocaleProvider $localeProvider,
        TokenFactoryInterfaceV2 $tokenFactory,
        RouterInterface $router,
        Translator $translator,
        SessionInterface $session,
        LoggerInterface $logger,
        EntityRepositoryInterface $tpayPaymentTokenRepository
    ) {
        parent::__construct($configService, $localeProvider, $tokenFactory, $router, $translator, $logger, $tpayPaymentTokenRepository);
        $this->session = $session;
    }

    /**
     * @throws TException
     */
    public function createBlikTransaction(SyncPaymentTransactionStruct $paymentTransactionStruct, SalesChannelContext $salesChannelContext, CustomerEntity $customer, string $blikCode)
    {
        if ($this->session->has(self::BLIK_TRANSACTION_SESSION_KEY)) {
           $tpayTransaction = $this->session->get(self::BLIK_TRANSACTION_SESSION_KEY);
        } else {
            $tpayTransaction = $this->createTransaction($paymentTransactionStruct, $salesChannelContext, $customer);
            $this->session->set(self::BLIK_TRANSACTION_SESSION_KEY, $tpayTransaction);
        }
        $basicApi = $this->createBasicApi($salesChannelContext);

        return $basicApi->blik($tpayTransaction['title'], $blikCode);
    }

    protected function getTpayTransactionConfig(SyncPaymentTransactionStruct $transaction, OrderEntity $order, CustomerEntity $customer, SalesChannelContext $salesChannelContext): TpayTransactionConfigStruct
    {
        $tpayTransactionConfig = parent::getTpayTransactionConfig($transaction, $order, $customer, $salesChannelContext);
        $tpayTransactionConfig->setGroup(Blik::ID);

        return $tpayTransactionConfig;
    }


}
