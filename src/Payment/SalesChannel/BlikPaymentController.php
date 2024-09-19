<?php

declare(strict_types=1);

/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   07 maj 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tpay\ShopwarePayment\Payment\SalesChannel;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel\AbstractBlikPaymentRoute;
use Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel\BlikPaymentCheckRouteResponse;
use Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel\BlikPaymentTransactionRouteResponse;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class BlikPaymentController extends AbstractController
{
    public function __construct(
        private readonly AbstractBlikPaymentRoute $blikPaymentRoute,
    ) {
    }

    #[Route(
        path: '/tpay/blik-payment/register-transaction',
        name: 'tpay.blik-payment.register-transaction',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST']
    )]
    public function registerTransaction(
        RequestDataBag $dataBag,
        Request $request,
        SalesChannelContext $context
    ): BlikPaymentTransactionRouteResponse {
        return $this->blikPaymentRoute->registerTransaction($dataBag, $request, $context);
    }

    #[Route(
        path: '/tpay/blik-payment/register-transaction-again',
        name: 'tpay.blik-payment.register-transaction-again',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST']
    )]
    public function registerTransactionAgain(
        RequestDataBag $dataBag,
        Request $request,
        SalesChannelContext $context
    ): BlikPaymentTransactionRouteResponse {
        return $this->blikPaymentRoute->registerTransactionAgain($dataBag, $request, $context);
    }

    #[Route(
        path: '/tpay/blik-payment/check-payment-state',
        name: 'tpay.blik-payment.check-payment-state',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST']
    )]
    public function checkPaymentState(Request $request, SalesChannelContext $context): BlikPaymentCheckRouteResponse
    {
        return $this->blikPaymentRoute->checkPaymentState($request, $context);
    }
}
