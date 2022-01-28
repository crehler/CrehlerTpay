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


use Shopware\Core\Framework\Struct\Struct;

class TpayBankStruct extends Struct
{
    /** @var int  */
    protected $id;

    /** @var string  */
    protected $name;

    /** @var string  */
    protected $banks;

    /** @var string  */
    protected $image;

    /** @var string  */
    protected $mainBankId;

    public function __construct(array $bank)
    {
        $this->id = (int) $bank['id'];
        $this->name = $bank['name'];
        $this->banks = $bank['banks'];
        $this->image = $bank['img'];
        $this->mainBankId = $bank['main_bank_id'];
    }
}
