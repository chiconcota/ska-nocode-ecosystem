/**
 * Ska List Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { listType = 'ul', tailwindClasses = '', className = '', logic, customStyle } = attributes;

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
            className: `ska-list-block wp-block-ska-builder-list ${tailwindClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        const ALLOWED_BLOCKS = [ 'ska-builder/list-item' ];
        const ListTag = listType || 'ul';

        // useInnerBlocksProps: merge inner blocks directly into wrapper
        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            allowedBlocks: ALLOWED_BLOCKS,
            renderAppender: InnerBlocks.ButtonBlockAppender,
            template: [
                ['ska-builder/list-item', {}],
                ['ska-builder/list-item', {}]
            ]
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('List Settings', 'ska-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('List Type', 'ska-builder-core')}
                            value={listType}
                            options={[
                                { label: 'Unordered List (ul)', value: 'ul' },
                                { label: 'Ordered List (ol)', value: 'ol' }
                            ]}
                            onChange={(value) => setAttributes({ listType: value })}
                        />
                    </PanelBody>

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

                <ListTag {...innerBlocksProps} />
            </>
        );
    },
    save: () => {
        return <InnerBlocks.Content />;
    },
});
