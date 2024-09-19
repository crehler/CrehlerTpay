import PseudoModalUtil from 'src/utility/modal-extension/pseudo-modal.util';

export default class BlikModalUtil extends PseudoModalUtil {
    _open(cb) {
        this.getModal();
        this._modal.addEventListener('hidden.bs.modal', this._modalWrapper.remove);
        this._modal.addEventListener('shown.bs.modal', cb);
        this._modalInstance._config.backdrop = 'static';
        this._modalInstance._config.keyboard = false;
        this._modalInstance.show();
    }
}