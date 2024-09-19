<?php

declare(strict_types=1);

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

namespace Tpay\ShopwarePayment\Util\Locale;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageCollection;
use tpayLibs\src\Dictionaries\FieldsConfigDictionary;
use tpayLibs\src\Dictionaries\Payments\BasicFieldsDictionary;
use WhiteCube\Lingua\Service;

class LocaleProvider
{
    final public const DEFAULT_LANGUAGE = 'EN';

    public function __construct(private readonly EntityRepository $languageRepository)
    {
    }

    public function getLocaleCodeFromContext(Context $context): string
    {
        $language = $this->guessLanguageFromContext($context);
        if (!in_array($language, BasicFieldsDictionary::REQUEST_FIELDS['language'][FieldsConfigDictionary::OPTIONS])) {
            return self::DEFAULT_LANGUAGE;
        }

        return $language;
    }

    private function guessLanguageFromContext(Context $context): string
    {
        $languageId = $context->getLanguageId();

        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');

        /** @var LanguageCollection $languageCollection */
        $languageCollection = $this->languageRepository->search($criteria, $context)->getEntities();

        $language = $languageCollection->get($languageId);
        if ($language === null) {
            return self::DEFAULT_LANGUAGE;
        }

        $locale = $language->getLocale();
        if (!$locale) {
            return self::DEFAULT_LANGUAGE;
        }
        $tpayLocale = Service::create($locale->getCode());

        if (!$tpayLocale->toISO_639_1()) {
            return self::DEFAULT_LANGUAGE;
        }

        return strtoupper((string)$tpayLocale->toISO_639_1());
    }
}
