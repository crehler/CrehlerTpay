<?php declare(strict_types=1);
/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   24 kwi 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Tpay\ShopwarePayment\Config;


use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Struct\Struct;

class TpayTransactionConfigStruct extends Struct
{
    /** @var float */
    protected $amount;

    /** @var string */
    protected $crc;

    /** @var int */
    protected $group;

    /** @var string */
    protected $name;

    /** @var string */
    protected $city;

    /** @var string */
    protected $address;

    /** @var string */
    protected $zip;

    /** @var string */
    protected $return_url;

    /** @var string */
    protected $return_error_url;

    /** @var string */
    protected $result_url;

    /** @var string */
    protected $email;

    /** @var string */
    protected $language;

    /** @var string */
    protected $module = 'Shopware';

    /** @var string */
    protected $description;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $country;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     *
     * @return $this
     */
    public function setAmount($amount): TpayTransactionConfigStruct
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCrc()
    {
        return $this->crc;
    }

    /**
     * @param mixed $crc
     *
     * @return TpayTransactionConfigStruct
     */
    public function setCrc($crc): TpayTransactionConfigStruct
    {
        $this->crc = $crc;
        return $this;
    }

    public function getGroup(): int
    {
        return $this->group;
    }


    public function setGroup(int $group): TpayTransactionConfigStruct
    {
        $this->group = $group;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return TpayTransactionConfigStruct
     */
    public function setName($name): TpayTransactionConfigStruct
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return TpayTransactionConfigStruct
     */
    public function setCity($city): TpayTransactionConfigStruct
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     *
     * @return TpayTransactionConfigStruct
     */
    public function setAddress($address): TpayTransactionConfigStruct
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     *
     * @return TpayTransactionConfigStruct
     */
    public function setZip($zip): TpayTransactionConfigStruct
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * @param mixed $return_url
     *
     * @return TpayTransactionConfigStruct
     */
    public function setReturnUrl($return_url): TpayTransactionConfigStruct
    {
        $this->return_url = $return_url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnErrorUrl()
    {
        return $this->return_error_url;
    }

    /**
     * @param mixed $return_error_url
     *
     * @return TpayTransactionConfigStruct
     */
    public function setReturnErrorUrl($return_error_url): TpayTransactionConfigStruct
    {
        $this->return_error_url = $return_error_url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultUrl()
    {
        return $this->result_url;
    }

    /**
     * @param mixed $result_url
     *
     * @return TpayTransactionConfigStruct
     */
    public function setResultUrl($result_url): TpayTransactionConfigStruct
    {
        $this->result_url = $result_url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return TpayTransactionConfigStruct
     */
    public function setEmail($email): TpayTransactionConfigStruct
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     *
     * @return TpayTransactionConfigStruct
     */
    public function setLanguage($language): TpayTransactionConfigStruct
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     *
     * @return TpayTransactionConfigStruct
     */
    public function setModule($module): TpayTransactionConfigStruct
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return TpayTransactionConfigStruct
     */
    public function setDescription($description): TpayTransactionConfigStruct
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     *
     * @return TpayTransactionConfigStruct
     */
    public function setPhone($phone): TpayTransactionConfigStruct
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *
     * @return TpayTransactionConfigStruct
     */
    public function setCountry($country): TpayTransactionConfigStruct
    {
        $this->country = $country;
        return $this;
    }

    public function getTransactionConfig(): array
    {
        return array_filter($this->getVars());
    }

    public function setBuyer(CustomerEntity $buyer): TpayTransactionConfigStruct
    {
        $billingAddress = $buyer->getActiveBillingAddress();

        $this->name = $buyer->getFirstName() . ' ' . $buyer->getLastName();
        $this->email = $buyer->getEmail();
        $this->address = $billingAddress->getStreet();
        $this->zip = $billingAddress->getZipcode();
        $this->city = $billingAddress->getCity();
        $this->country = $billingAddress->getCountry()->getTranslated()['name'];
        $this->phone = $this->getPhone();

        return $this;
    }
}
