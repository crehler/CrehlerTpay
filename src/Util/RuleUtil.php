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

namespace Tpay\ShopwarePayment\Util;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Rule\Container\AndRule;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\Rule\CurrencyRule;

class RuleUtil
{
    private const RULE_NAME = 'Tpay only PLN';

    public function __construct(
        private readonly EntityRepository $ruleRepository,
        private readonly EntityRepository $currencyRepository,
        private readonly Context $context
    ) {
    }

    public function getRuleId(): string
    {
        $ruleId = $this->checkRuleExist();
        if ($ruleId === null) {
            return $this->createRule();
        }

        return $ruleId;
    }

    private function checkRuleExist(): ?string
    {
        $ruleCriteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', self::RULE_NAME));
        $ruleIds = $this->ruleRepository->searchIds($ruleCriteria, $this->context);

        if ($ruleIds->getTotal() === 0) {
            return null;
        }
        return $ruleIds->firstId();
    }

    private function createRule(): ?string
    {
        $ruleId = Uuid::randomHex();
        $currencyId = $this->getCurrencyID();
        $data = [
            'id' => $ruleId,
            'name' => self::RULE_NAME,
            'priority' => 1,
            'description' => 'The currency required is PLN',
            'conditions' => [
                [
                    'type' => (new AndRule())->getName(),
                    'children' => [
                        [
                            'type' => (new CurrencyRule())->getName(),
                            'value' => [
                                'currencyIds' => [$currencyId],
                                'operator' => CurrencyRule::OPERATOR_EQ,
                            ],
                        ]
                    ],
                ],
            ],
        ];
        $this->ruleRepository->create([$data], $this->context);

        return $ruleId;
    }

    private function getCurrencyID(): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('currency.isoCode', 'PLN'));

        $currency = $this->currencyRepository->search($criteria, $this->context);

        if ($currency->getTotal() >= 1) {
            return $currency->first()->getId();
        }

        $id = Uuid::randomHex();

        $currencyData = [
            'id' => $id,
            'factor' => 1,
            'symbol' => 'zł',
            'isoCode' => 'PLN',
            'shortName' => 'PLN',
            'name' => 'Złoty',
            'itemRounding' => [
                'decimals' => 2,
                'interval' => 0.01,
                'roundForNet' => true
            ],
            'totalRounding' => [
                'decimals' => 2,
                'interval' => 0.01,
                'roundForNet' => true
            ]
        ];


        $this->currencyRepository->upsert([$currencyData], $this->context);
        return $id;
    }
}
