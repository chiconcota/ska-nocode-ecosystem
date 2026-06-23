/**
 * Ska Code Block Entry Point
 */
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit.js';

registerBlockType(metadata.name, {
    edit: Edit,
    save: () => {
        return null; // Dynamic block rendered on server-side
    }
});
