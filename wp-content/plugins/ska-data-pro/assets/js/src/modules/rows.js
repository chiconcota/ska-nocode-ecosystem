import { apiFetch } from '../utils/api.js';

export function attachRowEvents() {
    // 1. XÓA DÒNG
    document.querySelectorAll('.ska-delete-row-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            if ( ! confirm('Bạn có chắc chắn muốn xóa vĩnh viễn dòng này?') ) return;

            const row = this.closest('tr');
            const rowId = row.getAttribute('data-id');

            row.style.opacity = '0.5';

            const res = await apiFetch('ska_data_delete_row', {
                id: rowId
            });

            if (res.success) {
                row.remove();
            } else {
                alert(res.data?.message || 'Có lỗi xảy ra');
                row.style.opacity = '1';
            }
        });
    });

    // 2. THÊM DÒNG MỚI (Hỗ trợ nhiều nút qua class ska-add-row-trigger)
    const addBtns = document.querySelectorAll('.ska-add-row-trigger');
    addBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (btn.tagName === 'BUTTON') {
                btn.disabled = true;
                btn.innerHTML = '<span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang xử lý...';
            } else {
                btn.style.pointerEvents = 'none';
                btn.innerHTML = '<span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang tạo...';
            }

            const res = await apiFetch('ska_data_add_row');

            if (res.success) { 
                window.location.reload(); 
            } else {
                alert(res.data?.message || 'Lỗi mạng');
                window.location.reload(); 
            }
        });
    });
}
