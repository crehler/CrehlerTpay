<?php

declare(strict_types=1);

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

namespace Tpay\ShopwarePayment\Config\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Tpay\ShopwarePayment\Config\Exception\TpayConfigInvalidException;
use Tpay\ShopwarePayment\Config\TpayConfigStruct;
use Tpay\ShopwarePayment\Config\TpayConfigStructValidator;

class ConfigService implements ConfigServiceInterface
{
    final public const SYSTEM_CONFIG_DOMAIN = 'TpayShopwarePayment.config';

    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    /**
     * @throws TpayConfigInvalidException
     */
    public function getConfigs(?string $salesChannelId = null): TpayConfigStruct
    {
        $values = $this->systemConfigService->get(
            self::SYSTEM_CONFIG_DOMAIN,
            $salesChannelId
        );

        $configStruct = new TpayConfigStruct();
        $configStruct->assign($values);
        TpayConfigStructValidator::validate($configStruct);

        return $configStruct;
    }
}
