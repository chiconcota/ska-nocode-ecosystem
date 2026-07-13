/**
 * Skaaa List Item Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { tailwindClasses = '', className = '', logic } = attributes;

        const { useEffect } = wp.element;
        useEffect(() => {
            // One-time migration: merge className into tailwindClasses
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                const merged = `${tailwindClasses} ${className}`.trim();
                setAttributes({ tailwindClasses: merged, className: '' });
            }
        }, []);

        const blockProps = useBlockProps({
            className: `skaaa-list-item-block wp-block-skaaaaa-builder-list-item ${tailwindClasses}`.trim()});

        const ALLOWED_BLOCKS = [
            'skaaaaa-builder/text', 
            'skaaaaa-builder/image', 
            'skaaaaa-builder/button', 
            'skaaaaa-builder/icon', 
            'skaaaaa-builder/container', 
            'skaaaaa-builder/list',
            'skaaaaa-builder/html2tailwind',
            'core/paragraph', 
            'core/heading', 
            'core/image'
        ];

        // useInnerBlocksProps: merge inner blocks directly into wrapper
        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            allowedBlocks: ALLOWED_BLOCKS,
            renderAppender: InnerBlocks.ButtonBlockAppender
        });

        return (
            <>
                <InspectorControls>
                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                    />

                    </InspectorControls>

                <li {...innerBlocksProps} />
            </>
        );
    },
    save: () => {
        return <InnerBlocks.Content />;
    }});

