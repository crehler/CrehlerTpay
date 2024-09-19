<?php

declare(strict_types=1);

namespace Tpay\ShopwarePayment\Service;

use JsonException;
use Symfony\Component\HttpFoundation\Request;

use function base64_decode;
use function explode;
use function file_get_contents;
use function json_decode;
use function openssl_pkey_get_public;
use function openssl_x509_verify;
use function openssl_verify;
use function str_replace;
use function str_starts_with;
use function strtr;

use const JSON_THROW_ON_ERROR;
use const OPENSSL_ALGO_SHA256;

class TpayNotificationValidator implements TpayNotificationValidatorInterface
{
    private const TPAY_PREFIX_URL = 'https://secure.tpay.com';

    private const TPAY_CA_CERTIFICATE_URL = 'https://secure.tpay.com/x509/tpay-jws-root.pem';

    public function isJwsValid(Request $request): bool
    {
        $jws = $request->server->get('HTTP_X_JWS_SIGNATURE');

        if (null === $jws) {
            return false;
        }

        $jwsData = explode('.', (string) $jws);
        $headers = $jwsData[0] ?? null;
        $signature = $jwsData[2] ?? null;

        if (null === $headers || null === $signature) {
            return false;
        }

        $headersJson = base64_decode(strtr($headers, '-_', '+/'));

        try {
            $headersData = json_decode($headersJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        $x5u = $headersData['x5u'] ?? null;

        if (null === $x5u || !str_starts_with((string) $x5u, self::TPAY_PREFIX_URL)) {
            return false;
        }

        $certificate = file_get_contents($x5u);
        $trusted = file_get_contents(self::TPAY_CA_CERTIFICATE_URL);

        if (1 !== openssl_x509_verify($certificate, $trusted)) {
            return false;
        }

        $body = $request->getContent();
        $payload = str_replace('=', '', strtr(base64_encode($body), '+/', '-_'));
        $decodedSignature = base64_decode(strtr($signature, '-_', '+/'));
        $publicKey = openssl_pkey_get_public($certificate);

        return 1 === openssl_verify($headers . '.' . $payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);
    }
}
