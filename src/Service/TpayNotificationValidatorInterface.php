<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Service;

use Symfony\Component\HttpFoundation\Request;

interface TpayNotificationValidatorInterface
{
    public function isJwsValid(Request $request): bool;
}
