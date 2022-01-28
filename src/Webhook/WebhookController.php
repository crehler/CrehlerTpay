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

namespace Tpay\ShopwarePayment\Webhook;


use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Component\TpayPayment\TpayBasicNotificationHandler;
use Tpay\ShopwarePayment\Payment\TpayPaymentService;

/**
 * @RouteScope(scopes={"storefront"})
 */
class WebhookController extends StorefrontController
{
    /** @var ConfigServiceInterface */
    private $configService;

    /** @var TpayPaymentService */
    private $paymentService;

    /** @var EntityRepositoryInterface */
    private $tpayPaymentTokenRepository;

    public function __construct(ConfigServiceInterface $configService, TpayPaymentService $paymentService, EntityRepositoryInterface $tpayPaymentTokenRepository
    )
    {
        $this->configService = $configService;
        $this->paymentService = $paymentService;
        $this->tpayPaymentTokenRepository = $tpayPaymentTokenRepository;
    }

    /**
     * @Route(
     *     "/tpay/webhook/notify",
     *     name="action.tpay.webhook.notify",
     *     options={"seo"="false"},
     *     methods={"POST"},
     *     defaults={"csrf_protected"=false}
     *     )
     */
    public function notifyAction(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $tokenId = $request->get('tokenId');

        if (null === $tokenId) {
            throw new \Exception('Token is empty');
        }

        $tokenEntity = $this->getToken($tokenId, $salesChannelContext->getContext());

        if (null === $tokenEntity) {
            throw new \Exception('Token not found');
        }

        $config = $this->configService->getConfigs($salesChannelContext->getSalesChannel()->getId());

        $notification = new TpayBasicNotificationHandler($config->getMerchantId(), $config->getMerchantSecret());
        if ($config->isVerificationSenderIpAddressOfPaymentNotification()) {
            $notification->enableForwardedIPValidation()->enableValidationServerIP();
        } else {
            $notification->enableForwardedIPValidation()->disableValidationServerIP();
        }

        $notificationData = $notification->checkPayment();

        return $this->paymentService->process($notificationData, $tokenEntity->get('token'), $salesChannelContext);
    }

    private function getToken(string $tokenId, Context $context): ?ArrayEntity
    {
        return $this->tpayPaymentTokenRepository->search(new Criteria([$tokenId]), $context)->first();
    }

}

