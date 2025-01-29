<?php

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

declare(strict_types=1);

namespace Crehler\TpayShopwarePayment\Webhook;

use Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Crehler\TpayShopwarePayment\Config\Service\ConfigServiceInterface;
use Crehler\TpayShopwarePayment\Component\TpayPayment\TpayBasicNotificationHandler;
use Crehler\TpayShopwarePayment\Payment\TpayPaymentService;
use Crehler\TpayShopwarePayment\Service\TpayNotificationValidatorInterface;
use tpayLibs\src\_class_tpay\Utilities\Util;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class WebhookController extends StorefrontController
{
    public function __construct(
        private readonly ConfigServiceInterface $configService,
        private readonly TpayPaymentService $paymentService,
        #[Autowire('@tpay_payment_tokens.repository')]
        private readonly EntityRepository $tpayPaymentTokenRepository,
        private readonly TpayNotificationValidatorInterface $tpayNotificationValidator
    ) {
    }

    #[Route(
        path: '/tpay/webhook/notify',
        name: 'action.tpay.webhook.notify',
        options: ['seo' => 'false'],
        defaults: ['csrf_protected' => false],
        methods: ['POST']
    )]
    public function notifyAction(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        if (!$this->tpayNotificationValidator->isJwsValid($request)) {
            return new Response('FALSE');
        }

        $tokenId = $request->get('tokenId');

        if (null === $tokenId) {
            throw new Exception('Token is empty');
        }

        $tokenEntity = $this->getToken($tokenId, $salesChannelContext->getContext());

        if (null === $tokenEntity) {
            throw new Exception('Token not found');
        }

        $config = $this->configService->getConfigs($salesChannelContext->getSalesChannel()->getId());

        $notification = new TpayBasicNotificationHandler($config->getMerchantId(), $config->getMerchantSecret());
        if ($config->isVerificationSenderIpAddressOfPaymentNotification()) {
            $notification->enableForwardedIPValidation()->enableValidationServerIP();
        } else {
            $notification->disableForwardedIPValidation()->disableValidationServerIP();
        }

        $notificationData = $notification->checkPayment();

        return $this->paymentService->process($notificationData, $tokenEntity->get('token'), $salesChannelContext);
    }

    private function getToken(string $tokenId, Context $context): ?ArrayEntity
    {
        return $this->tpayPaymentTokenRepository->search(new Criteria([$tokenId]), $context)->first();
    }
}
