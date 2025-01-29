<?php

declare(strict_types=1);

namespace Crehler\TpayShopwarePayment\Service;

use Symfony\Component\HttpFoundation\Request;

interface TpayNotificationValidatorInterface
{
    public function isJwsValid(Request $request): bool;
}
