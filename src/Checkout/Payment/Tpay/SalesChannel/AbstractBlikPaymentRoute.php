<?php

declare(strict_types=1);

namespace Crehler\TpayShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractBlikPaymentRoute
{
    abstract public function getDecorated(): AbstractBlikPaymentRoute;

    abstract public function registerTransaction(RequestDataBag $dataBag, Request $request, SalesChannelContext $context): BlikPaymentTransactionRouteResponse;

    abstract public function registerTransactionAgain(RequestDataBag $dataBag, Request $request, SalesChannelContext $context): BlikPaymentTransactionRouteResponse;

    abstract public function checkPaymentState(Request $request, SalesChannelContext $context): BlikPaymentCheckRouteResponse;
}
