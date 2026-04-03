import BooleanCell from './types/BooleanCell.js';
import MediaCell from './types/MediaCell.js';
import GalleryCell from './types/GalleryCell.js';
import SelectCell from './types/SelectCell.js';
import TextCell from './types/TextCell.js';
import RelationCell from './types/RelationCell.js';
import BaseCell from './BaseCell.js';

export default class CellRegistry {
    static getStrategy(element) {
        const type = element.getAttribute('data-type');
        
        switch (type) {
            case 'boolean':
                return new BooleanCell(element);
            case 'media':
                return new MediaCell(element);
            case 'media_gallery':
                return new GalleryCell(element);
            case 'select':
            case 'multi_select':
                return new SelectCell(element);
            case 'relation':
                return new RelationCell(element);
            case 'rollup':
                // Read-only or special handled via popup
                return new BaseCell(element); // fallback (no edit action)
            default:
                // Text, Number, Date...
                return new TextCell(element);
        }
    }

    static attachInlineEditEvent() {
        document.querySelectorAll('.ska-editable-cell').forEach(cell => {
            if ( cell.getAttribute('data-col') === 'id' ) {
                cell.classList.remove('ska-editable-cell', 'cursor-text', 'hover:bg-gray-100/80');
                cell.title = "Khoá chính (ID) không thể sửa";
                cell.style.cursor = 'not-allowed';
                return;
            }

            cell.addEventListener('click', function(e) {
                // Đảm bảo không bấm đè khi đang mở popup Select
                if (e.target.closest('#ska-datagrid-popover')) return;

                const strategy = CellRegistry.getStrategy(this);
                if (strategy && typeof strategy.edit === 'function') {
                    strategy.edit();
                }
            });
        });
    }
}
