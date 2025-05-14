<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Checkout\Payment\Tpay\SalesChannel;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class BlikPaymentTransactionRouteResponse extends StoreApiResponse
{
    public function __construct(
        bool $success,
        ?string $orderId,
        ?string $finishUrl,
        ?bool $blikCodeValid = null,
        ?string $message = null
    ) {
        parent::__construct(
            new ArrayStruct(
                [
                    'success' => $success,
                    'orderId' => $orderId,
                    'finishUrl' => $finishUrl,
                    'blikCodeValid' => $blikCodeValid,
                    'message' => $message,
                ]
            )
        );
    }
}
