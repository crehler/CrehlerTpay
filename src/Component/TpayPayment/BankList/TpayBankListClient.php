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

namespace Tpay\ShopwarePayment\Component\TpayPayment\BankList;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Struct\Collection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Slim\Http\StatusCode;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Tpay\ShopwarePayment\Config\Exception\TpayConfigInvalidException;
use Tpay\ShopwarePayment\Config\Service\ConfigServiceInterface;
use Tpay\ShopwarePayment\Config\TpayConfigStruct;
use Tpay\ShopwarePayment\Util\Payments\Blik;
use Tpay\ShopwarePayment\Util\Payments\Card;

class TpayBankListClient implements TpayBankListInterface
{

    private const BANK_LIST_CACHE_KEY = 'TpayShopwarePayment_Bank_List';

    private const BANK_LIST_CACHE_LIFETIME = 3600;

    private const TPAY_ENDPOINT = 'https://secure.tpay.com/';

    /** @var PhpArrayAdapter */
    private $cache;

    /** @var ConfigServiceInterface */
    private $configService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PhpArrayAdapter $cache, ConfigServiceInterface $configService, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    public function getBankList(SalesChannelContext $salesChannelContext): ArrayStruct
    {
        try {
            $tpayConfig = $this->configService->getConfigs($salesChannelContext->getSalesChannel()->getId());
        } catch (TpayConfigInvalidException $exception) {
            $this->logger->error('Tpay configuration is not valid:' . PHP_EOL . $exception->getMessage());
            throw $exception;
        }

        try {
            $bankList = $this->cache->get(
                self::BANK_LIST_CACHE_KEY . '_' . $tpayConfig->getMerchantId(),
                static function () use ($tpayConfig): array {
                    return self::getBankListRaw($tpayConfig);
                }, self::BANK_LIST_CACHE_LIFETIME
            ) ?? [];
        } catch (InvalidArgumentException $exception) {
            $this->logger->warning('An error occurred while loading Tpau bank list from Shopware cache.' . PHP_EOL . $exception->getMessage());

            $bankList = self::getBankListRaw($tpayConfig) ?? [];
        }

        $bankListStruct = new ArrayStruct($bankList);
        $bankListStruct->offsetUnset(Blik::ID);
        $bankListStruct->offsetUnset(Card::ID);

        return $bankListStruct;
    }

    public static function getBankListRaw(TpayConfigStruct $tpayConfig): ?array
    {
        $client = new Client();

        $url = self::TPAY_ENDPOINT . 'groups-' . $tpayConfig->getMerchantID() . $tpayConfig->getChannels() . '.js?json';
        try {
	        $response = $client->get($url);
	        $responseBody = $response->getBody()->getContents();
        } catch (ClientException $e) {
        	$responseBody = '';
        }


        return json_decode($responseBody, true);
    }
}
