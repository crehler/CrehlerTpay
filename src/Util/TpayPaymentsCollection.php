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

namespace Tpay\ShopwarePayment\Util;


use Shopware\Core\Framework\Struct\Collection;
use Tpay\ShopwarePayment\Util\Payments\Payment;

class TpayPaymentsCollection extends Collection
{
    public function setRuleIdToCollection(string $id): void
    {
        /** @var Payment $payment */
        foreach ($this->elements as $payment) {
            $payment->setAvailabilityRuleId($id);
        }
    }

    public function setPluginIdToCollection(string $id): void
    {
        /** @var Payment $payment */
        foreach ($this->elements as $payment) {
            $payment->setPluginId($id);
        }
    }

    public function getAllHandlerIdentifiers(): array
    {
        $result = [];

        /** @var Payment $payment */
        foreach ($this->elements as $payment) {
            $result[] = $payment->getHandlerIdentifier();
        }
        return $result;
    }

    public function jsonSerialize(bool $deep = false): array
    {
        $elements = parent::jsonSerialize();
        if (!$deep) {
            return $elements;
        }
        $result = [];

        /** @var Payment $element */
        foreach ($elements as $element) {
            $result[] = $element->jsonSerialize();
        }

        return $result;
    }
}
