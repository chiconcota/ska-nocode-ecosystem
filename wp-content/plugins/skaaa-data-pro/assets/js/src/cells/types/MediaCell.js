import { __ } from '@wordpress/i18n';
import BaseCell from '../BaseCell.js';

export default class MediaCell extends BaseCell {
    edit() {
        if (typeof wp !== 'undefined' && wp.media) {
            let file_frame = wp.media({ 
                title: __( 'Select Image', 'skaaa-data-pro' ), 
                button: { text: __( 'Use This Photo', 'skaaa-data-pro' ) }, 
                multiple: false 
            });
            
            file_frame.on('select', async () => {
                const attachment = file_frame.state().get('selection').first().toJSON();
                const newVal = attachment.url;
                if (newVal === this.val) return;
                
                const success = await this.updateDb(newVal);
                if (success) {
                    this.contentDiv.innerHTML = `<img src="${newVal}" class="h-6 w-6 object-cover rounded border border-gray-200 inline-block mr-2"><span class="text-[11px] text-gray-400">Media</span>`;
                }
            });
            file_frame.open();
        } else {
            alert(__( 'Unable to load wp.media library', 'skaaa-data-pro' ));
        }
    }
}
