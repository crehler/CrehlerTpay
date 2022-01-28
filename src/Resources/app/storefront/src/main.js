import TpayPaymentBankSelectionPlugin from './plugin/tpay-payment/tpay-payment-bank-selection.plugin';
import TpayBlikMaskPlugin from './plugin/tpay-payment/tpay-blik-mask.plugin';
import TpayBlikPlugin from './plugin/tpay-payment/tpay-blik.plugin';
import TpayPaymentCheckPlugin from "./plugin/tpay-payment/tpay-payment-check.plugin";

const PluginManager = window.PluginManager;

PluginManager.register('TpayPaymentBankSelection', TpayPaymentBankSelectionPlugin, '[data-tpay-bank-selection]');
PluginManager.register('TpayBlikMask', TpayBlikMaskPlugin, '.blik--input');
PluginManager.register('TpayBlik', TpayBlikPlugin, '[data-tpay-blik');
PluginManager.register('TpayPaymentCheck', TpayPaymentCheckPlugin, '[data-tpay-payment-check]');
