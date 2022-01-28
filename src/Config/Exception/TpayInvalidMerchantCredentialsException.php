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

namespace Tpay\ShopwarePayment\Config\Exception;


use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class TpayInvalidMerchantCredentialsException extends ShopwareHttpException
{
    public function __construct($e)
    {
        parent::__construct($e);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    public function getErrorCode(): string
    {
        return 'TPAY_SHOPWARE_PAYMENT__INVALID_MERCHANT_CREDENTIALS';
    }
}
