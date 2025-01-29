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

namespace Crehler\TpayShopwarePayment\Config\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Crehler\TpayShopwarePayment\Config\Exception\TpayConfigInvalidException;
use Crehler\TpayShopwarePayment\Config\TpayConfigStruct;
use Crehler\TpayShopwarePayment\Config\TpayConfigStructValidator;

class ConfigService implements ConfigServiceInterface
{
    final public const SYSTEM_CONFIG_DOMAIN = 'CrehlerTpayShopwarePayment.config';

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
