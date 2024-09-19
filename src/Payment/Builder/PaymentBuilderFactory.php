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
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Util\Locale\LocaleProvider;

class PaymentBuilderFactory
{
    public function __construct(
        private readonly ConfigServiceInterface $configService,
        private readonly LocaleProvider $localeProvider,
        private readonly TokenFactoryInterfaceV2 $tokenFactory,
        private readonly RouterInterface $router,
        private readonly AbstractTranslator $translator,
        private readonly LoggerInterface $logger,
        #[Autowire('@tpay_payment_tokens.repository')]
        private readonly EntityRepository $tpayPaymentTokenRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public function createCardBuilder(): PaymentBuilderInterface
    {
        return new CardPaymentBuilder(
            $this->configService,
            $this->localeProvider,
            $this->tokenFactory,
            $this->router,
            $this->translator,
            $this->logger,
            $this->tpayPaymentTokenRepository
        );
    }

    public function createBankTransferBuilder(): PaymentBuilderInterface
    {
        return new BankTransferPaymentBuilder(
            $this->configService,
            $this->localeProvider,
            $this->tokenFactory,
            $this->router,
            $this->translator,
            $this->logger,
            $this->tpayPaymentTokenRepository
        );
    }

    public function createBlikBuilder(): BlikPaymentBuilderInterface
    {
        $session = $this->requestStack?->getCurrentRequest()?->hasSession() ?
            $this->requestStack->getSession() : null;

        return new BlikPaymentBuilder(
            $this->configService,
            $this->localeProvider,
            $this->tokenFactory,
            $this->router,
            $this->translator,
            $this->logger,
            $this->tpayPaymentTokenRepository,
            $session
        );
    }
}
