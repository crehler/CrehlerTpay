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

namespace Tpay\ShopwarePayment\Payment\Builder;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Checkout\Payment\Cart\Token\TokenStruct;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tpay\ShopwarePayment\Component\TpayPayment\TpayBasicApi;
use Tpay\ShopwarePayment\Config\Exception\TpayConfigInvalidException;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Config\TpayConfigStruct;
use Tpay\ShopwarePayment\Config\TpayTransactionConfigStruct;
use Tpay\ShopwarePayment\Util\Locale\LocaleProvider;
use tpayLibs\src\_class_tpay\Utilities\TException;

abstract class AbstractPaymentBuilder implements PaymentBuilderInterface
{
    /** @var ConfigServiceInterface */
    protected $configService;
    /** @var TpayConfigStruct */
    protected $config;
    /** @var LocaleProvider */
    protected $localeProvider;
    /** @var TokenFactoryInterfaceV2 */
    protected $tokenFactory;
    /** @var RouterInterface */
    protected $router;
    /** @var Translator */
    protected $translator;
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        ConfigServiceInterface $configService,
        LocaleProvider $localeProvider,
        TokenFactoryInterfaceV2 $tokenFactory,
        RouterInterface $router,
        Translator $translator,
        LoggerInterface $logger,
        private readonly EntityRepository $tpayPaymentTokenRepository
    ) {
        $this->configService = $configService;
        $this->localeProvider = $localeProvider;
        $this->tokenFactory = $tokenFactory;
        $this->router = $router;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @throws TException
     */
    public function createTransaction(SyncPaymentTransactionStruct $transaction, SalesChannelContext $salesChannelContext, CustomerEntity $customer): array
    {
        $order = $transaction->getOrder();

        try {
            $this->config = $this->configService->getConfigs($salesChannelContext->getSalesChannel()->getId());
        } catch (TpayConfigInvalidException $exception) {
            $this->logger->error('Tpay configuration is not valid:' . PHP_EOL . $exception->getMessage());
            throw $exception;
        }

        $tpayTransactionConfig = $this->getTpayTransactionConfig($transaction, $order, $customer, $salesChannelContext);

        $basicApi = $this->createBasicApi();

        return $basicApi->create($tpayTransactionConfig->getTransactionConfig());
    }

    protected function getTpayTransactionConfig(SyncPaymentTransactionStruct $transaction, OrderEntity $order, CustomerEntity $customer, SalesChannelContext $salesChannelContext): TpayTransactionConfigStruct
    {
        $tpayTransactionConfig = new TpayTransactionConfigStruct();
        $token = $this->handleToken($transaction);

        $tpayTransactionConfig
            ->setAmount($transaction->getOrderTransaction()->getAmount()->getTotalPrice())
            ->setLanguage($this->localeProvider->getLocaleCodeFromContext($salesChannelContext->getContext()))
            ->setBuyer($customer)
            ->setResultUrl($this->assembleResultUrl($token, $salesChannelContext->getContext()))
            ->setReturnUrl($this->assembleReturnUrl($token, $salesChannelContext->getContext()))
            ->setDescription($this->translator->trans('tpay.config.transaction.description') . ' ' . $order->getOrderNumber())
            ->setCrc($transaction->getOrderTransaction()->getId());

        return $tpayTransactionConfig;
    }

    final public function handleToken(SyncPaymentTransactionStruct $transaction): string
    {
        $tokenStruct = new TokenStruct(
            $transaction->getOrder()->getId(),
            null,
            $transaction->getOrderTransaction()->getPaymentMethodId(),
            $transaction->getOrderTransaction()->getId(),
            null,
            259200, // 3 days
            null
        );

        return $this->tokenFactory->generateToken($tokenStruct);
    }

    private function assembleResultUrl(string $token, Context $context): string
    {
        $id = Uuid::randomHex();
        $this->tpayPaymentTokenRepository->upsert([
            [
                'id' => $id,
                'token' => $token
            ]
        ], $context);

        $parameter = ['tokenId' => $id];

        return $this->router->generate('action.tpay.webhook.notify', $parameter, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function assembleReturnUrl(string $token, Context $context): string
    {
        $id = Uuid::randomHex();
        $this->tpayPaymentTokenRepository->upsert([
            [
                'id' => $id,
                'token' => $token
            ]

        ], $context);
        $parameter = ['tokenId' => $id];

        return $this->router->generate('tpay.payment.return-url', $parameter, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    final public function createBasicApi(?SalesChannelContext $salesChannelContext = null): TpayBasicApi
    {
        $config = $salesChannelContext ? $this->config ?? $this->configService->getConfigs($salesChannelContext->getSalesChannel()->getId()) : $this->config;

        return new TpayBasicApi(
            (int)$config->getMerchantID(),
            $config->getMerchantSecret(),
            $config->getMerchantTransactionApiKey(),
            $config->getMerchantTransactionApiPassword()
        );
    }
}
