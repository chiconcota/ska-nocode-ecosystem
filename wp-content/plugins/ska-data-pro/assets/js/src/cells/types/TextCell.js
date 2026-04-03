import BaseCell from '../BaseCell.js';

export default class TextCell extends BaseCell {
    edit() {
        if (this.cell.querySelector('input')) return;
        
        this.contentDiv.innerHTML = '';
        const input = document.createElement('input');
        input.type = 'text';
        input.value = this.val;
        input.className = 'w-full h-full px-2 py-1 outline-none border border-emerald-500 rounded bg-emerald-50 text-emerald-800 text-sm shadow-inner';
        
        this.contentDiv.appendChild(input);
        input.focus();
        
        const save = async () => {
            const newVal = input.value.trim();
            if (newVal !== this.val) {
                const success = await this.updateDb(newVal);
                if (success) {
                    this.contentDiv.innerHTML = newVal || '<span class="text-gray-300 italic opacity-50">#</span>';
                } else {
                    this.contentDiv.innerHTML = this.val || '<span class="text-gray-300 italic opacity-50">#</span>';
                }
            } else {
                this.contentDiv.innerHTML = this.val || '<span class="text-gray-300 italic opacity-50">#</span>';
            }
        };

        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
    }
}
