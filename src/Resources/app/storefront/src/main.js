const PluginManager = window.PluginManager;

PluginManager.register(
    'TpayPaymentBankSelection',
    () => import('./plugin/tpay-payment/tpay-payment-bank-selection.plugin'),
    '[data-tpay-bank-selection]'
);

PluginManager.register(
    'TpayBlikMask',
    () => import('./plugin/tpay-payment/tpay-blik-mask.plugin'),
    '.blik--input'
);

PluginManager.register(
    'TpayBlik',
    () => import('./plugin/tpay-payment/tpay-blik.plugin'),
    '[data-tpay-blik]'
);

PluginManager.register(
    'TpayPaymentCheck',
    () => import('./plugin/tpay-payment/tpay-payment-check.plugin'),
    '[data-tpay-payment-check]'
);