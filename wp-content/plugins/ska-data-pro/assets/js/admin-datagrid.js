function skaOpenEditCol(slug, label, type, options) {
    document.getElementById('ska-edit-col-slug').value = slug;
    document.getElementById('ska-edit-col-label').value = label;
    document.getElementById('ska-edit-col-type').value = type;
    
    const wrapper = document.getElementById('ska-edit-col-options-wrapper');
    const rollupWrapper = document.getElementById('ska-edit-col-options-rollup-wrapper');
    const txtInput = document.getElementById('ska-edit-col-options');
    const selInput = document.getElementById('ska-edit-col-options-relation');
    const labelEl = document.getElementById('ska-edit-col-options-label');
    
    // Reset all
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
        
        // Tách chuỗi relation_col,target_col để gán mặc định
        if (options) {
            const parts = options.split(',');
            const relCol = parts[0] ? parts[0].trim() : '';
            const tgtCol = parts[1] ? parts[1].trim() : '';
            
            const relSelect = document.getElementById('ska-edit-col-options-rollup-rel');
            const targetSelect = document.getElementById('ska-edit-col-options-rollup-target');
            
            // Đặt biến cờ chờ AJAX Response
            targetSelect.setAttribute('data-selected-val', tgtCol);
            
            relSelect.value = relCol;
            // Kích hoạt sự kiện change thủ công để load Dropdown 2 (AJAX)
            relSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Hiện popup
    document.getElementById('ska-edit-col-modal').classList.remove('hidden');
}

function skaOpenDeleteCol(slug, label) {
    document.getElementById('ska-del-col-slug').value = slug;
    document.getElementById('ska-del-col-name').innerText = label;
    document.getElementById('ska-delete-col-modal').classList.remove('hidden');
}

function skaOpenRenameTable(slug, name, icon, group) {
    document.getElementById('ska-rename-table-slug').value = slug;
    document.getElementById('ska-rename-table-name').value = name;
    document.getElementById('ska-rename-table-icon').value = icon;
    document.getElementById('ska-rename-table-group').value = group || 'custom';
    document.getElementById('ska-rename-table-modal').classList.remove('hidden');
}

function skaOpenDeleteTable(slug, name) {
    document.getElementById('ska-del-tbl-slug').value = slug;
    document.getElementById('ska-del-tbl-name').innerText = name;
    document.getElementById('ska-delete-confirm-input').value = '';
    const btn = document.getElementById('ska-execute-del-table-btn');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    document.getElementById('ska-delete-table-modal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    // Nhận biến Môi trường được bơm từ Bọc WebPHP
    const tableId = window.skaDataConfig.tableId;
    const ajaxurl = window.skaDataConfig.ajaxurl;
    const nonce   = window.skaDataConfig.nonce;

    // 1. CHỨC NĂNG: XÓA DÒNG
    document.querySelectorAll('.ska-delete-row-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if ( ! confirm('Bạn có chắc chắn muốn xóa vĩnh viễn dòng này?') ) return;

            const row = this.closest('tr');
            const rowId = row.getAttribute('data-id');

            const formData = new URLSearchParams();
            formData.append('action', 'ska_data_delete_row');
            formData.append('security', nonce);
            formData.append('table', tableId);
            formData.append('id', rowId);

            row.style.opacity = '0.5';

            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        row.remove();
                    } else {
                        alert(res.data.message);
                        row.style.opacity = '1';
                    }
                })
                .catch(() => { alert('Lỗi mạng'); row.style.opacity = '1'; });
        });
    });

    // XỬ LÝ ẨN HIỆN OPTIONS KHI CHỌN SELECT HOẶC RELATION
    const colTypeSelect = document.getElementById('ska-col-type');
    if (colTypeSelect) {
        colTypeSelect.addEventListener('change', function() {
            const wrapper = document.getElementById('ska-col-options-wrapper');
            const rollupWrapper = document.getElementById('ska-col-options-rollup-wrapper');
            const txtInput = document.getElementById('ska-col-options');
            const selInput = document.getElementById('ska-col-options-relation');
            const hint = document.getElementById('ska-col-options-hint');
            const label = document.getElementById('ska-col-options-label');
            
            // Ẩn tất cả trước
            wrapper.classList.add('hidden');
            rollupWrapper.classList.add('hidden');
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
                rollupWrapper.classList.remove('hidden');
            }
        });
    }

    const editColTypeSelect = document.getElementById('ska-edit-col-type');
    if (editColTypeSelect) {
        editColTypeSelect.addEventListener('change', function() {
            const wrapper = document.getElementById('ska-edit-col-options-wrapper');
            const rollupWrapper = document.getElementById('ska-edit-col-options-rollup-wrapper');
            const txtInput = document.getElementById('ska-edit-col-options');
            const selInput = document.getElementById('ska-edit-col-options-relation');
            const label = document.getElementById('ska-edit-col-options-label');
            
            wrapper.classList.add('hidden');
            rollupWrapper.classList.add('hidden');
            txtInput.classList.add('hidden');
            selInput.classList.add('hidden');

            if (this.value === 'select' || this.value === 'multi_select') {
                wrapper.classList.remove('hidden');
                txtInput.classList.remove('hidden');
                if (label) label.innerText = 'Danh Sách Lựa Chọn (Option List)';
            } else if (this.value === 'relation') {
                wrapper.classList.remove('hidden');
                selInput.classList.remove('hidden');
                if (label) label.innerText = 'Bảng Đích Khai Thác (Target Table)';
            } else if (this.value === 'rollup') {
                wrapper.classList.remove('hidden');
                rollupWrapper.classList.remove('hidden');
            }
        });
    }

    // Bật Sự kiện cho Cascading Rollups
    bindRollupCascading('ska-col-options-rollup-rel', 'ska-col-options-rollup-target');
    bindRollupCascading('ska-edit-col-options-rollup-rel', 'ska-edit-col-options-rollup-target');

    // CHIẾN LƯỢC SCHEMA: TẠO CỘT VẬT LÝ MỚI
    const addColBtn = document.getElementById('ska-submit-col-btn');
    if ( addColBtn ) {
        addColBtn.addEventListener('click', function() {
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

            const formData = new URLSearchParams();
            formData.append('action', 'ska_data_add_column');
            formData.append('security', nonce);
            formData.append('table', tableId);
            formData.append('label', labelValue);
            formData.append('type', typeInput.value);
            
            let optsValue = '';
            if (typeInput.value === 'relation') {
                optsValue = document.getElementById('ska-col-options-relation').value;
            } else if (typeInput.value === 'rollup') {
                const relVal = document.getElementById('ska-col-options-rollup-rel').value;
                const tgtVal = document.getElementById('ska-col-options-rollup-target').value;
                optsValue = relVal + ',' + tgtVal;
                
                // Nếu chọn thiếu thì validate
                if (!relVal || !tgtVal) {
                    alert('Bạn cần chọn đầy đủ Cột Tham Chiếu và Cột Tra Cứu cho chức năng Rollup!');
                    addColBtn.disabled = false;
                    addColBtn.innerHTML = 'Tạo Trường Dữ Liệu';
                    return;
                }
            } else {
                if(optsInput) optsValue = optsInput.value;
            }
            formData.append('options', optsValue);

            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        window.location.reload(); 
                    } else {
                        alert(res.data.message);
                        addColBtn.disabled = false;
                        addColBtn.innerHTML = 'Tạo Trường Dữ Liệu';
                    }
                })
                .catch(() => { 
                    alert('Lỗi khởi tạo'); 
                    addColBtn.disabled = false;
                    addColBtn.innerHTML = 'Tạo Trường Dữ Liệu';
                });
        });
    }

    // CHIẾN LƯỢC SCHEMA: CHỈNH SỬA THUỘC TÍNH CỘT (RENAME & MODIFY)
    const updateColBtn = document.getElementById('ska-update-col-btn');
    if ( updateColBtn ) {
        updateColBtn.addEventListener('click', function() {
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

            const fd = new URLSearchParams();
            fd.append('action', 'ska_data_update_column');
            fd.append('security', nonce);
            fd.append('table', tableId);
            fd.append('col', slug);
            fd.append('label', label);
            fd.append('type', type);
            fd.append('options', opts);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) window.location.reload();
                    else { alert(res.data.message); updateColBtn.disabled=false; updateColBtn.innerText='Lưu Thuộc Tính'; }
                }).catch(()=>alert('Network Error'));
        });
    }

    // CHIẾN LƯỢC SCHEMA: TÀN SÁT CỘT DỮ LIỆU BẰNG RÌU (DROP COLUMN)
    const exDelColBtn = document.getElementById('ska-execute-del-col-btn');
    if ( exDelColBtn ) {
        exDelColBtn.addEventListener('click', function() {
            const slug = document.getElementById('ska-del-col-slug').value;

            exDelColBtn.disabled = true;
            exDelColBtn.innerText = 'Đang thi hành án...';

            const fd = new URLSearchParams();
            fd.append('action', 'ska_data_drop_column');
            fd.append('security', nonce);
            fd.append('table', tableId);
            fd.append('col', slug);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) window.location.reload();
                    else { alert(res.data.message); exDelColBtn.disabled=false; exDelColBtn.innerText='Trảm (Xóa Mãi Mãi)'; }
                }).catch(()=>alert('Network Error'));
        });
    }

    // CHIẾN LƯỢC TABLE: TẠO BẢNG
    const exCreateTblBtn = document.getElementById('ska-execute-create-table-btn');
    if ( exCreateTblBtn ) {
        exCreateTblBtn.addEventListener('click', function() {
            const name  = document.getElementById('ska-new-table-name').value.trim();
            const icon  = document.getElementById('ska-new-table-icon').value;
            const group = document.getElementById('ska-new-table-group').value;

            if(!name) { alert('Vui lòng điền tên bảng (vd: Khách Hàng).'); return; }
            exCreateTblBtn.disabled = true;
            exCreateTblBtn.innerText = 'Đang khởi tạo...';

            const fd = new URLSearchParams();
            fd.append('action', 'ska_data_create_table');
            fd.append('security', nonce);
            fd.append('name', name);
            fd.append('icon', icon);
            fd.append('group', group);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const baseUrl = window.location.href.split('&table=')[0];
                        window.location.href = baseUrl + '&table=' + res.data.table;
                    } else { alert(res.data.message); exCreateTblBtn.disabled=false; exCreateTblBtn.innerText='Tạo Bảng'; }
                }).catch(()=>alert('Network Error'));
        });
    }

    // CHIẾN LƯỢC TABLE: ĐỔI TÊN BẢNG
    const exRenameTblBtn = document.getElementById('ska-execute-rename-table-btn');
    if ( exRenameTblBtn ) {
        exRenameTblBtn.addEventListener('click', function() {
            const slug  = document.getElementById('ska-rename-table-slug').value;
            const name  = document.getElementById('ska-rename-table-name').value.trim();
            const icon  = document.getElementById('ska-rename-table-icon').value;
            const group = document.getElementById('ska-rename-table-group').value;

            if(!name) { alert('Vui lòng điền tên bảng.'); return; }
            exRenameTblBtn.disabled = true;
            exRenameTblBtn.innerText = 'Đang cập nhật...';

            const fd = new URLSearchParams();
            fd.append('action', 'ska_data_rename_table');
            fd.append('security', nonce);
            fd.append('table', slug);
            fd.append('name', name);
            fd.append('icon', icon);
            fd.append('group', group);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) window.location.reload();
                    else { alert(res.data.message); exRenameTblBtn.disabled=false; exRenameTblBtn.innerText='Lưu Thông Tin'; }
                }).catch(()=>alert('Network Error'));
        });
    }

    // CHIẾN LƯỢC TABLE: XÓA BẢNG 
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

        exDelTblBtn.addEventListener('click', function() {
            const slug = document.getElementById('ska-del-tbl-slug').value;

            exDelTblBtn.disabled = true;
            exDelTblBtn.innerHTML = 'Đang phi tang...';

            const fd = new URLSearchParams();
            fd.append('action', 'ska_data_drop_table');
            fd.append('security', nonce);
            fd.append('table', slug);

            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        window.location.href = window.location.href.split('&table=')[0];
                    }
                    else { alert(res.data.message); exDelTblBtn.disabled=false; exDelTblBtn.innerHTML='Chấp nhận Rủi ro & Xóa'; }
                }).catch(()=>alert('Network Error'));
        });
    }

    // 2. CHỨC NĂNG: THÊM DÒNG MỚI (Hỗ trợ nhiều nút qua class ska-add-row-trigger)
    const addBtns = document.querySelectorAll('.ska-add-row-trigger');
    addBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (btn.tagName === 'BUTTON') {
                btn.disabled = true;
                btn.innerHTML = '<span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang xử lý...';
            } else {
                btn.style.pointerEvents = 'none';
                btn.innerHTML = '<span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang tạo...';
            }

            const formData = new URLSearchParams();
            formData.append('action', 'ska_data_add_row');
            formData.append('security', nonce);
            formData.append('table', tableId);

            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.success) { window.location.reload(); } 
                    else {
                        alert(res.data.message);
                        window.location.reload(); 
                    }
                })
                .catch(() => { alert('Lỗi mạng'); window.location.reload(); });
        });
    });

    // 3. CHỨC NĂNG: SỬA NỘI TUYẾN (INLINE EDITING)
    let activeInput = null;

    document.querySelectorAll('.ska-editable-cell').forEach(cell => {
        if ( cell.getAttribute('data-col') === 'id' ) {
            cell.classList.remove('ska-editable-cell', 'cursor-text', 'hover:bg-gray-100/80');
            cell.title = "Khoá chính (ID) không thể sửa";
            cell.style.cursor = 'not-allowed';
            return;
        }

        cell.addEventListener('click', function() {
            if (this.querySelector('input') || this.querySelector('select') || this.querySelector('textarea')) return;
            if (activeInput) { activeInput.blur(); }

            const colName = this.getAttribute('data-col');
            const type    = this.getAttribute('data-type');
            const val     = this.getAttribute('data-value');
            const optsStr = this.getAttribute('data-options');
            const rowId   = this.closest('tr').getAttribute('data-id');
            const contentDiv = this.querySelector('.ska-cell-content');

            // --- LOGIC XỬ LÝ NHANH CHO BOOLEAN ---
            if (type === 'boolean') {
                const newVal = (val == '1') ? '0' : '1';
                this.classList.add('bg-blue-50', 'opacity-50');
                
                const fd = new URLSearchParams();
                fd.append('action', 'ska_data_update_cell');
                fd.append('security', nonce);
                fd.append('table', tableId);
                fd.append('id', rowId);
                fd.append('column', colName);
                fd.append('value', newVal);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(r => r.json()).then(res => {
                        this.classList.remove('bg-blue-50', 'opacity-50');
                        if (res.success) {
                            this.setAttribute('data-value', newVal);
                            
                            const is_checked = (newVal == '1');
                            const bg_class = is_checked ? 'bg-emerald-500' : 'bg-gray-300';
                            const translate_class = is_checked ? 'translate-x-3.5' : 'translate-x-0.5';
                            
                            contentDiv.innerHTML = `<div class="w-8 h-4 flex items-center rounded-full transition-colors pointer-events-none ${bg_class}"><div class="w-3.5 h-3.5 bg-white rounded-full shadow-sm transform transition-transform ${translate_class}"></div></div>`;
                        } else alert(res.data.message);
                    });
                return;
            }

            // --- LOGIC XỬ LÝ CHỌN ẢNH (MEDIA) ---
            if (type === 'media') {
                if (typeof wp !== 'undefined' && wp.media) {
                    let file_frame = wp.media({ title: 'Chọn Hình Ảnh', button: { text: 'Dùng Ảnh Này' }, multiple: false });
                    file_frame.on('select', () => {
                        const attachment = file_frame.state().get('selection').first().toJSON();
                        const newVal = attachment.url;
                        if (newVal == val) return;
                        
                        this.classList.add('bg-blue-50', 'opacity-50');
                        const fd = new URLSearchParams();
                        fd.append('action', 'ska_data_update_cell');
                        fd.append('security', nonce);
                        fd.append('table', tableId);
                        fd.append('id', rowId);
                        fd.append('column', colName);
                        fd.append('value', newVal);

                        fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                            this.classList.remove('bg-blue-50', 'opacity-50');
                            if (res.success) {
                                this.setAttribute('data-value', newVal);
                                contentDiv.innerHTML = `<img src="${newVal}" class="h-6 w-6 object-cover rounded border border-gray-200 inline-block mr-2"><span class="text-[11px] text-gray-400">Media</span>`;
                            } else alert(res.data.message);
                        });
                    });
                    file_frame.open();
                } else alert('Chưa tải được thư viện wp.media');
                return;
            }

            // --- LOGIC XỬ LÝ CHỌN NHIỀU ẢNH (MEDIA GALLERY) ---
            if (type === 'media_gallery') {
                if (document.getElementById('ska-datagrid-popover')) {
                    document.getElementById('ska-datagrid-popover').remove();
                }

                const popover = document.createElement('div');
                popover.id = 'ska-datagrid-popover';
                popover.className = 'absolute top-full left-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-2 text-sm font-normal text-gray-800 flex flex-col gap-2';
                
                let selectedArray = (val || '').split(',').map(s=>s.trim()).filter(s=>s!=='');
                let isMediaOpen = false;

                const closePopover = (e) => {
                    // Ngăn việc xóa Popover khi wp.media đang được mở hoặc nhấp vào backdrop của nó
                    if (isMediaOpen || (e.target.closest && (e.target.closest('.media-modal') || e.target.closest('.media-modal-backdrop')))) return;

                    if(!popover.contains(e.target) && !this.contains(e.target)) {
                        popover.remove();
                        document.removeEventListener('mousedown', closePopover);
                    }
                };
                setTimeout(() => document.addEventListener('mousedown', closePopover), 10);

                const renderGallery = () => {
                    popover.innerHTML = '';
                    
                    const grid = document.createElement('div');
                    grid.className = 'grid grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1 ska-datagrid-scroll';
                    
                    selectedArray.forEach((url, idx) => {
                        const imgWrap = document.createElement('div');
                        imgWrap.className = 'relative group aspect-square bg-gray-50 rounded border border-gray-200 overflow-hidden shadow-sm';
                        
                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'w-full h-full object-cover';
                        
                        const delBtn = document.createElement('div');
                        delBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity scale-90 hover:scale-100 shadow-sm';
                        delBtn.innerHTML = '<span class="dashicons dashicons-no-alt" style="font-size: 10px; margin-top: -3px; margin-left: -1px;"></span>';
                        delBtn.onclick = (e) => {
                            e.stopPropagation();
                            selectedArray.splice(idx, 1);
                            renderGallery();
                        };
                        
                        imgWrap.appendChild(img);
                        imgWrap.appendChild(delBtn);
                        grid.appendChild(imgWrap);
                    });
                    
                    if (selectedArray.length === 0) {
                        grid.className = 'text-center p-4 text-xs text-gray-400 italic bg-gray-50 rounded border border-dashed border-gray-200';
                        grid.innerText = 'Trống. Xin hãy thêm ảnh vào.';
                    }
                    
                    popover.appendChild(grid);
                    
                    const btnWrap = document.createElement('div');
                    btnWrap.className = 'flex gap-2 mt-1';
                    
                    const addBtn = document.createElement('button');
                    addBtn.className = 'flex-1 bg-white border border-emerald-500 text-emerald-600 hover:bg-emerald-50 text-xs py-1.5 rounded font-bold transition-colors flex justify-center items-center shadow-sm';
                    addBtn.innerHTML = '<span class="dashicons dashicons-plus" style="font-size: 14px; margin-top: -1px; margin-right: 2px;"></span> Thêm Ảnh';
                    addBtn.onclick = (e) => {
                        e.stopPropagation();
                        if (typeof wp !== 'undefined' && wp.media) {
                            let file_frame = wp.media({ title: 'Chọn Ảnh', button: { text: 'Thêm' }, multiple: false });
                            
                            file_frame.on('open', () => { isMediaOpen = true; });
                            file_frame.on('close', () => { setTimeout(() => { isMediaOpen = false; }, 200); });
                            
                            file_frame.on('select', () => {
                                const attachment = file_frame.state().get('selection').first().toJSON();
                                selectedArray.push(attachment.url);
                                renderGallery();
                            });
                            file_frame.open();
                        } else alert('Chưa tải được wp.media');
                    };
                    
                    const applyBtn = document.createElement('button');
                    applyBtn.className = 'flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs py-1.5 rounded font-bold transition-colors flex justify-center items-center shadow-sm';
                    applyBtn.innerHTML = '<span class="dashicons dashicons-saved" style="font-size: 14px; margin-top: -1px; margin-right: 2px;"></span> Đóng Dấu';
                    applyBtn.onclick = (e) => {
                        e.stopPropagation();
                        const newCsv = selectedArray.join(', ');
                        if (newCsv !== this.getAttribute('data-value')) {
                            updateCellDb(newCsv);
                        } else {
                            popover.remove();
                            document.removeEventListener('mousedown', closePopover);
                        }
                    };
                    
                    btnWrap.appendChild(addBtn);
                    btnWrap.appendChild(applyBtn);
                    popover.appendChild(btnWrap);
                };

                const updateCellDb = (newVal) => {
                    this.classList.add('bg-blue-50', 'opacity-50');
                    const fd = new URLSearchParams();
                    fd.append('action', 'ska_data_update_cell');
                    fd.append('security', nonce);
                    fd.append('table', tableId);
                    fd.append('id', rowId);
                    fd.append('column', colName);
                    fd.append('value', newVal);
                    fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                        this.classList.remove('bg-blue-50', 'opacity-50');
                        if (res.success) {
                            this.setAttribute('data-value', newVal);
                            
                            const urls = newVal.split(',').map(s=>s.trim()).filter(s=>s!=='');
                            if (urls.length === 0) {
                                contentDiv.innerHTML = '<span class="text-gray-300 italic opacity-50">#</span>';
                            } else {
                                let html = '<div class="flex items-center -space-x-2">';
                                const limit = Math.min(3, urls.length);
                                for (let i = 0; i < limit; i++) {
                                    html += `<img src="${urls[i]}" class="h-6 w-6 object-cover rounded-full border border-white ring-1 ring-gray-200 relative z-${30-i} bg-gray-100 shadow-sm">`;
                                }
                                if (urls.length > 3) {
                                    html += `<span class="flex items-center justify-center h-6 w-6 rounded-full border border-white bg-gray-100 text-[10px] text-gray-500 font-medium relative z-0 ring-1 ring-gray-200">+${urls.length - 3}</span>`;
                                }
                                html += '</div>';
                                contentDiv.innerHTML = html;
                            }
                            
                            if (document.getElementById('ska-datagrid-popover')) document.getElementById('ska-datagrid-popover').remove();
                            document.removeEventListener('mousedown', closePopover);
                        } else alert(res.data.message);
                    });
                };

                renderGallery();
                this.appendChild(popover);
                return;
            }

            // --- LOGIC XỬ LÝ DROPDOWN SELECT CUSTOM ---
            if (type === 'select' || type === 'multi_select') {
                if (document.getElementById('ska-datagrid-popover')) {
                    document.getElementById('ska-datagrid-popover').remove();
                }

                const popover = document.createElement('div');
                popover.id = 'ska-datagrid-popover';
                popover.className = 'absolute top-full left-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-1 text-sm font-normal text-gray-800 flex flex-col gap-0.5';
                
                // Thu thập cấu hình chọn nhiều
                let selectedArray = [];
                if (type === 'multi_select') {
                    selectedArray = (val || '').split(',').map(s=>s.trim()).filter(s=>s!=='');
                }

                // Click outside to close
                const closePopover = (e) => {
                    if(!popover.contains(e.target) && !this.contains(e.target)) {
                        popover.remove();
                        document.removeEventListener('mousedown', closePopover);
                    }
                };
                setTimeout(() => document.addEventListener('mousedown', closePopover), 10);

                const currentOptsStr = this.getAttribute('data-options');
                const optsSplit = (currentOptsStr || '').split(',').map(o=>o.trim()).filter(o=>o!=='');

                const renderOptions = () => {
                    popover.innerHTML = '';
                    
                    optsSplit.forEach(o => {
                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'flex items-center justify-between px-2 py-1.5 hover:bg-emerald-50 rounded cursor-pointer group transition-colors';
                        
                        let isSel = false;
                        if (type === 'select') isSel = (o === this.getAttribute('data-value'));
                        else isSel = selectedArray.includes(o);
                        
                        // Icon Checkbox Ảo Diệu cho Multi Select
                        if (type === 'multi_select') {
                            const chk = document.createElement('input');
                            chk.type = 'checkbox';
                            chk.checked = isSel;
                            chk.className = 'mr-2 rounded text-emerald-500 border-gray-300 w-3.5 h-3.5 focus:ring-emerald-500 pointer-events-none';
                            rowDiv.appendChild(chk);
                        }

                        const labelSpan = document.createElement('span');
                        labelSpan.className = 'flex-1 truncate ' + (isSel ? 'font-bold text-emerald-600' : 'text-gray-700 pointer-events-none');
                        labelSpan.innerText = o;

                        rowDiv.onclick = () => {
                            if (type === 'select') {
                                if (o !== this.getAttribute('data-value')) {
                                    updateCellDb(o);
                                }
                                popover.remove();
                                document.removeEventListener('mousedown', closePopover);
                            } else {
                                // Logic Nháy Checkbox (Multi-select)
                                if (selectedArray.includes(o)) {
                                    selectedArray = selectedArray.filter(v => v !== o);
                                } else {
                                    selectedArray.push(o);
                                }
                                renderOptions(); // Rerender Checkbox state
                            }
                        };

                        const editBtn = document.createElement('span');
                        editBtn.className = 'dashicons dashicons-edit text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-blue-100';
                        editBtn.style.fontSize = '14px';
                        editBtn.style.width = '20px';
                        editBtn.style.height = '20px';
                        editBtn.onclick = (e) => {
                            e.stopPropagation();
                            rowDiv.innerHTML = '';
                            const editInput = document.createElement('input');
                            editInput.className = 'w-full text-xs px-2 py-1 border border-blue-500 rounded outline-none shadow-inner bg-blue-50';
                            editInput.value = o;
                            rowDiv.appendChild(editInput);
                            editInput.focus();
                            
                            editInput.onkeydown = (ev) => {
                                if (ev.key === 'Enter') {
                                    ev.preventDefault();
                                    const newOptVal = editInput.value.trim();
                                    if (newOptVal && newOptVal !== o) {
                                        manageOptionDb('edit', o, newOptVal);
                                    } else {
                                        renderOptions();
                                    }
                                }
                            };
                            editInput.onblur = () => renderOptions();
                        };

                        rowDiv.appendChild(labelSpan);
                        rowDiv.appendChild(editBtn);
                        popover.appendChild(rowDiv);
                    });

                    const divider = document.createElement('div');
                    divider.className = 'h-px w-full bg-gray-100 my-1';
                    popover.appendChild(divider);

                    const addBtn = document.createElement('div');
                    addBtn.className = 'flex items-center gap-1 px-2 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 rounded cursor-pointer transition-colors';
                    addBtn.innerHTML = '<span class="dashicons dashicons-plus-alt2" style="font-size:14px; margin-top:-2px"></span> Thêm Option Mới';
                    
                    addBtn.onclick = (e) => {
                        e.stopPropagation();
                        popover.innerHTML = '';
                        const wrapInput = document.createElement('div');
                        wrapInput.className = 'p-1 relative';
                        const addInput = document.createElement('input');
                        addInput.placeholder = 'Nhập tên mới + Enter...';
                        addInput.className = 'w-full text-xs px-2 py-1.5 rounded border border-emerald-400 outline-none shadow-inner bg-emerald-50';
                        wrapInput.appendChild(addInput);
                        popover.appendChild(wrapInput);
                        addInput.focus();

                        addInput.onkeydown = (ev) => {
                            if (ev.key === 'Enter') {
                                ev.preventDefault();
                                const newOptVal = addInput.value.trim();
                                if (newOptVal && !optsSplit.includes(newOptVal)) {
                                    manageOptionDb('add', '', newOptVal);
                                } else {
                                    renderOptions();
                                }
                            }
                        };
                    };
                    popover.appendChild(addBtn);

                    // --- NÚT ĐÓNG DẤU CHẠY DATABASE (Dành riêng Multi Select) ---
                    if (type === 'multi_select') {
                        const applyBtn = document.createElement('button');
                        applyBtn.className = 'mt-2 w-full bg-emerald-500 hover:bg-emerald-600 text-white rounded text-xs py-2 font-bold transition-colors uppercase outline-none focus:outline-none flex justify-center items-center shadow-sm';
                        applyBtn.innerHTML = '<span class="dashicons dashicons-saved" style="font-size: 14px; margin-top: -1px; margin-right: 4px;"></span> Đóng Dấu Áp Dụng';
                        applyBtn.onclick = (e) => {
                            e.stopPropagation();
                            const newCsv = selectedArray.join(', ');
                            if (newCsv !== this.getAttribute('data-value')) {
                                updateCellDb(newCsv);
                            } else {
                                popover.remove();
                                document.removeEventListener('mousedown', closePopover);
                            }
                        };
                        popover.appendChild(applyBtn);
                    }
                };

                const updateCellDb = (newVal) => {
                    this.classList.add('bg-blue-50', 'opacity-50');
                    const fd = new URLSearchParams();
                    fd.append('action', 'ska_data_update_cell');
                    fd.append('security', nonce);
                    fd.append('table', tableId);
                    fd.append('id', rowId);
                    fd.append('column', colName);
                    fd.append('value', newVal);
                    fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                        this.classList.remove('bg-blue-50', 'opacity-50');
                        if (res.success) {
                            this.setAttribute('data-value', newVal);
                            if (type === 'select') {
                                contentDiv.innerHTML = `<span class="bg-blue-100 text-blue-700 text-[11px] px-2 py-0.5 rounded-full border border-blue-200">${newVal}</span>`;
                            } else {
                                const arr = newVal.split(',').map(s=>s.trim()).filter(s=>s!=='');
                                if(arr.length === 0) contentDiv.innerHTML = '<span class="text-gray-300 italic opacity-50">#</span>';
                                else {
                                    let html = '<div class="flex flex-wrap gap-1">';
                                    arr.forEach(s => html += `<span class="bg-purple-100 text-purple-700 text-[11px] px-2 py-px rounded border border-purple-200">${s}</span>`);
                                    html += '</div>';
                                    contentDiv.innerHTML = html;
                                }
                            }
                            if (document.getElementById('ska-datagrid-popover')) document.getElementById('ska-datagrid-popover').remove();
                            document.removeEventListener('mousedown', closePopover);
                        } else alert(res.data.message);
                    });
                };

                const manageOptionDb = (action, oldVal, newVal) => {
                    popover.innerHTML = '<div class="p-4 text-center text-xs text-emerald-600 font-bold"><span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span><br>Đang đồng bộ Mass Update...</div>';
                    
                    const fd = new URLSearchParams();
                    fd.append('action', 'ska_data_manage_select_option');
                    fd.append('security', nonce);
                    fd.append('table', tableId);
                    fd.append('column', colName);
                    fd.append('opt_action', action);
                    fd.append('old_val', oldVal);
                    fd.append('new_val', newVal);
                    
                    fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                        if (res.success) window.location.reload(); // Bắt buộc Reload để toàn bộ Grid nhận diện Tên Mới
                        else { alert(res.data.message); renderOptions(); }
                    }).catch(()=>{ alert('Lỗi mạng'); renderOptions(); });
                };

                renderOptions();
                this.appendChild(popover);
                return; // Kết thúc sớm nhánh Select để không đụng tới Logic Blur của Input bên dưới
            }

            // --- LOGIC XỬ LÝ RELATION (THAM CHIẾU NỐI BẢNG LÕI) ---
            if (type === 'relation') {
                if (document.getElementById('ska-datagrid-popover')) {
                    document.getElementById('ska-datagrid-popover').remove();
                }

                const popover = document.createElement('div');
                popover.id = 'ska-datagrid-popover';
                popover.className = 'absolute top-full left-0 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-2 text-sm font-normal text-gray-800 flex flex-col gap-2 cursor-auto';
                
                let relationData = [];
                try { if (val) relationData = JSON.parse(val); } catch(e) {}
                if (!Array.isArray(relationData)) relationData = [];

                const targetTable = this.getAttribute('data-options');

                const closePopover = (e) => {
                    if(!popover.contains(e.target) && !this.contains(e.target)) {
                        popover.remove();
                        document.removeEventListener('mousedown', closePopover);
                    }
                };
                setTimeout(() => document.addEventListener('mousedown', closePopover), 10);

                let searchTimeout = null;
                let searchResults = [];

                const renderUI = () => {
                    popover.innerHTML = '';
                    
                    if (relationData.length > 0) {
                        const selBox = document.createElement('div');
                        selBox.className = 'flex flex-wrap gap-1 mb-1 p-1.5 bg-gray-50 rounded border border-gray-100 max-h-24 overflow-y-auto ska-datagrid-scroll';
                        relationData.forEach((item, idx) => {
                            const badge = document.createElement('span');
                            badge.className = 'flex items-center gap-1 bg-indigo-100 text-indigo-700 text-[11px] font-medium px-2 py-0.5 rounded border border-indigo-200';
                            badge.innerHTML = `<span>${item.label}</span> <span class="dashicons dashicons-no-alt text-red-500 hover:text-red-700 cursor-pointer scale-75 hover:scale-100 transition-transform"></span>`;
                            badge.querySelector('.dashicons').onclick = (e) => {
                                e.stopPropagation();
                                relationData.splice(idx, 1);
                                renderUI();
                            };
                            selBox.appendChild(badge);
                        });
                        popover.appendChild(selBox);
                    } else {
                        const emptyBox = document.createElement('div');
                        emptyBox.className = 'text-[11px] text-gray-400 italic mb-1 px-1';
                        emptyBox.innerText = 'Trống (Chưa tham chiếu ai).';
                        popover.appendChild(emptyBox);
                    }

                    const searchInput = document.createElement('input');
                    searchInput.type = 'text';
                    searchInput.placeholder = 'Gõ tìm kiếm bản ghi...';
                    searchInput.className = 'w-full px-2 py-1.5 text-xs border border-gray-300 rounded shadow-inner outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400';
                    popover.appendChild(searchInput);

                    const resContainer = document.createElement('div');
                    resContainer.className = 'max-h-40 overflow-y-auto ska-datagrid-scroll border border-gray-100 rounded hidden shadow-inner';
                    popover.appendChild(resContainer);

                    const performSearch = (keyword) => {
                        resContainer.innerHTML = '<div class="p-2 text-center text-[10px] text-gray-400"><span class="dashicons dashicons-update-alt" style="animation: spin 1s infinite linear;"></span> Đang tải...</div>';
                        resContainer.classList.remove('hidden');

                        const fd = new URLSearchParams();
                        fd.append('action', 'ska_data_search_relation');
                        fd.append('security', nonce);
                        fd.append('target_table', targetTable);
                        fd.append('keyword', keyword);

                        fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                            if (res.success && res.data.items) {
                                resContainer.innerHTML = '';
                                searchResults = res.data.items;
                                if (searchResults.length === 0) {
                                    resContainer.innerHTML = '<div class="p-2 text-center text-xs text-gray-500 italic">No Data.</div>';
                                    return;
                                }
                                searchResults.forEach(item => {
                                    const isSel = relationData.some(r => parseInt(r.id) === parseInt(item.id));
                                    if (isSel) return;

                                    const row = document.createElement('div');
                                    row.className = 'px-2 py-1.5 text-xs text-gray-700 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors border-b border-gray-50/50 last:border-b-0';
                                    row.innerHTML = `<span class="truncate pr-2 font-medium">${item.label}</span> <span class="bg-gray-100 font-mono text-gray-400 text-[9px] px-1 rounded border border-gray-200">#${item.id}</span>`;
                                    row.onclick = (e) => {
                                        e.stopPropagation();
                                        relationData.push(item);
                                        renderUI();
                                    };
                                    resContainer.appendChild(row);
                                });
                                if (resContainer.innerHTML === '') {
                                    resContainer.innerHTML = '<div class="p-2 text-center text-[10px] text-gray-400 bg-gray-50 italic">Hết kết quả khả dụng.</div>';
                                }
                            } else {
                                resContainer.innerHTML = `<div class="p-2 text-xs text-red-500">${res.data.message || 'Lỗi'}</div>`;
                            }
                        }).catch(()=>{ resContainer.innerHTML = '<div class="p-2 text-xs text-red-500">Mất kết nối API</div>'; });
                    };

                    searchInput.onkeyup = (e) => {
                        if (searchTimeout) clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => { performSearch(searchInput.value.trim()); }, 300);
                    };
                    
                    setTimeout(() => { if (searchInput.value === '') performSearch(''); }, 10);
                    setTimeout(() => searchInput.focus(), 50);

                    const divider = document.createElement('div');
                    divider.className = 'h-px w-full bg-gray-100 my-1';
                    popover.appendChild(divider);

                    const applyBtn = document.createElement('button');
                    applyBtn.className = 'w-full bg-indigo-500 hover:bg-indigo-600 text-white rounded text-xs py-1.5 font-bold transition-colors flex justify-center items-center shadow-sm';
                    applyBtn.innerHTML = '<span class="dashicons dashicons-saved" style="font-size: 14px; margin-top: -1px; margin-right: 4px;"></span> Lưu Tham Chiếu';
                    applyBtn.onclick = (e) => {
                        e.stopPropagation();
                        // Backend mong chờ mảng ID thuần túy "15, 20" để ghi vào column phẳng
                        const newCsv = relationData.map(r => r.id).join(', ');
                        const newJsonStr = JSON.stringify(relationData);
                        
                        if (newJsonStr !== this.getAttribute('data-value')) {
                            this.classList.add('bg-blue-50', 'opacity-50');
                            const fd = new URLSearchParams();
                            fd.append('action', 'ska_data_update_cell');
                            fd.append('security', nonce);
                            fd.append('table', tableId);
                            fd.append('id', rowId);
                            fd.append('column', colName);
                            fd.append('value', newCsv); // Send Flat CSV
                            
                            fetch(ajaxurl, { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                                this.classList.remove('bg-blue-50', 'opacity-50');
                                if (res.success) {
                                    // Trực tiếp tải lại toàn bộ khung cảnh (Reload) để các cột Rollup (Computed Columns) 
                                    // được máy chủ PHP (Data_Fetcher) tính toán lại và nhồi dữ liệu mới nhất.
                                    window.location.reload();
                                } else alert(res.data.message);
                            }).catch(() => { this.classList.remove('bg-blue-50', 'opacity-50'); alert('Mạng bị xịt!'); });
                        } else {
                            popover.remove();
                            document.removeEventListener('mousedown', closePopover);
                        }
                    };
                    popover.appendChild(applyBtn);
                };

                renderUI();
                this.appendChild(popover);
                return;
            }

            contentDiv.style.display = 'none';
            let input;

            if (type === 'long_text') {
                input = document.createElement('textarea');
                input.value = (val !== null && val !== '') ? val : '';
                input.className = 'w-full min-h-[90px] h-[90px] bg-white border-2 border-emerald-500 rounded outline-none p-2 text-sm text-gray-800 absolute top-0 -left-1 z-50 shadow-2xl resize-y';
                input.style.minWidth = '250px';
            } else {
                input = document.createElement('input');
                input.type = type;
                input.value = (val !== null && val !== '') ? val : '';
                input.className = 'w-full h-full bg-white border-2 border-emerald-500 rounded outline-none px-2 py-1 text-sm text-gray-800 absolute inset-0 z-50 shadow-sm';
            }
            
            this.appendChild(input);
            input.focus();
            if(type === 'text' || type === 'number') input.select();
            
            activeInput = input;

            const saveFunc = () => {
                const newVal = input.value;
                const oldVal = this.getAttribute('data-value');

                activeInput = null;

                if ( newVal == oldVal ) {
                    input.remove();
                    contentDiv.style.display = 'block';
                    return;
                }

                this.classList.add('bg-blue-50', 'opacity-50');
                
                const formData = new URLSearchParams();
                formData.append('action', 'ska_data_update_cell');
                formData.append('security', nonce);
                formData.append('table', tableId);
                formData.append('id', rowId);
                formData.append('column', colName);
                formData.append('value', newVal);

                fetch(ajaxurl, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(res => {
                        this.classList.remove('bg-blue-50', 'opacity-50');
                        input.remove();
                        contentDiv.style.display = 'block';

                        if (res.success) {
                            this.setAttribute('data-value', newVal);
                            if( newVal === '' ) {
                                contentDiv.innerHTML = '<span class="text-gray-300 italic opacity-50">#</span>';
                            } else {
                                if (type === 'long_text') {
                                    contentDiv.innerHTML = `<span class="dashicons dashicons-editor-justify text-gray-400 origin-left scale-75 mr-1 align-middle"></span><span class="text-gray-400 italic text-xs">Đã lưu văn bản...</span>`;
                                } else {
                                    contentDiv.textContent = newVal; 
                                }
                            }
                            this.classList.add('bg-emerald-100', 'transition-colors');
                            setTimeout(() => { this.classList.remove('bg-emerald-100'); }, 500);
                        } else {
                            alert(res.data.message);
                            input.value = oldVal;
                        }
                    })
                    .catch(() => { alert('Lỗi mạng'); input.remove(); contentDiv.style.display = 'block'; });
            };

            input.addEventListener('blur', saveFunc);
            input.addEventListener('keydown', function(e) {
                if(e.key === 'Enter') {
                    e.preventDefault();
                    input.blur(); 
                }
            });
        });
    });

    // =========================================================
    // 6. XỬ LÝ FILTER & GROUP BẰNG GET URL PARAMETERS
    // =========================================================
    const btnFilter = document.getElementById('ska-btn-filter');
    const modalFilter = document.getElementById('ska-filter-data-modal');
    if (btnFilter && modalFilter) {
        btnFilter.addEventListener('click', () => modalFilter.classList.remove('hidden'));
    }

    const btnExecFilter = document.getElementById('ska-execute-filter-btn');
    if (btnExecFilter) {
        btnExecFilter.addEventListener('click', () => {
            const field = document.getElementById('ska-filter-field').value;
            const op    = document.getElementById('ska-filter-op').value;
            const val   = document.getElementById('ska-filter-val').value.trim();
            
            const url = new URL(window.location.href);
            if (val) {
                url.searchParams.set('filter_field', field);
                url.searchParams.set('filter_op', op);
                url.searchParams.set('filter_val', val);
            } else {
                url.searchParams.delete('filter_field');
                url.searchParams.delete('filter_op');
                url.searchParams.delete('filter_val');
            }
            window.location.href = url.toString();
        });
    }

    const btnGroup = document.getElementById('ska-btn-group');
    const modalGroup = document.getElementById('ska-group-data-modal');
    if (btnGroup && modalGroup) {
        btnGroup.addEventListener('click', () => modalGroup.classList.remove('hidden'));
    }

    const btnExecGroup = document.getElementById('ska-execute-group-btn');
    if (btnExecGroup) {
        btnExecGroup.addEventListener('click', () => {
            const field = document.getElementById('ska-group-field').value;
            const url = new URL(window.location.href);
            if (field) {
                url.searchParams.set('group_by', field);
            } else {
                url.searchParams.delete('group_by');
            }
            window.location.href = url.toString();
        });
    }
});

