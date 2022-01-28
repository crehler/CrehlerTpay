<?php declare(strict_types=1);
/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   07 maj 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tpay\ShopwarePayment\Payment;


use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentFinalizeException;
use Shopware\Core\Checkout\Payment\Exception\CustomerCanceledAsyncPaymentException;
use Shopware\Core\Checkout\Payment\Exception\InvalidTransactionException;
use Shopware\Core\Checkout\Payment\Exception\TokenExpiredException;
use Shopware\Core\Checkout\Payment\Exception\UnknownPaymentMethodException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FinalizePaymentController extends AbstractController
{
   /** @var TpayPaymentService  */
    private $paymentService;

    public function __construct(TpayPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/tpay/finalize-transaction", defaults={"auth_required"=false}, name="tpay.finalize.transaction", methods={"GET"})
     *
     * @throws AsyncPaymentFinalizeException
     * @throws CustomerCanceledAsyncPaymentException
     * @throws InvalidTransactionException
     * @throws TokenExpiredException
     * @throws UnknownPaymentMethodException
     */
    public function finalizeTransaction(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $paymentToken = $request->get('_sw_payment_token');

        return $this->paymentService->finalizeTransaction(
            $paymentToken,
            $salesChannelContext
        );
    }
}
