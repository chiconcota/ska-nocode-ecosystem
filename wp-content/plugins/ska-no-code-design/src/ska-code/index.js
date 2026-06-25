/**
 * Ska Code Block Entry Point
 */
import { registerBlockType } from '@wordpress/blocks';
import { code } from '@wordpress/icons';
import metadata from './block.json';
import Edit from './edit.js';

registerBlockType(metadata.name, {
    icon: code,
    edit: Edit,
    save: () => {
        return null; // Dynamic block rendered on server-side
    }
});
