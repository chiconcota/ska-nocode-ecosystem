/**
 * Ska Text Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { 
            content, 
            tagName = 'p', 
            tailwindClasses = '', 
            className = '',
            dynamic = { source: 'text', key: '' }, 
            logic = { enabled: false, key: '', operator: '==', value: '' }, 
            customStyle 
        } = attributes;

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
            className: `ska-text-block wp-block-ska-builder-text ${tailwindClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        // Helper to update nested attributes
        const updateDynamic = (key, val) => {
            const newDynamic = Object.assign({}, dynamic, { [key]: val });
            setAttributes({ dynamic: newDynamic });
        };
        const updateLogic = (key, val) => {
            const newLogic = Object.assign({}, logic, { [key]: val });
            setAttributes({ logic: newLogic });
        };

        const Tag = tagName;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Element Settings', 'ska-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('Tag Name', 'ska-builder-core')}
                            value={tagName}
                            options={[
                                { label: 'Paragraph (p)', value: 'p' },
                                { label: 'Heading 1 (h1)', value: 'h1' },
                                { label: 'Heading 2 (h2)', value: 'h2' },
                                { label: 'Heading 3 (h3)', value: 'h3' },
                                { label: 'Div', value: 'div' },
                                { label: 'Span', value: 'span' }
                            ]}
                            onChange={(val) => setAttributes({ tagName: val })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Dynamic Data', 'ska-builder-core')} initialOpen={false}>
                        <SelectControl
                            label={__('Source', 'ska-builder-core')}
                            value={dynamic.source}
                            options={[
                                { label: 'Static Text', value: 'text' },
                                { label: 'Post Title', value: 'post_title' },
                                { label: 'Post Content', value: 'post_content' },
                                { label: 'Post Meta (Custom Field)', value: 'meta' },
                                { label: 'URL Parameter', value: 'url_param' }
                            ]}
                            onChange={(val) => updateDynamic('source', val)}
                        />
                        {(dynamic.source === 'meta' || dynamic.source === 'url_param') && (
                            <TextControl
                                label={__('Key / Param Name', 'ska-builder-core')}
                                value={dynamic.key}
                                onChange={(val) => updateDynamic('key', val)}
                            />
                        )}
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
                            checked={logic.enabled}
                            onChange={(val) => updateLogic('enabled', val)}
                        />
                    </PanelBody>
                </InspectorControls>

                <Tag {...blockProps}>
                    {dynamic.source === 'text' ? (
                        <RichText
                            tagName="span"
                            value={content}
                            onChange={(val) => setAttributes({ content: val })}
                            placeholder={__('Write text here...', 'ska-builder-core')}
                        />
                    ) : (
                        <span className="ska-dynamic-placeholder" style={{ background: '#eee', padding: '2px 6px', borderRadius: '4px', opacity: 0.7 }}>
                            [{dynamic.source === 'meta' ? `Meta: ${dynamic.key}` : dynamic.source}]
                        </span>
                    )}
                </Tag>
            </>
        );
    },
    save: () => {
        return null; // Dynamic block
    },
});