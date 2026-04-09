import { apiFetch } from '../utils/api.js';

export function attachAppEvents() {
    // 1. TẠO APP MỚI
    const createBtn = document.getElementById('ska-execute-create-app-btn');
    if ( createBtn ) {
        createBtn.addEventListener('click', async () => {
            const name = document.getElementById('ska-new-app-name').value.trim();
            const icon = document.getElementById('ska-new-app-icon').value;

            if(!name) { alert('Vui lòng điền tên Workspace.'); return; }
            createBtn.disabled = true;
            createBtn.innerText = 'Đang khởi tạo...';

            const res = await apiFetch('ska_data_create_app', { name, icon });

            if (res.success) {
                window.location.reload(); 
            } else { 
                alert(res.data?.message || 'Lỗi'); 
                createBtn.disabled = false; 
                createBtn.innerHTML = '<span class="dashicons dashicons-plus-alt2 mt-0.5" style="font-size:16px;"></span> Tạo Workspace'; 
            }
        });
    }

    // 2. ĐỔI TÊN APP
    const renameBtn = document.getElementById('ska-execute-rename-app-btn');
    if ( renameBtn ) {
        renameBtn.addEventListener('click', async () => {
            const slug = document.getElementById('ska-rename-app-slug').value;
            const name = document.getElementById('ska-rename-app-name').value.trim();
            const icon = document.getElementById('ska-rename-app-icon').value;

            if(!name) { alert('Vui lòng điền tên Workspace.'); return; }
            renameBtn.disabled = true;
            renameBtn.innerText = 'Đang lưu...';

            const res = await apiFetch('ska_data_update_app', { app_id: slug, name, icon });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || 'Lỗi'); 
                renameBtn.disabled = false; 
                renameBtn.innerText = 'Lưu Thông Tin'; 
            }
        });
    }

    // 3. XÓA APP
    const confirmInput = document.getElementById('ska-delete-app-confirm-input');
    const delBtn = document.getElementById('ska-execute-del-app-btn');
    if ( confirmInput && delBtn ) {
        confirmInput.addEventListener('input', function() {
            if (this.value === 'XACNHAN') {
                delBtn.disabled = false;
                delBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                delBtn.disabled = true;
                delBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        delBtn.addEventListener('click', async () => {
            const slug = document.getElementById('ska-del-app-slug').value;

            delBtn.disabled = true;
            delBtn.innerHTML = 'Đang giải tán...';

            const res = await apiFetch('ska_data_drop_app', { app_id: slug });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || 'Lỗi'); 
                delBtn.disabled = false; 
                delBtn.innerHTML = 'Chấp nhận Rủi ro & Giải Tán'; 
            }
        });
    }
}
