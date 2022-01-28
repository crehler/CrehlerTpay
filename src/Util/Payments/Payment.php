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

namespace Tpay\ShopwarePayment\Util\Payments;


abstract class Payment implements \JsonSerializable
{
    /** @var string */
    protected $name;

    /** @var int */
    protected $position;

    /** @var string */
    protected $pluginId;

    /** @var array */
    protected $translations;

    /** @var string */
    protected $availabilityRuleId;

    /** @var string */
    protected $handlerIdentifier;

    /** @var string */
    protected $afterOrderEnabled;

    public function getHandlerIdentifier(): string
    {
        return $this->handlerIdentifier;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    public function setAvailabilityRuleId(string $availabilityRuleId): void
    {
        $this->availabilityRuleId = $availabilityRuleId;
    }

    public function setPluginId(string $id): void
    {
        $this->pluginId = $id;
    }

    public function addDynamicallyId(string $id): void
    {
        $this->id = $id;
    }

    public function getAfterOrderEnabled(): string
    {
        return $this->afterOrderEnabled;
    }

    public function setAfterOrderEnabled(string $afterOrderEnabled): void
    {
        $this->afterOrderEnabled = $afterOrderEnabled;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        foreach ($vars as $property => $value) {
            $vars[$property] = $value;
        }

        return array_filter($vars);
    }
}
