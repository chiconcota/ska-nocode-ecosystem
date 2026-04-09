import { attachModalHandlers } from './modules/modals.js';
import { attachRowEvents } from './modules/rows.js';
import { attachSchemaEvents } from './modules/schema.js';
import { attachAppEvents } from './modules/apps.js';
import CellRegistry from './cells/Registry.js';

// Init
document.addEventListener('DOMContentLoaded', () => {
    try {
        attachModalHandlers();
        attachRowEvents();
        attachSchemaEvents();
        attachAppEvents();
        CellRegistry.attachInlineEditEvent();

        // Bật Sự kiện cho Cascading Rollups nếu đã load ở php (Nó được gọi trong file admin-cascade.js hoặc script tag)
        // Hiện tại ta bind tự động qua onchange bằng modals handler.

    } catch (error) {
        console.error('Ska DataGrid Initialization Error:', error);
    }
});
