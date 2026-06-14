import { __ } from '@wordpress/i18n';
import BaseCell from '../BaseCell.js';
import { apiFetch, getConfig } from '../../utils/api.js';

export default class RelationCell extends BaseCell {
    edit() {
        if (document.getElementById('ska-datagrid-popover')) {
            document.getElementById('ska-datagrid-popover').remove();
        }

        const popover = document.createElement('div');
        popover.id = 'ska-datagrid-popover';
        popover.className = 'absolute top-full left-0 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-2 text-sm font-normal text-gray-800 flex flex-col gap-2 cursor-auto';
        
        let relationData = [];
        try { if (this.val) relationData = JSON.parse(this.val); } catch(e) {}
        if (!Array.isArray(relationData)) relationData = [];

        const targetTable = this.optionsStr;

        const closePopover = (e) => {
            if(!popover.contains(e.target) && !this.cell.contains(e.target)) {
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
                emptyBox.innerText = __( 'Blank (No reference yet).', 'ska-data-pro' );
                popover.appendChild(emptyBox);
            }

            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = __( 'Type search records...', 'ska-data-pro' );
            searchInput.className = 'w-full px-2 py-1.5 text-xs border border-gray-300 rounded shadow-inner outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400';
            popover.appendChild(searchInput);

            const resContainer = document.createElement('div');
            resContainer.className = 'max-h-40 overflow-y-auto ska-datagrid-scroll border border-gray-100 rounded hidden shadow-inner';
            popover.appendChild(resContainer);

            const performSearch = async (keyword) => {
                resContainer.innerHTML = __( '<div class=\"p-2 text-center text-[10px] text-gray-400\"><span class=\"dashicons dashicons-update-alt\" style=\"animation: spin 1s infinite linear;\"></span> Loading...</div>', 'ska-data-pro' );
                resContainer.classList.remove('hidden');

                const res = await apiFetch('ska_data_search_relation', {
                    target_table: targetTable,
                    keyword: keyword
                });

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
                        resContainer.innerHTML = __( '<div class=\"p-2 text-center text-[10px] text-gray-400 bg-gray-50 italic\">No available results.</div>', 'ska-data-pro' );
                    }
                } else {
                    const errorMsg = res.data?.message || __( 'An error occurred', 'ska-data-pro' );
                    resContainer.innerHTML = `<div class="p-2 text-xs text-red-500">${errorMsg}</div>`;
                }
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
            applyBtn.innerHTML = __( '<span class=\"dashicons dashicons-saved\" style=\"font-size: 14px; margin-top: -1px; margin-right: 4px;\"></span> Save Reference', 'ska-data-pro' );
            
            applyBtn.onclick = async (e) => {
                e.stopPropagation();
                
                const newCsv = relationData.map(r => r.id).join(', ');
                const newJsonStr = JSON.stringify(relationData);
                
                if (newJsonStr !== this.val) {
                    this.cell.classList.add('bg-blue-50', 'opacity-50');
                    
                    const res = await apiFetch('ska_data_update_cell', {
                        id: this.rowId,
                        column: this.colName,
                        value: newCsv // Backend nhận CSV flat IDs
                    });

                    this.cell.classList.remove('bg-blue-50', 'opacity-50');

                    if (res.success) {
                        window.location.reload(); 
                    } else {
                        alert(res.data?.message || __( 'Network crashed!', 'ska-data-pro' ));
                    }
                } else {
                    popover.remove();
                    document.removeEventListener('mousedown', closePopover);
                }
            };
            popover.appendChild(applyBtn);
        };

        renderUI();
        this.cell.appendChild(popover);
    }
}
