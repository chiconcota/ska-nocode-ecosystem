/**
 * Ska List Item Block
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
        const { tailwindClasses = '', className = '', logic, customStyle } = attributes;

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

        // Helper to parse inline style string to React object
        const parseStyle = (styleString) => {
            if (!styleString) return {};
            return styleString.split(';').reduce((acc, style) => {
                const parts = style.split(/:(.+)/);
                if (parts.length >= 2) {
                    const prop = parts[0].trim();
                    const value = parts[1].trim();
                    if (prop && value) {
                        const camelProp = prop.replace(/-([a-z])/g, g => g[1].toUpperCase());
                        acc[camelProp] = value;
                    }
                }
                return acc;
            }, {});
        };

        const blockProps = useBlockProps({
            className: `ska-list-item-block wp-block-ska-builder-list-item ${tailwindClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        const ALLOWED_BLOCKS = [
            'ska-builder/text', 
            'ska-builder/image', 
            'ska-builder/button', 
            'ska-builder/icon', 
            'ska-builder/container', 
            'ska-builder/list',
            'ska-builder/html2tailwind',
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
                        customStyle={customStyle}
                        setCustomStyle={(val) => setAttributes({ customStyle: val })}
                    />

                    <PanelBody title={__('Logic (Visibility)', 'ska-builder-core')} initialOpen={false}>
                        <ToggleControl
                            label={__('Enable Logic', 'ska-builder-core')}
                            checked={logic?.enabled}
                            onChange={(value) => setAttributes({ logic: { ...logic, enabled: value } })}
                        />
                    </PanelBody>
                </InspectorControls>

                <li {...innerBlocksProps} />
            </>
        );
    },
    save: () => {
        return <InnerBlocks.Content />;
    },
});
