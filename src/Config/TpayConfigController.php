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


use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tpay\ShopwarePayment\Config\Service\MerchantCredentialsServiceInterface;
use tpayLibs\src\_class_tpay\Utilities\Util;

/**
 * @RouteScope(scopes={"api"})
 */
class TpayConfigController extends AbstractController
{

    /** @var MerchantCredentialsServiceInterface */
    private $merchantCredentialsService;

    public function __construct(MerchantCredentialsServiceInterface $merchantCredentialsService)
    {
        $this->merchantCredentialsService = $merchantCredentialsService;
    }

    /**
     * @Route("/api/_action/tpay/validate-merchant-credentials", name="api.action.tpay.validate.merchant.credentials", methods={"POST"})
     */
    public function validateMerchantCredentials(Request $request): JsonResponse
    {
        Util::$loggingEnabled = false;

        $merchantId = $request->request->getInt('merchantId');
        if(empty($merchantId)) {
            return new JsonResponse(['success' => false, 'code' => 'merchantId']);
        }
        $merchantSecret = $request->request->get('merchantSecret');
        if(empty($merchantSecret)) {
            return new JsonResponse(['success' => false, 'code' => 'merchantSecret']);
        }
        $merchantTrApiKey = $request->request->get('merchantTrApiKey');
        if(empty($merchantTrApiKey)) {
            return new JsonResponse(['success' => false, 'code' => 'merchantTrApiKey']);
        }
        $merchantTrApiPass = $request->request->get('merchantTrApiPass');
        if(empty($merchantTrApiPass)) {
            return new JsonResponse(['success' => false, 'code' => 'merchantTrApiPass']);
        }

        try {
            $credentialsValid = $this->merchantCredentialsService->testMerchantCredentials($merchantId, $merchantSecret, $merchantTrApiKey, $merchantTrApiPass);
        }catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'code' => $e->getMessage()]);
        }

        return new JsonResponse(['success' => true, 'credentialsValid' => $credentialsValid]);
    }
}
