import { apiFetch } from '../utils/api.js';

export function attachModalHandlers() {
    window.skaOpenEditCol = (slug, label, type, options) => {
        document.getElementById('ska-edit-col-slug').value = slug;
        document.getElementById('ska-edit-col-label').value = label;
        document.getElementById('ska-edit-col-type').value = type;
        
        const wrapper = document.getElementById('ska-edit-col-options-wrapper');
        const rollupWrapper = document.getElementById('ska-edit-col-options-rollup-wrapper');
        const txtInput = document.getElementById('ska-edit-col-options');
        const selInput = document.getElementById('ska-edit-col-options-relation');
        const labelEl = document.getElementById('ska-edit-col-options-label');
        
        wrapper.classList.add('hidden');
        rollupWrapper.classList.add('hidden');
        txtInput.classList.add('hidden');
        selInput.classList.add('hidden');

        if (type === 'select' || type === 'multi_select') {
            txtInput.value = options || '';
            wrapper.classList.remove('hidden');
            txtInput.classList.remove('hidden');
            labelEl.innerText = 'Danh Sách Lựa Chọn (Option List)';
        } else if (type === 'relation') {
            selInput.value = options || '';
            wrapper.classList.remove('hidden');
            selInput.classList.remove('hidden');
            labelEl.innerText = 'Bảng Đích Khai Thác (Target Table)';
        } else if (type === 'rollup') {
            wrapper.classList.remove('hidden');
            rollupWrapper.classList.remove('hidden');
            
            if (options) {
                const parts = options.split(',');
                const relCol = parts[0] ? parts[0].trim() : '';
                const tgtCol = parts[1] ? parts[1].trim() : '';
                
                const relSelect = document.getElementById('ska-edit-col-options-rollup-rel');
                const targetSelect = document.getElementById('ska-edit-col-options-rollup-target');
                
                targetSelect.setAttribute('data-selected-val', tgtCol);
                relSelect.value = relCol;
                relSelect.dispatchEvent(new Event('change'));
            }
        }
        
        document.getElementById('ska-edit-col-modal').classList.remove('hidden');
    };

    window.skaOpenDeleteCol = (slug, label) => {
        document.getElementById('ska-del-col-slug').value = slug;
        document.getElementById('ska-del-col-name').innerText = label;
        document.getElementById('ska-delete-col-modal').classList.remove('hidden');
    };

    window.skaOpenRenameTable = (slug, name, icon, group) => {
        document.getElementById('ska-rename-table-slug').value = slug;
        document.getElementById('ska-rename-table-name').value = name;
        document.getElementById('ska-rename-table-icon').value = icon;
        document.getElementById('ska-rename-table-group').value = group || 'custom';
        document.getElementById('ska-rename-table-modal').classList.remove('hidden');
    };

    window.skaOpenDeleteTable = (slug, name) => {
        document.getElementById('ska-del-tbl-slug').value = slug;
        document.getElementById('ska-del-tbl-name').innerText = name;
        document.getElementById('ska-delete-confirm-input').value = '';
        const btn = document.getElementById('ska-execute-del-table-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        document.getElementById('ska-delete-table-modal').classList.remove('hidden');
    };

    window.skaOpenRenameApp = (slug, name, icon) => {
        document.getElementById('ska-rename-app-slug').value = slug;
        document.getElementById('ska-rename-app-name').value = name;
        document.getElementById('ska-rename-app-icon').value = icon;
        document.getElementById('ska-rename-app-modal').classList.remove('hidden');
    };

    window.skaOpenDeleteApp = (slug, name) => {
        document.getElementById('ska-del-app-slug').value = slug;
        document.getElementById('ska-del-app-name').innerText = name;
        document.getElementById('ska-delete-app-confirm-input').value = '';
        const btn = document.getElementById('ska-execute-del-app-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        document.getElementById('ska-delete-app-modal').classList.remove('hidden');
    };
    
    // Add event listeners for type changes in modals
    const bindTypeSelect = (idPrefix) => {
        const typeSelect = document.getElementById(`ska-${idPrefix}col-type`);
        if (!typeSelect) return;
        
        typeSelect.addEventListener('change', function() {
            const wrapper = document.getElementById(`ska-${idPrefix}col-options-wrapper`);
            const rollupWrapper = document.getElementById(`ska-${idPrefix}col-options-rollup-wrapper`);
            const txtInput = document.getElementById(`ska-${idPrefix}col-options`);
            const selInput = document.getElementById(`ska-${idPrefix}col-options-relation`);
            const label = document.getElementById(`ska-${idPrefix}col-options-label`);
            const hint = document.getElementById(`ska-${idPrefix}col-options-hint`);
            
            wrapper.classList.add('hidden');
            if(rollupWrapper) rollupWrapper.classList.add('hidden');
            txtInput.classList.add('hidden');
            selInput.classList.add('hidden');

            if (this.value === 'select' || this.value === 'multi_select') {
                wrapper.classList.remove('hidden');
                txtInput.classList.remove('hidden');
                if (hint) hint.classList.remove('hidden');
                if (label) label.innerText = 'Danh Sách Lựa Chọn (Option List)';
            } else if (this.value === 'relation') {
                wrapper.classList.remove('hidden');
                selInput.classList.remove('hidden');
                if (hint) hint.classList.add('hidden');
                if (label) label.innerText = 'Bảng Đích Khai Thác (Target Table)';
            } else if (this.value === 'rollup') {
                wrapper.classList.remove('hidden');
                if(rollupWrapper) rollupWrapper.classList.remove('hidden');
            }
        });
    };
    
    bindTypeSelect('');
    bindTypeSelect('edit-');

    const bindRollupCascading = (relSelectId, targetSelectId) => {
        const relSelect = document.getElementById(relSelectId);
        const targetSelect = document.getElementById(targetSelectId);
        
        if (!relSelect || !targetSelect) return;

        relSelect.addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            const targetTable = selectedOption ? selectedOption.getAttribute('data-target') : null;
            
            targetSelect.innerHTML = '<option value="">-- Chọn Cột Tra Cứu --</option>';
            
            if (!targetTable || !this.value) {
                targetSelect.disabled = true;
                return;
            }

            targetSelect.disabled = true;
            targetSelect.innerHTML = '<option value="">-- Đang tải dữ liệu... --</option>';

            try {
                // Tận dụng apiFetch fetch columns vật lý của TargetTable
                const response = await apiFetch('ska_data_get_table_columns', { target_table: targetTable });
                
                targetSelect.innerHTML = '<option value="">-- Chọn Cột Tra Cứu --</option>';
                if (response.success && response.data && response.data.columns) {
                    response.data.columns.forEach(col => {
                        targetSelect.add(new Option(col.label + ' (' + col.slug + ')', col.slug));
                    });

                    // Khôi phục giá trị được chọn khi mở form Chỉnh Sửa
                    const preSelected = targetSelect.getAttribute('data-selected-val');
                    if (preSelected) {
                        targetSelect.value = preSelected;
                        targetSelect.removeAttribute('data-selected-val');
                    }
                } else {
                    targetSelect.innerHTML = '<option value="">-- Bảng không có cột rỗng --</option>';
                }
                targetSelect.disabled = false;
            } catch (err) {
                targetSelect.innerHTML = '<option value="">-- Lỗi mạng --</option>';
                targetSelect.disabled = false;
            }
        });
    };

    bindRollupCascading('ska-col-options-rollup-rel', 'ska-col-options-rollup-target');
    bindRollupCascading('ska-edit-col-options-rollup-rel', 'ska-edit-col-options-rollup-target');
}
