import { __ } from '@wordpress/i18n';
import { apiFetch } from '../utils/api.js';

export function attachRowEvents() {
    // 1. XÓA DÒNG
    document.querySelectorAll('.skaaa-delete-row-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            if ( ! confirm(__( 'Are you sure you want to permanently delete this line?', 'skaaa-data-pro' )) ) return;

            const row = this.closest('tr');
            const rowId = row.getAttribute('data-id');

            row.style.opacity = '0.5';

            const res = await apiFetch('skaaa_data_delete_row', {
                id: rowId
            });

            if (res.success) {
                row.remove();
            } else {
                alert(res.data?.message || __( 'An error occurred', 'skaaa-data-pro' ));
                row.style.opacity = '1';
            }
        });
    });

    // 2. THÊM DÒNG MỚI (Hỗ trợ nhiều nút qua class skaaa-add-row-trigger)
    const addBtns = document.querySelectorAll('.skaaa-add-row-trigger');
    addBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (btn.tagName === 'BUTTON') {
                btn.disabled = true;
                btn.innerHTML = __( '<span class=\"dashicons dashicons-update-alt\" style=\"animation: spin 1s infinite linear;\"></span> Processing...', 'skaaa-data-pro' );
            } else {
                btn.style.pointerEvents = 'none';
                btn.innerHTML = __( '<span class=\"dashicons dashicons-update-alt\" style=\"animation: spin 1s infinite linear;\"></span> Creating...', 'skaaa-data-pro' );
            }

            const res = await apiFetch('skaaa_data_add_row');

            if (res.success) { 
                window.location.reload(); 
            } else {
                alert(res.data?.message || __( 'Network error', 'skaaa-data-pro' ));
                window.location.reload(); 
            }
        });
    });
}
