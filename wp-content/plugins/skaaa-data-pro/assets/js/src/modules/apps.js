import { __ } from '@wordpress/i18n';
import { apiFetch } from '../utils/api.js';

export function attachAppEvents() {
    // 1. TẠO APP MỚI
    const createBtn = document.getElementById('skaaa-execute-create-app-btn');
    if ( createBtn ) {
        createBtn.addEventListener('click', async () => {
            const name = document.getElementById('skaaa-new-app-name').value.trim();
            const icon = document.getElementById('skaaa-new-app-icon').value;

            if(!name) { alert(__( 'Please enter Workspace name.', 'skaaa-data-pro' )); return; }
            createBtn.disabled = true;
            createBtn.innerText = __( 'Initializing...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_create_app', { name, icon });

            if (res.success) {
                window.location.reload(); 
            } else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                createBtn.disabled = false; 
                createBtn.innerHTML = __( '<span class=\"dashicons dashicons-plus-alt2 mt-0.5\" style=\"font-size:16px;\"></span> Create Workspace', 'skaaa-data-pro' ); 
            }
        });
    }

    // 2. ĐỔI TÊN APP
    const renameBtn = document.getElementById('skaaa-execute-rename-app-btn');
    if ( renameBtn ) {
        renameBtn.addEventListener('click', async () => {
            const slug = document.getElementById('skaaa-rename-app-slug').value;
            const name = document.getElementById('skaaa-rename-app-name').value.trim();
            const icon = document.getElementById('skaaa-rename-app-icon').value;
            const redirect = document.getElementById('skaaa-rename-app-redirect').value.trim();

            if(!name) { alert(__( 'Please enter Workspace name.', 'skaaa-data-pro' )); return; }
            renameBtn.disabled = true;
            renameBtn.innerText = __( 'Saving...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_update_app', { app_id: slug, name, icon, unauthorized_redirect_url: redirect });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                renameBtn.disabled = false; 
                renameBtn.innerText = __( 'Save Information', 'skaaa-data-pro' ); 
            }
        });
    }

    // 3. XÓA APP
    const confirmInput = document.getElementById('skaaa-delete-app-confirm-input');
    const delBtn = document.getElementById('skaaa-execute-del-app-btn');
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
            const slug = document.getElementById('skaaa-del-app-slug').value;

            delBtn.disabled = true;
            delBtn.innerHTML = __( 'Disbanding...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_drop_app', { app_id: slug });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                delBtn.disabled = false; 
                delBtn.innerHTML = __( 'Accept Risk & Dissolve', 'skaaa-data-pro' ); 
            }
        });
    }

    // 4. IMPORT APP
    const importFileInput = document.getElementById('skaaa-import-app-file');
    const importFileName = document.getElementById('skaaa-import-file-name');
    const importBtn = document.getElementById('skaaa-execute-import-app-btn');
    const importLoading = document.getElementById('skaaa-import-loading-state');
    
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
                    importFileName.innerText = __( 'Invalid file (Must be .json)', 'skaaa-data-pro' );
                    importFileName.classList.remove('text-emerald-600');
                    importFileName.classList.add('text-red-500');
                    importBtn.disabled = true;
                    importBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                importFileName.innerText = __( 'Drag and Drop or Select File (.json)', 'skaaa-data-pro' );
                importFileName.classList.remove('text-emerald-600', 'text-red-500');
                importBtn.disabled = true;
                importBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        importBtn.addEventListener('click', async () => {
            if (!importFileInput.files || importFileInput.files.length === 0) return;
            
            const file = importFileInput.files[0];
            const config = window.skaaaDataConfig || {};
            
            const formData = new FormData();
            formData.append('action', 'skaaa_data_import_app');
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
                    alert(resJson.data?.message || __( 'Structural casting error.', 'skaaa-data-pro' ));
                    importBtn.disabled = false;
                    importBtn.classList.remove('hidden');
                    importLoading.classList.add('hidden');
                    importLoading.classList.remove('flex');
                    importFileInput.disabled = false;
                }
            } catch (e) {
                console.error('API Error:', e);
                alert(__( 'Network error or server not responding.', 'skaaa-data-pro' ));
                importBtn.disabled = false;
                importBtn.classList.remove('hidden');
                importLoading.classList.add('hidden');
                importLoading.classList.remove('flex');
                importFileInput.disabled = false;
            }
        });
    }
}
