import Plugin from 'src/plugin-system/plugin.class';

const BLIK_MASK = [
    new RegExp('^[0-9]$'),
    new RegExp('^[0-9][0-9]$'),
    new RegExp('^[0-9][0-9][0-9]$'),
    new RegExp('^[0-9][0-9][0-9]+\\s$'),
    new RegExp('^[0-9][0-9][0-9]+\\s[0-9]$'),
    new RegExp('^[0-9][0-9][0-9]+\\s[0-9][0-9]$'),
    new RegExp('^[0-9][0-9][0-9]+\\s[0-9][0-9][0-9]$')
];

export default class TpayBlikMaskPlugin extends Plugin {

    init() {
        this.validateBlik();
        this._registerEvents()
    }

    _registerEvents() {
        this.el.addEventListener('input', this.validateBlik.bind(this));
    }

    validateBlik() {
        const insertAt = (str, sub, pos) => `${str.slice(0, pos)}${sub}${str.slice(pos)}`;
        const isSpaceInserted = this.el.value[3] === ' ';

        this.el.value = this.el.value.replace(/[^\d\s]/g, '');

        let l = this.el.value.length;

        if (l < 0) {
            this.el.value = '';
        }

        if (l <= 7 && l > 0) {

            if (l >= 4 && !isSpaceInserted) {
                this.el.value = insertAt(this.el.value, ' ', 3);
                this.validateBlik();
                return;
            }

            while (l > 0 && !BLIK_MASK[l - 1].test(this.el.value)) {
                this.el.value = this.el.value.slice(0, -1)
                l = this.el.value.length;
            }

        } else if (l >= 8 && l <= 9) {
            this.el.value = this.el.value.slice(0, -1);
        }

        if (this.el.value.length >= 8) {
            this.el.value = this.el.value.slice(0, 7 - this.el.value.length);
        }
    }
}
