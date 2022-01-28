<?php declare(strict_types=1);
/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   27 kwi 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tpay\ShopwarePayment\Config\Exception;


use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class TpayConfigInvalidException extends ShopwareHttpException
{

    public function __construct(string $missingConfig)
    {
        parent::__construct(
            'Required config field "{{ missingSetting }}" is missing or invalid',
            ['missingConfig' => $missingConfig]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'TPAY_SHOPWARE_PAYMENT__REQUIRED_CONFIG_FIELD_INVALID';
    }
}
