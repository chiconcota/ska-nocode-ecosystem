import BaseCell from '../BaseCell.js';

export default class BooleanCell extends BaseCell {
    async edit() {
        // Toggle giá trị 0/1
        const newVal = (this.val == '1') ? '0' : '1';
        
        const success = await this.updateDb(newVal);
        
        if (success) {
            const isChecked = (newVal == '1');
            const bgClass = isChecked ? 'bg-emerald-500' : 'bg-gray-300';
            const translateClass = isChecked ? 'translate-x-3.5' : 'translate-x-0.5';
            
            this.contentDiv.innerHTML = `<div class="w-8 h-4 flex items-center rounded-full transition-colors pointer-events-none ${bgClass}"><div class="w-3.5 h-3.5 bg-white rounded-full shadow-sm transform transition-transform ${translateClass}"></div></div>`;
        }
    }
}
