import { apiFetch } from '../utils/api.js';

export function attachSchemaEvents() {
    // 1. TẠO CỘT MỚI
    const addColBtn = document.getElementById('ska-submit-col-btn');
    if ( addColBtn ) {
        addColBtn.addEventListener('click', async () => {
            const labelInput = document.getElementById('ska-col-label');
            const typeInput  = document.getElementById('ska-col-type');
            const optsInput  = document.getElementById('ska-col-options');
            
            const labelValue = labelInput.value.trim();
            if(!labelValue) {
                alert('Tên cột không được để trống.');
                labelInput.focus();
                return;
            }

            addColBtn.disabled = true;
            addColBtn.innerHTML = '<span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang kết nối...';

            let optsValue = '';
            if (typeInput.value === 'relation') {
                optsValue = document.getElementById('ska-col-options-relation').value;
            } else if (typeInput.value === 'rollup') {
                const relVal = document.getElementById('ska-col-options-rollup-rel').value;
                const tgtVal = document.getElementById('ska-col-options-rollup-target').value;
                optsValue = relVal + ',' + tgtVal;
                if (!relVal || !tgtVal) {
                    alert('Bạn cần chọn đầy đủ Cột Tham Chiếu và Cột Tra Cứu cho chức năng Rollup!');
                    addColBtn.disabled = false;
                    addColBtn.innerHTML = 'Tạo Trường Dữ Liệu';
                    return;
                }
            } else {
                if(optsInput) optsValue = optsInput.value;
            }

            const res = await apiFetch('ska_data_add_column', {
                label: labelValue,
                type: typeInput.value,
                options: optsValue
            });

            if (res.success) {
                window.location.reload(); 
            } else {
                alert(res.data?.message || 'Có lỗi xảy ra');
                addColBtn.disabled = false;
                addColBtn.innerHTML = 'Tạo Trường Dữ Liệu';
            }
        });
    }

    // 2. CẬP NHẬT CỘT
    const updateColBtn = document.getElementById('ska-update-col-btn');
    if ( updateColBtn ) {
        updateColBtn.addEventListener('click', async () => {
            const slug  = document.getElementById('ska-edit-col-slug').value;
            const label = document.getElementById('ska-edit-col-label').value.trim();
            const type  = document.getElementById('ska-edit-col-type').value;
            let opts = '';
            
            if (type === 'relation') {
                opts = document.getElementById('ska-edit-col-options-relation').value;
            } else if (type === 'rollup') {
                const relVal = document.getElementById('ska-edit-col-options-rollup-rel').value;
                const tgtVal = document.getElementById('ska-edit-col-options-rollup-target').value;
                opts = relVal + ',' + tgtVal;
                
                if (!relVal || !tgtVal) {
                    alert('Bạn cần chọn đầy đủ Cột Tham Chiếu và Cột Tra Cứu cho chức năng Rollup!');
                    return;
                }
            } else {
                opts = document.getElementById('ska-edit-col-options').value;
            }

            if(!label) { alert('Tên không được trống.'); return; }

            updateColBtn.disabled = true;
            updateColBtn.innerText = 'Đang đúc lại lò...';

            const res = await apiFetch('ska_data_update_column', {
                col: slug,
                label: label,
                type: type,
                options: opts
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || 'Có lỗi xảy ra'); 
                updateColBtn.disabled = false; 
                updateColBtn.innerText = 'Lưu Thuộc Tính'; 
            }
        });
    }

    // 3. XÓA CỘT
    const exDelColBtn = document.getElementById('ska-execute-del-col-btn');
    if ( exDelColBtn ) {
        exDelColBtn.addEventListener('click', async () => {
            const slug = document.getElementById('ska-del-col-slug').value;

            exDelColBtn.disabled = true;
            exDelColBtn.innerText = 'Đang thi hành án...';

            const res = await apiFetch('ska_data_drop_column', {
                col: slug
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || 'Lỗi'); 
                exDelColBtn.disabled = false; 
                exDelColBtn.innerText = 'Trảm (Xóa Mãi Mãi)'; 
            }
        });
    }

    // 4. TẠO BẢNG
    const exCreateTblBtn = document.getElementById('ska-execute-create-table-btn');
    if ( exCreateTblBtn ) {
        exCreateTblBtn.addEventListener('click', async () => {
            const name   = document.getElementById('ska-new-table-name').value.trim();
            const icon   = document.getElementById('ska-new-table-icon').value;
            const app_id = document.getElementById('ska-new-table-group').value;

            if(!name) { alert('Vui lòng điền tên bảng (vd: Khách Hàng).'); return; }
            exCreateTblBtn.disabled = true;
            exCreateTblBtn.innerText = 'Đang khởi tạo...';

            const res = await apiFetch('ska_data_create_table', {
                name: name,
                icon: icon,
                app_id: app_id
            });

            if (res.success) {
                const baseUrl = window.location.href.split('&table=')[0];
                window.location.href = baseUrl + '&table=' + res.data.table;
            } else { 
                alert(res.data?.message || 'Lỗi'); 
                exCreateTblBtn.disabled = false; 
                exCreateTblBtn.innerText = 'Tạo Bảng'; 
            }
        });
    }

    // 5. ĐỔI TÊN BẢNG
    const exRenameTblBtn = document.getElementById('ska-execute-rename-table-btn');
    if ( exRenameTblBtn ) {
        exRenameTblBtn.addEventListener('click', async () => {
            const slug   = document.getElementById('ska-rename-table-slug').value;
            const name   = document.getElementById('ska-rename-table-name').value.trim();
            const icon   = document.getElementById('ska-rename-table-icon').value;
            const app_id = document.getElementById('ska-rename-table-group').value;

            if(!name) { alert('Vui lòng điền tên bảng.'); return; }
            exRenameTblBtn.disabled = true;
            exRenameTblBtn.innerText = 'Đang cập nhật...';

            const res = await apiFetch('ska_data_rename_table', {
                table: slug, // Rename action usually requires table override if it operates on different table, apiFetch auto appends config.tableId, so we pass explicit 'table' var here.
                name: name,
                icon: icon,
                app_id: app_id
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || 'Lỗi'); 
                exRenameTblBtn.disabled = false; 
                exRenameTblBtn.innerText = 'Lưu Thông Tin'; 
            }
        });
    }

    // 6. XÓA BẢNG
    const confirmInput = document.getElementById('ska-delete-confirm-input');
    const exDelTblBtn = document.getElementById('ska-execute-del-table-btn');
    if ( confirmInput && exDelTblBtn ) {
        confirmInput.addEventListener('input', function() {
            if (this.value === 'XACNHAN') {
                exDelTblBtn.disabled = false;
                exDelTblBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                exDelTblBtn.disabled = true;
                exDelTblBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        exDelTblBtn.addEventListener('click', async () => {
            const slug = document.getElementById('ska-del-tbl-slug').value;

            exDelTblBtn.disabled = true;
            exDelTblBtn.innerHTML = 'Đang phi tang...';

            const res = await apiFetch('ska_data_drop_table', {
                table: slug
            });

            if (res.success) {
                window.location.href = window.location.href.split('&table=')[0];
            } else { 
                alert(res.data?.message || 'Lỗi'); 
                exDelTblBtn.disabled = false; 
                exDelTblBtn.innerHTML = 'Chấp nhận Rủi ro & Xóa'; 
            }
        });
    }

    // 7. CẤU HÌNH APP PORTAL
    window.skaInitPortalSettings = function() {
        const tableId = window.skaDataConfig.tableId;
        const dict = window.skaGlobalDict && window.skaGlobalDict[tableId] ? window.skaGlobalDict[tableId] : null;
        const portalSettings = dict && dict['__table_info'] && dict['__table_info']['portal_settings'] 
                                ? dict['__table_info']['portal_settings'] 
                                : {};

        const chkActive = document.getElementById('ska-portal-active');
        const fieldsWrapper = document.getElementById('ska-portal-fields-wrapper');
        const slugInput = document.getElementById('ska-portal-slug');
        const rolesInput = document.getElementById('ska-portal-roles');
        const viewModeInput = document.getElementById('ska-portal-view-mode');
        const redirectInput = document.getElementById('ska-portal-unauthorized-redirect');

        // Populate existing data
        if (portalSettings && portalSettings.active) {
            chkActive.checked = true;
            fieldsWrapper.classList.remove('opacity-50', 'pointer-events-none');
            slugInput.value = portalSettings.slug || '';
            rolesInput.value = (portalSettings.roles || []).join(',');
            viewModeInput.value = portalSettings.view_mode || 'readonly';
            if (redirectInput) redirectInput.value = portalSettings.unauthorized_redirect_url || '';
        } else {
            chkActive.checked = false;
            fieldsWrapper.classList.add('opacity-50', 'pointer-events-none');
            // Auto suggest slug based on table id
            if (!slugInput.value) {
                slugInput.value = tableId.replace('ska_data_', '').replace(/_/g, '-');
            }
        }

        chkActive.addEventListener('change', function() {
            if (this.checked) {
                fieldsWrapper.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                fieldsWrapper.classList.add('opacity-50', 'pointer-events-none');
            }
        });
    };

    const exPortalSettingsBtn = document.getElementById('ska-execute-portal-settings-btn');
    if (exPortalSettingsBtn) {
        exPortalSettingsBtn.addEventListener('click', async () => {
            const tableId = document.getElementById('ska-portal-table-slug').value;
            const active = document.getElementById('ska-portal-active').checked;
            const slug = document.getElementById('ska-portal-slug').value.trim();
            const rolesInput = document.getElementById('ska-portal-roles').value.trim();
            const viewMode = document.getElementById('ska-portal-view-mode').value;
            const redirectUrl = document.getElementById('ska-portal-unauthorized-redirect') ? document.getElementById('ska-portal-unauthorized-redirect').value.trim() : '';

            if (active && !slug) {
                alert('Vui lòng điền URL Slug cho Portal.');
                return;
            }

            // Parse roles
            let roles = [];
            if (rolesInput) {
                roles = rolesInput.split(',').map(r => r.trim()).filter(r => r);
            }

            exPortalSettingsBtn.disabled = true;
            const originalText = exPortalSettingsBtn.innerHTML;
            exPortalSettingsBtn.innerHTML = 'Đang lưu...';

            const res = await apiFetch('ska_data_update_portal_settings', {
                table: tableId,
                active: active,
                slug: slug,
                roles: roles,
                view_mode: viewMode,
                unauthorized_redirect_url: redirectUrl
            });

            if (res.success) {
                window.location.reload();
            } else {
                alert(res.data?.message || 'Có lỗi xảy ra trong quá trình lưu.');
                exPortalSettingsBtn.disabled = false;
                exPortalSettingsBtn.innerHTML = originalText;
            }
        });
    }
}
