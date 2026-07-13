import { __ } from '@wordpress/i18n';
import { apiFetch } from '../utils/api.js';

export function attachSchemaEvents() {
    // 1. TẠO CỘT MỚI
    const addColBtn = document.getElementById('skaaa-submit-col-btn');
    if ( addColBtn ) {
        addColBtn.addEventListener('click', async () => {
            const labelInput = document.getElementById('skaaa-col-label');
            const typeInput  = document.getElementById('skaaa-col-type');
            const optsInput  = document.getElementById('skaaa-col-options');
            
            const labelValue = labelInput.value.trim();
            if(!labelValue) {
                alert(__( 'Column names cannot be empty.', 'skaaa-data-pro' ));
                labelInput.focus();
                return;
            }

            addColBtn.disabled = true;
            addColBtn.innerHTML = __( '<span class=\"dashicons dashicons-update-alt\" style=\"animation: spin 1s infinite linear;\"></span> Connecting...', 'skaaa-data-pro' );

            let optsValue = '';
            if (typeInput.value === 'relation') {
                optsValue = document.getElementById('skaaa-col-options-relation').value;
            } else if (typeInput.value === 'rollup') {
                const relVal = document.getElementById('skaaa-col-options-rollup-rel').value;
                const tgtVal = document.getElementById('skaaa-col-options-rollup-target').value;
                optsValue = relVal + ',' + tgtVal;
                if (!relVal || !tgtVal) {
                    alert(__( 'You need to select the full Reference Column and Lookup Column for the Rollup function!', 'skaaa-data-pro' ));
                    addColBtn.disabled = false;
                    addColBtn.innerHTML = __( 'Create Data Field', 'skaaa-data-pro' );
                    return;
                }
            } else {
                if(optsInput) optsValue = optsInput.value;
            }

            const res = await apiFetch('skaaa_data_add_column', {
                label: labelValue,
                type: typeInput.value,
                options: optsValue
            });

            if (res.success) {
                window.location.reload(); 
            } else {
                alert(res.data?.message || __( 'An error occurred', 'skaaa-data-pro' ));
                addColBtn.disabled = false;
                addColBtn.innerHTML = __( 'Create Data Field', 'skaaa-data-pro' );
            }
        });
    }

    // 2. CẬP NHẬT CỘT
    const updateColBtn = document.getElementById('skaaa-update-col-btn');
    if ( updateColBtn ) {
        updateColBtn.addEventListener('click', async () => {
            const slug  = document.getElementById('skaaa-edit-col-slug').value;
            const label = document.getElementById('skaaa-edit-col-label').value.trim();
            const type  = document.getElementById('skaaa-edit-col-type').value;
            let opts = '';
            
            if (type === 'relation') {
                opts = document.getElementById('skaaa-edit-col-options-relation').value;
            } else if (type === 'rollup') {
                const relVal = document.getElementById('skaaa-edit-col-options-rollup-rel').value;
                const tgtVal = document.getElementById('skaaa-edit-col-options-rollup-target').value;
                opts = relVal + ',' + tgtVal;
                
                if (!relVal || !tgtVal) {
                    alert(__( 'You need to select the full Reference Column and Lookup Column for the Rollup function!', 'skaaa-data-pro' ));
                    return;
                }
            } else {
                opts = document.getElementById('skaaa-edit-col-options').value;
            }

            if(!label) { alert(__( 'Name cannot be empty.', 'skaaa-data-pro' )); return; }

            updateColBtn.disabled = true;
            updateColBtn.innerText = __( 'Recasting furnace...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_update_column', {
                col: slug,
                label: label,
                type: type,
                options: opts
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'An error occurred', 'skaaa-data-pro' )); 
                updateColBtn.disabled = false; 
                updateColBtn.innerText = __( 'Save Attributes', 'skaaa-data-pro' ); 
            }
        });
    }

    // 3. XÓA CỘT
    const exDelColBtn = document.getElementById('skaaa-execute-del-col-btn');
    if ( exDelColBtn ) {
        exDelColBtn.addEventListener('click', async () => {
            const slug = document.getElementById('skaaa-del-col-slug').value;

            exDelColBtn.disabled = true;
            exDelColBtn.innerText = __( 'Judgment in progress...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_drop_column', {
                col: slug
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                exDelColBtn.disabled = false; 
                exDelColBtn.innerText = __( 'Beheaded (Deleted Forever)', 'skaaa-data-pro' ); 
            }
        });
    }

    // 4. TẠO BẢNG
    const exCreateTblBtn = document.getElementById('skaaa-execute-create-table-btn');
    if ( exCreateTblBtn ) {
        exCreateTblBtn.addEventListener('click', async () => {
            const name   = document.getElementById('skaaa-new-table-name').value.trim();
            const icon   = document.getElementById('skaaa-new-table-icon').value;
            const app_id = document.getElementById('skaaa-new-table-group').value;

            if(!name) { alert(__( 'Please fill in the table name (eg: Customer).', 'skaaa-data-pro' )); return; }
            exCreateTblBtn.disabled = true;
            exCreateTblBtn.innerText = __( 'Initializing...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_create_table', {
                name: name,
                icon: icon,
                app_id: app_id
            });

            if (res.success) {
                const baseUrl = window.location.href.split('&table=')[0];
                window.location.href = baseUrl + '&table=' + res.data.table;
            } else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                exCreateTblBtn.disabled = false; 
                exCreateTblBtn.innerText = __( 'Create Table', 'skaaa-data-pro' ); 
            }
        });
    }

    // 5. ĐỔI TÊN BẢNG
    const exRenameTblBtn = document.getElementById('skaaa-execute-rename-table-btn');
    if ( exRenameTblBtn ) {
        exRenameTblBtn.addEventListener('click', async () => {
            const slug   = document.getElementById('skaaa-rename-table-slug').value;
            const name   = document.getElementById('skaaa-rename-table-name').value.trim();
            const icon   = document.getElementById('skaaa-rename-table-icon').value;
            const app_id = document.getElementById('skaaa-rename-table-group').value;

            if(!name) { alert(__( 'Please fill in the table name.', 'skaaa-data-pro' )); return; }
            exRenameTblBtn.disabled = true;
            exRenameTblBtn.innerText = __( 'Updating...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_rename_table', {
                table: slug, // Rename action usually requires table override if it operates on different table, apiFetch auto appends config.tableId, so we pass explicit 'table' var here.
                name: name,
                icon: icon,
                app_id: app_id
            });

            if (res.success) window.location.reload();
            else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                exRenameTblBtn.disabled = false; 
                exRenameTblBtn.innerText = __( 'Save Information', 'skaaa-data-pro' ); 
            }
        });
    }

    // 6. XÓA BẢNG
    const confirmInput = document.getElementById('skaaa-delete-confirm-input');
    const exDelTblBtn = document.getElementById('skaaa-execute-del-table-btn');
    if ( confirmInput && exDelTblBtn ) {
        confirmInput.addEventListener('input', function() {
            if (this.value === 'CONFIRM') {
                exDelTblBtn.disabled = false;
                exDelTblBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                exDelTblBtn.disabled = true;
                exDelTblBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });

        exDelTblBtn.addEventListener('click', async () => {
            const slug = document.getElementById('skaaa-del-tbl-slug').value;

            exDelTblBtn.disabled = true;
            exDelTblBtn.innerHTML = __( 'Destroying...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_drop_table', {
                table: slug
            });

            if (res.success) {
                window.location.href = window.location.href.split('&table=')[0];
            } else { 
                alert(res.data?.message || __( 'Error', 'skaaa-data-pro' )); 
                exDelTblBtn.disabled = false; 
                exDelTblBtn.innerHTML = __( 'Accept Risk & Delete', 'skaaa-data-pro' ); 
            }
        });
    }

    // 7. CẤU HÌNH APP PORTAL
    window.skaaaInitPortalSettings = function() {
        const tableId = window.skaaaDataConfig.tableId;
        const dict = window.skaaaGlobalDict && window.skaaaGlobalDict[tableId] ? window.skaaaGlobalDict[tableId] : null;
        const portalSettings = dict && dict['__table_info'] && dict['__table_info']['portal_settings'] 
                                ? dict['__table_info']['portal_settings'] 
                                : {};

        const chkActive = document.getElementById('skaaa-portal-active');
        const fieldsWrapper = document.getElementById('skaaa-portal-fields-wrapper');
        const slugInput = document.getElementById('skaaa-portal-slug');
        const rolesInput = document.getElementById('skaaa-portal-roles');
        const viewModeInput = document.getElementById('skaaa-portal-view-mode');
        const redirectInput = document.getElementById('skaaa-portal-unauthorized-redirect');

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
                slugInput.value = tableId.replace('skaaa_data_', '').replace(/_/g, '-');
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

    const exPortalSettingsBtn = document.getElementById('skaaa-execute-portal-settings-btn');
    if (exPortalSettingsBtn) {
        exPortalSettingsBtn.addEventListener('click', async () => {
            const tableId = document.getElementById('skaaa-portal-table-slug').value;
            const active = document.getElementById('skaaa-portal-active').checked;
            const slug = document.getElementById('skaaa-portal-slug').value.trim();
            const rolesInput = document.getElementById('skaaa-portal-roles').value.trim();
            const viewMode = document.getElementById('skaaa-portal-view-mode').value;
            const redirectUrl = document.getElementById('skaaa-portal-unauthorized-redirect') ? document.getElementById('skaaa-portal-unauthorized-redirect').value.trim() : '';

            if (active && !slug) {
                alert(__( 'Please fill in the Slug URL for Portal.', 'skaaa-data-pro' ));
                return;
            }

            // Parse roles
            let roles = [];
            if (rolesInput) {
                roles = rolesInput.split(',').map(r => r.trim()).filter(r => r);
            }

            exPortalSettingsBtn.disabled = true;
            const originalText = exPortalSettingsBtn.innerHTML;
            exPortalSettingsBtn.innerHTML = __( 'Saving...', 'skaaa-data-pro' );

            const res = await apiFetch('skaaa_data_update_portal_settings', {
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
                alert(res.data?.message || __( 'An error occurred during saving.', 'skaaa-data-pro' ));
                exPortalSettingsBtn.disabled = false;
                exPortalSettingsBtn.innerHTML = originalText;
            }
        });
    }
}
