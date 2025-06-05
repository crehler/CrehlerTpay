<?php

namespace Tpay\ShopwarePayment\Util\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Language\LanguageEntity;
use Tpay\ShopwarePayment\Payment\BankTransferPaymentHandler;

final class Update
{
    private EntityRepository $paymentMethodRepository;
    private EntityRepository $languageRepository;

    public function __construct(
        EntityRepository $paymentMethodRepository,
        EntityRepository $languageRepository
    )
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->languageRepository = $languageRepository;
    }

    public function update(Context $context): void
    {
        $this->updateBankTransferTranslations($context);
    }

    private function updateBankTransferTranslations(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('handlerIdentifier', BankTransferPaymentHandler::class));
        $paymentMethodId = $this->paymentMethodRepository->searchIds($criteria, $context)->firstId();

        if (!$paymentMethodId) {
            return;
        }

        $translations = [
            'de-DE' => [
                'name' => 'Schnelle Online-Überweisungen',
                'description' => 'Zahlung per Online-Überweisung verschiedener Banken',
            ],
            'en-GB' => [
                'name' => 'Online bank transfers',
                'description' => 'Payment via online bank transfer from multiple banks',
            ],
            'pl-PL' => [
                'name' => 'Szybkie przelewy online',
                'description' => 'Płatność przelewem online z wielu banków',
            ],
        ];

        $availableTranslationCodes = $this->getAvailableTranslationCodes($context);
        $filteredTranslations = [];

        foreach ($translations as $code => $translation) {
            if (in_array($code, $availableTranslationCodes, true)) {
                $filteredTranslations[$code] = $translation;
            }
        }

        $data = [
            'id' => $paymentMethodId,
            'translations' => $filteredTranslations
        ];

        $this->paymentMethodRepository->update([$data], $context);
    }

    private function getAvailableTranslationCodes(Context $context): array
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
}
