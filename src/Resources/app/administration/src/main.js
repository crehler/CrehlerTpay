import './module/sw-plugin/component/tpay-test-merchant-credentials-button/index';
import './module/sw-order/component/sw-order-user-card/index';

import './init/api-service.init';

import deDE from './snippets/de-DE.json';
import enGB from './snippets/en-GB.json';
import plPL from './snippets/pl-PL.json';

// Extend Snippets
Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
Shopware.Locale.extend('pl-PL', plPL);
