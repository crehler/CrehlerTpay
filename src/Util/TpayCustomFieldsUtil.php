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

namespace Tpay\ShopwarePayment\Util;


use Shopware\Core\System\CustomField\CustomFieldTypes;

class TpayCustomFieldsUtil
{
    public const CUSTOMER_CUSTOM_FIELDS = [
        [
            'name' => 'tpay_default_payment_selected_bank',
            'type' => CustomFieldTypes::JSON,
        ],
    ];

    public const ORDER_CUSTOM_FIELDS = [
        [
            'name' => 'tpay_order_transaction_id',
            'type' => CustomFieldTypes::INT,
        ],
    ];
}
