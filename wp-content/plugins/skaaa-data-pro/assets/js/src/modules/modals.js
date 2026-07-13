import { __ } from '@wordpress/i18n';
import { apiFetch } from '../utils/api.js';

export function attachModalHandlers() {
    window.skaaaOpenEditCol = (slug, label, type, options) => {
        document.getElementById('skaaa-edit-col-slug').value = slug;
        document.getElementById('skaaa-edit-col-label').value = label;
        document.getElementById('skaaa-edit-col-type').value = type;
        
        const wrapper = document.getElementById('skaaa-edit-col-options-wrapper');
        const rollupWrapper = document.getElementById('skaaa-edit-col-options-rollup-wrapper');
        const txtInput = document.getElementById('skaaa-edit-col-options');
        const selInput = document.getElementById('skaaa-edit-col-options-relation');
        const labelEl = document.getElementById('skaaa-edit-col-options-label');
        
        wrapper.classList.add('hidden');
        rollupWrapper.classList.add('hidden');
        txtInput.classList.add('hidden');
        selInput.classList.add('hidden');

        if (type === 'select' || type === 'multi_select') {
            txtInput.value = options || '';
            wrapper.classList.remove('hidden');
            txtInput.classList.remove('hidden');
            labelEl.innerText = __( 'Option List', 'skaaa-data-pro' );
        } else if (type === 'relation') {
            selInput.value = options || '';
            wrapper.classList.remove('hidden');
            selInput.classList.remove('hidden');
            labelEl.innerText = __( 'Target Table', 'skaaa-data-pro' );
        } else if (type === 'rollup') {
            wrapper.classList.remove('hidden');
            rollupWrapper.classList.remove('hidden');
            
            if (options) {
                const parts = options.split(',');
                const relCol = parts[0] ? parts[0].trim() : '';
                const tgtCol = parts[1] ? parts[1].trim() : '';
                
                const relSelect = document.getElementById('skaaa-edit-col-options-rollup-rel');
                const targetSelect = document.getElementById('skaaa-edit-col-options-rollup-target');
                
                targetSelect.setAttribute('data-selected-val', tgtCol);
                relSelect.value = relCol;
                relSelect.dispatchEvent(new Event('change'));
            }
        }
        
        document.getElementById('skaaa-edit-col-modal').classList.remove('hidden');
    };

    window.skaaaOpenDeleteCol = (slug, label) => {
        document.getElementById('skaaa-del-col-slug').value = slug;
        document.getElementById('skaaa-del-col-name').innerText = label;
        document.getElementById('skaaa-delete-col-modal').classList.remove('hidden');
    };

    window.skaaaOpenRenameTable = (slug, name, icon, group) => {
        document.getElementById('skaaa-rename-table-slug').value = slug;
        document.getElementById('skaaa-rename-table-name').value = name;
        document.getElementById('skaaa-rename-table-icon').value = icon;
        document.getElementById('skaaa-rename-table-group').value = group || 'custom';
        document.getElementById('skaaa-rename-table-modal').classList.remove('hidden');
    };

    window.skaaaOpenDeleteTable = (slug, name) => {
        document.getElementById('skaaa-del-tbl-slug').value = slug;
        document.getElementById('skaaa-del-tbl-name').innerText = name;
        document.getElementById('skaaa-delete-confirm-input').value = '';
        const btn = document.getElementById('skaaa-execute-del-table-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        document.getElementById('skaaa-delete-table-modal').classList.remove('hidden');
    };

    window.skaaaOpenRenameApp = (slug, name, icon, redirectUrl) => {
        document.getElementById('skaaa-rename-app-slug').value = slug;
        document.getElementById('skaaa-rename-app-name').value = name;
        document.getElementById('skaaa-rename-app-icon').value = icon;
        document.getElementById('skaaa-rename-app-redirect').value = redirectUrl || '';
        document.getElementById('skaaa-rename-app-modal').classList.remove('hidden');
    };

    window.skaaaOpenDeleteApp = (slug, name) => {
        document.getElementById('skaaa-del-app-slug').value = slug;
        document.getElementById('skaaa-del-app-name').innerText = name;
        document.getElementById('skaaa-delete-app-confirm-input').value = '';
        const btn = document.getElementById('skaaa-execute-del-app-btn');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        document.getElementById('skaaa-delete-app-modal').classList.remove('hidden');
    };
    
    // Add event listeners for type changes in modals
    const bindTypeSelect = (idPrefix) => {
        const typeSelect = document.getElementById(`skaaa-${idPrefix}col-type`);
        if (!typeSelect) return;
        
        typeSelect.addEventListener('change', function() {
            const wrapper = document.getElementById(`skaaa-${idPrefix}col-options-wrapper`);
            const rollupWrapper = document.getElementById(`skaaa-${idPrefix}col-options-rollup-wrapper`);
            const txtInput = document.getElementById(`skaaa-${idPrefix}col-options`);
            const selInput = document.getElementById(`skaaa-${idPrefix}col-options-relation`);
            const label = document.getElementById(`skaaa-${idPrefix}col-options-label`);
            const hint = document.getElementById(`skaaa-${idPrefix}col-options-hint`);
            
            wrapper.classList.add('hidden');
            if(rollupWrapper) rollupWrapper.classList.add('hidden');
            txtInput.classList.add('hidden');
            selInput.classList.add('hidden');

            if (this.value === 'select' || this.value === 'multi_select') {
                wrapper.classList.remove('hidden');
                txtInput.classList.remove('hidden');
                if (hint) hint.classList.remove('hidden');
                if (label) label.innerText = __( 'Option List', 'skaaa-data-pro' );
            } else if (this.value === 'relation') {
                wrapper.classList.remove('hidden');
                selInput.classList.remove('hidden');
                if (hint) hint.classList.add('hidden');
                if (label) label.innerText = __( 'Target Table', 'skaaa-data-pro' );
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
            
            targetSelect.innerHTML = __( '<option value=\"\">-- Select Lookup Column --</option>', 'skaaa-data-pro' );
            
            if (!targetTable || !this.value) {
                targetSelect.disabled = true;
                return;
            }

            targetSelect.disabled = true;
            targetSelect.innerHTML = __( '<option value=\"\">-- Loading data... --</option>', 'skaaa-data-pro' );

            try {
                // Tận dụng apiFetch fetch columns vật lý của TargetTable
                const response = await apiFetch('skaaa_data_get_table_columns', { target_table: targetTable });
                
                targetSelect.innerHTML = __( '<option value=\"\">-- Select Lookup Column --</option>', 'skaaa-data-pro' );
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
                    targetSelect.innerHTML = __( '<option value=\"\">-- The table does not have empty columns --</option>', 'skaaa-data-pro' );
                }
                targetSelect.disabled = false;
            } catch (err) {
                targetSelect.innerHTML = __( '<option value=\"\">-- Network error --</option>', 'skaaa-data-pro' );
                targetSelect.disabled = false;
            }
        });
    };

    bindRollupCascading('skaaa-col-options-rollup-rel', 'skaaa-col-options-rollup-target');
    bindRollupCascading('skaaa-edit-col-options-rollup-rel', 'skaaa-edit-col-options-rollup-target');

    // Global listener to close Sidebar Kebab dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('[id^="dd-tbl-"], [id^="dd-app-"]');
        dropdowns.forEach(dd => {
            if (!dd.classList.contains('hidden')) {
                const toggleIcon = dd.previousElementSibling;
                if (!dd.contains(e.target) && (!toggleIcon || !toggleIcon.contains(e.target))) {
                    dd.classList.add('hidden');
                }
            }
        });
    });
}
