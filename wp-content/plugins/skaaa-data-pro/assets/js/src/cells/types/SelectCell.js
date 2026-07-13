import { __ } from '@wordpress/i18n';
import BaseCell from '../BaseCell.js';

export default class SelectCell extends BaseCell {
    edit() {
        if (document.getElementById('skaaa-datagrid-popover')) {
            document.getElementById('skaaa-datagrid-popover').remove();
        }

        const popover = document.createElement('div');
        popover.id = 'skaaa-datagrid-popover';
        popover.className = 'absolute top-full left-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-1 text-sm font-normal text-gray-800 flex flex-col gap-0.5';
        
        let selectedArray = [];
        if (this.type === 'multi_select') {
            selectedArray = (this.val || '').split(',').map(s=>s.trim()).filter(s=>s!=='');
        }

        const closePopover = (e) => {
            if(!popover.contains(e.target) && !this.cell.contains(e.target)) {
                popover.remove();
                document.removeEventListener('mousedown', closePopover);
            }
        };
        setTimeout(() => document.addEventListener('mousedown', closePopover), 10);

        const currentOptsStr = this.optionsStr;
        const optsSplit = (currentOptsStr || '').split(',').map(o=>o.trim()).filter(o=>o!=='');

        const manageOptionDb = async (action, oldVal, newVal) => {
            alert(__( 'The feature to change Database options directly will be implemented later.', 'skaaa-data-pro' ));
            // Implement API Update Column Options...
        };

        const renderOptions = () => {
            popover.innerHTML = '';
            
            optsSplit.forEach(o => {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'flex items-center justify-between px-2 py-1.5 hover:bg-emerald-50 rounded cursor-pointer group transition-colors';
                
                let isSel = false;
                if (this.type === 'select') isSel = (o === this.val);
                else isSel = selectedArray.includes(o);
                
                if (this.type === 'multi_select') {
                    const chk = document.createElement('input');
                    chk.type = 'checkbox';
                    chk.checked = isSel;
                    chk.className = 'mr-2 rounded text-emerald-500 border-gray-300 w-3.5 h-3.5 focus:ring-emerald-500 pointer-events-none';
                    rowDiv.appendChild(chk);
                }

                const labelSpan = document.createElement('span');
                labelSpan.className = 'flex-1 truncate ' + (isSel ? 'font-bold text-emerald-600' : 'text-gray-700 pointer-events-none');
                labelSpan.innerText = o;

                rowDiv.onclick = async () => {
                    if (this.type === 'select') {
                        if (o !== this.val) {
                            const success = await this.updateDb(o);
                            if (success) {
                                this.contentDiv.innerHTML = `<span class="bg-blue-100 text-blue-700 text-[11px] px-2 py-0.5 rounded-full border border-blue-200">${o}</span>`;
                            }
                        }
                        popover.remove();
                        document.removeEventListener('mousedown', closePopover);
                    } else {
                        if (selectedArray.includes(o)) {
                            selectedArray = selectedArray.filter(v => v !== o);
                        } else {
                            selectedArray.push(o);
                        }
                        
                        const newCsv = selectedArray.join(',');
                        const success = await this.updateDb(newCsv);
                        if (success) {
                            renderOptions();
                            let html = '<div class="flex flex-wrap gap-1">';
                            selectedArray.forEach(s => {
                                html += `<span class="bg-purple-100 text-purple-700 text-[11px] px-2 py-px rounded border border-purple-200">${s}</span>`;
                            });
                            html += '</div>';
                            this.contentDiv.innerHTML = selectedArray.length ? html : '<span class="text-gray-300 italic opacity-50">#</span>';
                        }
                    }
                };

                rowDiv.appendChild(labelSpan);
                popover.appendChild(rowDiv);
            });

            // Giao diện Add Options (Mock)
            const hint = document.createElement('div');
            hint.className = 'p-1 text-center text-xs text-gray-400 mt-2 border-t border-gray-100';
            hint.innerText = __( 'Edit Options in Column configuration', 'skaaa-data-pro' );
            popover.appendChild(hint);
        };

        renderOptions();
        this.cell.appendChild(popover);
    }
}
