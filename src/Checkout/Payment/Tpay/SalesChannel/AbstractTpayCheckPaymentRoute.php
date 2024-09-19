<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractTpayCheckPaymentRoute
{
    abstract public function getDecorated(): AbstractTpayCheckPaymentRoute;

    abstract public function checkPaymentState(Request $request, SalesChannelContext $context): TpayCheckPaymentRouteResponse;
}
