import { __ } from '@wordpress/i18n';
import { apiFetch, getConfig } from '../utils/api.js';

export default class BaseCell {
    constructor(cellElement) {
        this.cell = cellElement;
        this.colName = cellElement.getAttribute('data-col');
        this.type = cellElement.getAttribute('data-type');
        this.val = cellElement.getAttribute('data-value');
        this.optionsStr = cellElement.getAttribute('data-options');
        this.rowId = cellElement.closest('tr').getAttribute('data-id');
        this.contentDiv = cellElement.querySelector('.ska-cell-content');
        this.tableId = getConfig().tableId;
    }

    // Sẽ được gọi khi user click vào ô
    edit() {
        // Phải được override ở class con
        console.warn('Edit method not implemented for', this.type);
    }

    // Gọi API update db
    async updateDb(newVal) {
        this.cell.classList.add('bg-blue-50', 'opacity-50');
        
        const res = await apiFetch('ska_data_update_cell', {
            id: this.rowId,
            column: this.colName,
            value: newVal
        });

        this.cell.classList.remove('bg-blue-50', 'opacity-50');
        
        if (res.success) {
            this.cell.setAttribute('data-value', newVal);
            this.val = newVal;
            return true;
        } else {
            alert(res.data?.message || __( 'An error occurred', 'ska-data-pro' ));
            return false;
        }
    }
}
