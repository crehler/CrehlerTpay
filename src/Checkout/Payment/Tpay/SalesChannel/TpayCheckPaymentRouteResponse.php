<?php

declare(strict_types=1);

namespace Crehler\TpayShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class TpayCheckPaymentRouteResponse extends StoreApiResponse
{
    public function __construct(?bool $success, bool $waiting)
    {
        parent::__construct(new ArrayStruct(['success' => $success, 'waiting' => $waiting]));
    }
}
