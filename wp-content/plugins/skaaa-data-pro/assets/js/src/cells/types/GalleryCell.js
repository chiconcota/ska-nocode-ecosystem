import { __ } from '@wordpress/i18n';
import BaseCell from '../BaseCell.js';

export default class GalleryCell extends BaseCell {
    edit() {
        if (document.getElementById('skaaa-datagrid-popover')) {
            document.getElementById('skaaa-datagrid-popover').remove();
        }

        const popover = document.createElement('div');
        popover.id = 'skaaa-datagrid-popover';
        popover.className = 'absolute top-full left-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-[100] p-2 text-sm font-normal text-gray-800 flex flex-col gap-2';
        
        let selectedArray = (this.val || '').split(',').map(s=>s.trim()).filter(s=>s!=='');
        let isMediaOpen = false;

        const closePopover = (e) => {
            if (isMediaOpen || (e.target.closest && (e.target.closest('.media-modal') || e.target.closest('.media-modal-backdrop')))) return;
            if(!popover.contains(e.target) && !this.cell.contains(e.target)) {
                popover.remove();
                document.removeEventListener('mousedown', closePopover);
            }
        };
        setTimeout(() => document.addEventListener('mousedown', closePopover), 10);

        const renderGallery = () => {
            popover.innerHTML = '';
            
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1 skaaa-datagrid-scroll';
            
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
                grid.innerText = __( 'Drum. ', 'skaaa-data-pro' );
            }
            
            popover.appendChild(grid);
            
            const btnWrap = document.createElement('div');
            btnWrap.className = 'flex gap-2 mt-1';
            
            const addBtn = document.createElement('button');
            addBtn.className = 'flex-1 bg-white border border-emerald-500 text-emerald-600 hover:bg-emerald-50 text-xs py-1.5 rounded font-bold transition-colors flex justify-center items-center shadow-sm';
            addBtn.innerHTML = __( '<span class=\"dashicons dashicons-plus\" style=\"font-size: 14px; margin-top: -1px; margin-right: 2px;\"></span> Add Photo', 'skaaa-data-pro' );
            addBtn.onclick = (e) => {
                e.stopPropagation();
                if (typeof wp !== 'undefined' && wp.media) {
                    let file_frame = wp.media({ title: __( 'Select Photo', 'skaaa-data-pro' ), button: { text: __( 'More', 'skaaa-data-pro' ) }, multiple: false });
                    
                    file_frame.on('open', () => { isMediaOpen = true; });
                    file_frame.on('close', () => { setTimeout(() => { isMediaOpen = false; }, 200); });
                    
                    file_frame.on('select', () => {
                        const attachment = file_frame.state().get('selection').first().toJSON();
                        selectedArray.push(attachment.url);
                        renderGallery();
                    });
                    file_frame.open();
                } else {
                    alert(__( 'Wp.media has not been loaded yet', 'skaaa-data-pro' ));
                }
            };
            
            const applyBtn = document.createElement('button');
            applyBtn.className = 'flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs py-1.5 rounded font-bold transition-colors flex justify-center items-center shadow-sm';
            applyBtn.innerHTML = __( '<span class=\"dashicons dashicons-saved\" style=\"font-size: 14px; margin-top: -1px; margin-right: 2px;\"></span> Stamp', 'skaaa-data-pro' );
            applyBtn.onclick = async (e) => {
                e.stopPropagation();
                const newCsv = selectedArray.join(', ');
                if (newCsv !== this.val) {
                    const success = await this.updateDb(newCsv);
                    if (success) {
                        const urls = newCsv.split(',').map(s=>s.trim()).filter(s=>s!=='');
                        if (urls.length === 0) {
                            this.contentDiv.innerHTML = '<span class="text-gray-300 italic opacity-50">#</span>';
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
                            this.contentDiv.innerHTML = html;
                        }
                        popover.remove();
                        document.removeEventListener('mousedown', closePopover);
                    }
                } else {
                    popover.remove();
                    document.removeEventListener('mousedown', closePopover);
                }
            };
            
            btnWrap.appendChild(addBtn);
            btnWrap.appendChild(applyBtn);
            popover.appendChild(btnWrap);
        };

        renderGallery();
        this.cell.appendChild(popover);
    }
}