/**
 * Hàm Helper: Xử lý Cascading 2 Select Boxes (Notion Style)
 * Khi chọn Select 1 (Relation Cột), tự động bung Select 2 (Mục Tiêu) của Target Table.
 */
function bindRollupCascading(relSelectId, targetSelectId) {
    const relSelect = document.getElementById(relSelectId);
    const targetSelect = document.getElementById(targetSelectId);
    
    if (!relSelect || !targetSelect) return;

    relSelect.addEventListener('change', function() {
        // Lấy data-target (tên Bảng đích) đang chọn
        const selectedOption = this.options[this.selectedIndex];
        const targetTable = selectedOption ? selectedOption.getAttribute('data-target') : null;
        
        // Reset Option 2
        targetSelect.innerHTML = '<option value="">-- Chọn Cột Tra Cứu --</option>';
        
        if (!targetTable || !this.value) {
            targetSelect.disabled = true;
            return;
        }

        targetSelect.disabled = true; // Temporary disable while loading
        targetSelect.innerHTML = '<option value="">-- Đang tải dữ liệu... --</option>';

		// Đã xóa nhánh tĩnh hardcode cho wp_posts và wp_users.
		// Bây giờ thả trôi cho AJAX Backend gọiska_data_get_table_columns để PHP lấy thêm Meta Keys.

        // FETCH BẰNG AJAX ĐỂ LẤY FULL SCHEMA VẬT LÝ (Khắc phục lỗi Từ Điển thiếu / Không Đồng Bộ)
        jQuery.post(window.skaDataConfig.ajaxurl, {
            action: 'ska_data_get_table_columns',
            security: window.skaDataConfig.nonce,
            target_table: targetTable
        }, function(response) {
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
                targetSelect.innerHTML = '<option value="">-- Bảng không có cột nào --</option>';
            }
            targetSelect.disabled = false;
        }).fail(function() {
            targetSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            targetSelect.disabled = false;
        });
    });
}
