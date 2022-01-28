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

namespace Tpay\ShopwarePayment\Util\Lifecycle;


use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Tpay\ShopwarePayment\Util\{PaymentMethodUtil,
    TpayPaymentsCollection,
    Payments\BankTransfer,
    Payments\Blik,
    Payments\Card,
    TranslationsUtil};

class InstallUninstall
{
    /** @var Connection */
    private $connection;

    /** @var EntityRepositoryInterface */
    private $paymentRepository;

    /** @var EntityRepositoryInterface */
    private $ruleRepository;

    /** @var EntityRepositoryInterface */
    private $currencyRepository;

    /** @var EntityRepositoryInterface */
    private $languageRepository;

    /** @var PluginIdProvider */
    private $pluginIdProvider;

    /** @var string */
    private $className;

    public function __construct(
        Connection $connection,
        EntityRepositoryInterface $paymentRepository,
        EntityRepositoryInterface $ruleRepository,
        EntityRepositoryInterface $currencyRepository,
        EntityRepositoryInterface $languageRepository,
        PluginIdProvider $pluginIdProvider,
        string $className
    ) {
        $this->connection = $connection;
        $this->paymentRepository = $paymentRepository;
        $this->ruleRepository = $ruleRepository;
        $this->currencyRepository = $currencyRepository;
        $this->languageRepository = $languageRepository;
        $this->pluginIdProvider = $pluginIdProvider;
        $this->className = $className;
    }

    public function install(Context $context): void
    {
       $this->addTpayPayments($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        if (!$context->keepUserData()) {
            try {
                $this->connection->executeStatement("DROP TABLE IF EXISTS `tpay_payment_tokens`");
            } catch (\Exception $e) {}
        }
    }

    private function addTpayPayments(Context $context): void
    {
        $paymentMethodUtil = new PaymentMethodUtil(
            $this->paymentRepository,
            $this->ruleRepository,
            $this->currencyRepository
        );

        $payments = new TpayPaymentsCollection(
            [
                new Blik(),
                new BankTransfer(),
                new Card()
            ]
        );

        $payments = $paymentMethodUtil->getTpayPaymentMethodsPreparedToUpdate($payments, $context);

        $translationsUtil = new TranslationsUtil($this->languageRepository);
        $payments = $translationsUtil->prepareTranslations($payments, $context);

        $payments->setPluginIdToCollection($this->pluginIdProvider->getPluginIdByBaseClass($this->className, $context));
        $this->paymentRepository->upsert($payments->jsonSerialize(true), $context);
    }
}
