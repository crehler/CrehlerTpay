<?php

namespace Tpay\ShopwarePayment\Payment\SalesChannel\Page;

use Shopware\Storefront\Page\Page;

class TpayCheckPaymentPage extends Page
{
    protected string $transactionId;
    protected string $orderId;


    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
