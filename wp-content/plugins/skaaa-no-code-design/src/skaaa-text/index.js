/**
 * Skaaa Text Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';
import { SkaaaLinkControl } from '../components/SkaaaLinkControl.js';

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
            link = { url: '', target: '_self', dynamic: { source: 'static', key: '' } } 
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

        const blockProps = useBlockProps({
            className: `skaaa-text-block wp-block-skaaaaa-builder-text ${tailwindClasses}`.trim()});

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
                    <PanelBody title={__('Element Settings', 'skaaaaa-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('Tag Name', 'skaaaaa-builder-core')}
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

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                    />

                    <PanelBody title={__('Link Settings', 'skaaaaa-builder-core')} initialOpen={false}>
                        <SkaaaLinkControl
                            link={link}
                            onChange={(newLink) => setAttributes({ link: newLink })}
                        />
                    </PanelBody>

                    </InspectorControls>

                <Tag {...blockProps}>
                    {dynamic.source === 'text' ? (
                        <RichText
                            tagName="span"
                            value={content}
                            onChange={(val) => setAttributes({ content: val })}
                            placeholder={__('Write text here...', 'skaaaaa-builder-core')}
                        />
                    ) : (
                        <span className="skaaa-dynamic-placeholder" style={{ background: '#eee', padding: '2px 6px', borderRadius: '4px', opacity: 0.7 }}>
                            [{dynamic.source === 'meta' ? `Meta: ${dynamic.key}` : dynamic.source}]
                        </span>
                    )}
                </Tag>
            </>
        );
    },
    save: () => {
        return null; // Dynamic block
    }});
