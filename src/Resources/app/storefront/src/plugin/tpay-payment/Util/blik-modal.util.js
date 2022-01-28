import PseudoModalUtil from 'src/utility/modal-extension/pseudo-modal.util';


export default class BlikModalUtil extends PseudoModalUtil {

    _open(cb) {
        this.getModal();
        // register on modal hidden event to remove the ajax modal pseudoModal
        this._$modal.on('hidden.bs.modal', this._modalWrapper.remove);
        this._$modal.on('shown.bs.modal', cb);
        this._$modal.modal({ backdrop: 'static', keyboard: false });
        this._$modal.modal('show');
    }

}
