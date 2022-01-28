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
use Shopware\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Util\Locale\LocaleProvider;

class PaymentBuilderFactory
{
    /** @var ConfigServiceInterface */
    private $configService;

    /** @var EntityRepositoryInterface */
    private $tpayPaymentTokenRepository;

    /** @var LocaleProvider */
    private $localeProvider;

    /** @var TokenFactoryInterfaceV2 */
    private $tokenFactory;

    /** @var RouterInterface */
    private $router;

    /** @var Translator */
    private $translator;

    /** @var Session */
    private $session;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ConfigServiceInterface $configService,
        LocaleProvider $localeProvider,
        TokenFactoryInterfaceV2 $tokenFactory
        , RouterInterface $router,
        Translator $translator,
        Session $session,
        LoggerInterface $logger,
        EntityRepositoryInterface $tpayPaymentTokenRepository
    ) {
        $this->configService = $configService;
        $this->tokenFactory = $tokenFactory;
        $this->localeProvider = $localeProvider;
        $this->router = $router;
        $this->translator = $translator;
        $this->session = $session;
        $this->logger = $logger;
        $this->tpayPaymentTokenRepository = $tpayPaymentTokenRepository;
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
        return new BlikPaymentBuilder(
            $this->configService,
            $this->localeProvider,
            $this->tokenFactory,
            $this->router,
            $this->translator,
            $this->session,
            $this->logger,
            $this->tpayPaymentTokenRepository
        );
    }
}
