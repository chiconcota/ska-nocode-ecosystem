import { __ } from '@wordpress/i18n';
import { apiFetch } from '../utils/api.js';

export function attachAppEvents() {
    // 1. TẠO APP MỚI
    const createBtn = document.getElementById('ska-execute-create-app-btn');
    if ( createBtn ) {
        createBtn.addEventListener('click', async () => {
            const name = document.getElementById('ska-new-app-name').value.trim();
            const icon = document.getElementById('ska-new-app-icon').value;

            if(!name) { alert(__( 'Please enter Workspace name.', 'ska-data-pro' )); return; }
            createBtn.disabled = true;
            createBtn.innerText = __( 'Initializing...', 'ska-data-pro' );

            const res = await apiFetch('ska_data_create_app', { name, icon });

            if (res.success) {
                window.location.reload(); 
            } else { 
                alert(res.data?.message || __( 'Error', 'ska-data-pro' )); 
                createBtn.disabled = false; 
                createBtn.innerHTML = __( '<span class=\"dashicons dashicons-plus-alt2 mt-0.5\" style=\"font-size:16px;\"></span> Create Workspace', 'ska-data-pro' ); 
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
            const redirect = document.getElementById('ska-rename-app-redirect').value.trim();

            if(!name) { alert(__( 'Please enter Workspace name.', 'ska-data-pro' )); return; }
            renameBtn.disabled = true;
            renameBtn.innerText = __( 'Saving...', 'ska-data-pro' );

            const res = await apiFetch('ska_data_update_app', { app_id: slug, name, icon, unauthorized_redirect_url: redirect });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'ska-data-pro' )); 
                renameBtn.disabled = false; 
                renameBtn.innerText = __( 'Save Information', 'ska-data-pro' ); 
            }
        });
    }

    // 3. XÓA APP
    const confirmInput = document.getElementById('ska-delete-app-confirm-input');
    const delBtn = document.getElementById('ska-execute-del-app-btn');
    if ( confirmInput && delBtn ) {
        confirmInput.addEventListener('input', function() {
            if (this.value === 'CONFIRM') {
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
            delBtn.innerHTML = __( 'Disbanding...', 'ska-data-pro' );

            const res = await apiFetch('ska_data_drop_app', { app_id: slug });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'ska-data-pro' )); 
                delBtn.disabled = false; 
                delBtn.innerHTML = __( 'Accept Risk & Dissolve', 'ska-data-pro' ); 
            }
        });
    }

    // 4. IMPORT APP
    const importFileInput = document.getElementById('ska-import-app-file');
    const importFileName = document.getElementById('ska-import-file-name');
    const importBtn = document.getElementById('ska-execute-import-app-btn');
    const importLoading = document.getElementById('ska-import-loading-state');
    
    if ( importFileInput && importBtn ) {
        importFileInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                if (file.name.endsWith('.json')) {
                    importFileName.innerText = file.name;
                    importFileName.classList.add('text-emerald-600');
                    importBtn.disabled = false;
                    importBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    importFileName.innerText = __( 'Invalid file (Must be .json)', 'ska-data-pro' );
                    importFileName.classList.remove('text-emerald-600');
                    importFileName.classList.add('text-red-500');
                    importBtn.disabled = true;
                    importBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                importFileName.innerText = __( 'Drag and Drop or Select File (.json)', 'ska-data-pro' );
                importFileName.classList.remove('text-emerald-600', 'text-red-500');
                importBtn.disabled = true;
                importBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        importBtn.addEventListener('click', async () => {
            if (!importFileInput.files || importFileInput.files.length === 0) return;
            
            const file = importFileInput.files[0];
            const config = window.skaDataConfig || {};
            
            const formData = new FormData();
            formData.append('action', 'ska_data_import_app');
            formData.append('security', config.nonce);
            formData.append('blueprint_file', file);
            
            importBtn.disabled = true;
            importBtn.classList.add('hidden'); // Ẩn nút đi cho đỡ rối
            importLoading.classList.remove('hidden');
            importLoading.classList.add('flex');
            importFileInput.disabled = true;

            try {
                const response = await fetch(config.ajaxurl, {
                    method: 'POST',
                    body: formData
                });
                const resJson = await response.json();
                
                if ( resJson.success ) {
                    window.location.reload();
                } else {
                    alert(resJson.data?.message || __( 'Structural casting error.', 'ska-data-pro' ));
                    importBtn.disabled = false;
                    importBtn.classList.remove('hidden');
                    importLoading.classList.add('hidden');
                    importLoading.classList.remove('flex');
                    importFileInput.disabled = false;
                }
            } catch (e) {
                console.error('API Error:', e);
                alert(__( 'Network error or server not responding.', 'ska-data-pro' ));
                importBtn.disabled = false;
                importBtn.classList.remove('hidden');
                importLoading.classList.add('hidden');
                importLoading.classList.remove('flex');
                importFileInput.disabled = false;
            }
        });
    }
}
