<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Payment\SalesChannel;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel\AbstractTpayCheckPaymentRoute;
use Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel\TpayCheckPaymentRouteResponse;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class TpayCheckPaymentController extends AbstractController
{
    public function __construct(
        private readonly AbstractTpayCheckPaymentRoute $checkPaymentRoute
    ) {
    }

    #[Route(
        path: '/tpay-payment-check',
        name: 'tpay-payment-check',
        defaults: ["XmlHttpRequest" => true],
        methods: ['POST']
    )]
    public function checkPaymentState(Request $request, SalesChannelContext $context): TpayCheckPaymentRouteResponse
    {
        return $this->checkPaymentRoute->checkPaymentState($request, $context);
    }
}
