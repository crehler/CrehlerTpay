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

namespace Tpay\ShopwarePayment\Payment;


use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tpay\ShopwarePayment\Payment\Builder\PaymentBuilderInterface;
use tpayLibs\src\_class_tpay\Utilities\TException;
use tpayLibs\src\_class_tpay\Utilities\Util;

class CardPaymentHandler implements AsynchronousPaymentHandlerInterface
{
    use TpayResponseHandlerTrait;

    /** @var PaymentBuilderInterface */
    private $cardPaymentBuilder;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PaymentBuilderInterface $cardPaymentBuilder, LoggerInterface $logger)
    {
        $this->cardPaymentBuilder = $cardPaymentBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
	public function pay(AsyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): RedirectResponse
	{
        Util::$loggingEnabled = false;

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            throw new AsyncPaymentProcessException(
                $transaction->getOrderTransaction()->getId(),
                (new CustomerNotLoggedInException())->getMessage()
            );
        }

        try {
            $tpayResponse = $this->cardPaymentBuilder->createTransaction($transaction, $salesChannelContext, $customer);

            return $this->handleTpayResponse($tpayResponse, $transaction);
        } catch (TException $exception) {
            $this->logger->error('Tpay connection error' . PHP_EOL . $exception->getMessage());
        }

		throw new AsyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'Tpay transaction error');
	}

	/**
	 * @inheritDoc
	 */
	public function finalize(AsyncPaymentTransactionStruct $transaction, Request $request, SalesChannelContext $salesChannelContext): void
	{
        /**
         * @See Tpay\ShopwarePayment\Payment\FinalizePaymentController
         * Nothing to do here.
         */
	}
}
