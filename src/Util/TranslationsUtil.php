<?php

declare(strict_types=1);

/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   2 lip 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Crehler\TpayShopwarePayment\Util;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Crehler\TpayShopwarePayment\Util\Payments\Payment;

class TranslationsUtil
{
    public function __construct(private readonly EntityRepository $languageRepository)
    {
    }

    public function prepareTranslations(TpayPaymentsCollection $collection, Context $context): TpayPaymentsCollection
    {
        $codes = $this->getAvailableTranslations($context);

        /** @var Payment $payment */
        foreach ($collection as $payment) {
            $payment->setTranslations($this->filterTranslations($payment->getTranslations(), $codes));
        }

        return $collection;
    }

    private function getAvailableTranslations(Context $context): array
    {
        $codes = [];

        $criteria = new Criteria();
        $criteria->addAssociation('translationCode');

        $repo = $this->languageRepository->search($criteria, $context);

        /** @var LanguageEntity $entity */
        foreach ($repo->getEntities() as $entity) {
            $codes[] = $entity->getTranslationCode()->getCode();
        }

        return $codes;
    }

    private function filterTranslations(array $translations, array $codes): array
    {
        foreach ($translations as $code => $value) {
            if (!in_array($code, $codes, true)) {
                unset($translations[$code]);
            }
        }

        return $translations;
    }
}
