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

namespace Tpay\ShopwarePayment\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Tpay\ShopwarePayment\Util\{PaymentMethodUtil,
    Payments\BankTransfer,
    Payments\Blik,
    Payments\Card,
    TpayPaymentsCollection,
    TranslationsUtil
};

class InstallUninstall
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $paymentRepository,
        private readonly EntityRepository $ruleRepository,
        private readonly EntityRepository $currencyRepository,
        private readonly EntityRepository $languageRepository,
        private readonly PluginIdProvider $pluginIdProvider,
        private readonly string $className
    ) {
    }

    public function install(Context $context): void
    {
        $this->addTpayPayments($context);
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

    public function uninstall(UninstallContext $context): void
    {
        if (!$context->keepUserData()) {
            try {
                $this->connection->executeStatement("DROP TABLE IF EXISTS `tpay_payment_tokens`");
            } catch (\Exception) {
            }
        }
    }
}
