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

namespace Tpay\ShopwarePayment\Config;


use Shopware\Core\Framework\Struct\Struct;

class TpayConfigStruct extends Struct
{
    /** @var int */
    protected $merchantId;

    /** @var string */
    protected $merchantSecret;

    /** @var string */
    protected $merchantTrApiKey;

    /** @var string */
    protected $merchantTrApiPass;

    /** @var int */
    protected $channels;

    /** @var bool */
    protected $redirectDirectlyToTheBank = false;

    /** @var bool */
    protected $verificationSenderIpAddressOfPaymentNotification = true;

    /**
     * @return string
     */
    public function getMerchantId(): int
    {
        return (int) $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getMerchantSecret(): string
    {
        return $this->merchantSecret;
    }

    /**
     * @param string $merchantSecret
     */
    public function setMerchantSecret(string $merchantSecret): void
    {
        $this->merchantSecret = $merchantSecret;
    }

    /**
     * @return string
     */
    public function getMerchantTransactionApiKey(): string
    {
        return $this->merchantTrApiKey;
    }

    /**
     * @param string $merchantTrApiKey
     */
    public function setMerchantTransactionApiKey(string $merchantTrApiKey): void
    {
        $this->merchantTrApiKey = $merchantTrApiKey;
    }

    /**
     * @return string
     */
    public function getMerchantTransactionApiPassword(): string
    {
        return $this->merchantTrApiPass;
    }

    /**
     * @param string $merchantTrApiPass
     */
    public function setMerchantTransactionApiPassword(string $merchantTrApiPass): void
    {
        $this->merchantTrApiPass = $merchantTrApiPass;
    }

    /**
     * @return int
     */
    public function getChannels(): int
    {
        return (int) $this->channels;
    }

    /**
     * @param int $channels
     */
    public function setChannels(int $channels): void
    {
        $this->channels = $channels;
    }

    /**
     * @return bool
     */
    public function isRedirectDirectlyToTheBank(): bool
    {
        return $this->redirectDirectlyToTheBank;
    }

    /**
     * @param bool $redirectDirectlyToTheBank
     */
    public function setRedirectDirectlyToTheBank(bool $redirectDirectlyToTheBank): void
    {
        $this->redirectDirectlyToTheBank = $redirectDirectlyToTheBank;
    }

    /**
     * @return bool
     */
    public function isVerificationSenderIpAddressOfPaymentNotification(): bool
    {
        return $this->verificationSenderIpAddressOfPaymentNotification;
    }

    /**
     * @param bool $verificationSenderIpAddressOfPaymentNotification
     */
    public function setVerificationSenderIpAddressOfPaymentNotification(bool $verificationSenderIpAddressOfPaymentNotification): void
    {
        $this->verificationSenderIpAddressOfPaymentNotification = $verificationSenderIpAddressOfPaymentNotification;
    }

}
