/**
 * Ska Button Block
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
            text, 
            url, 
            target, 
            tagName = 'a', 
            hasIcon = false,
            iconName = '',
            iconPosition = 'left',
            iconClasses = '',
            actionType = 'link',
            tailwindClasses = '', 
            className = '',
            dynamic = { text_source: 'static', text_key: '', url_source: 'static', url_key: '' }, 
            logic = { enabled: false, key: '', operator: '==', value: '' },
            customStyle
        } = attributes;

        const { useEffect } = wp.element;

        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
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

        const fullClasses = (tailwindClasses + ' ' + className).trim();

        const blockProps = useBlockProps({
            className: `ska-button-block wp-block-ska-builder-button ${fullClasses}`.trim(),
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

        // Always force Tag based on actionType to prevent invalid HTML (e.g. <button href="...">)
        const Tag = actionType === 'link' ? 'a' : 'button';

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Button Settings', 'ska-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('Action Type', 'ska-builder-core')}
                            value={actionType}
                            options={[
                                { label: 'Link URL', value: 'link' },
                                { label: 'Trigger Popup', value: 'popup' },
                                { label: 'Submit Form', value: 'submit' }
                            ]}
                            onChange={(val) => {
                                const newTagName = val === 'link' ? 'a' : 'button';
                                setAttributes({ actionType: val, tagName: newTagName });
                            }}
                        />
                        {actionType === 'link' && (
                            <>
                                <TextControl
                                    label={__('Link URL', 'ska-builder-core')}
                                    value={url}
                                    onChange={(val) => setAttributes({ url: val })}
                                />
                                <SelectControl
                                    label={__('Target', 'ska-builder-core')}
                                    value={target}
                                    options={[
                                        { label: 'Same Window (_self)', value: '_self' },
                                        { label: 'New Window (_blank)', value: '_blank' }
                                    ]}
                                    onChange={(val) => setAttributes({ target: val })}
                                />
                            </>
                        )}
                    </PanelBody>

                    <PanelBody title={__('Icon Settings', 'ska-builder-core')} initialOpen={false}>
                        <ToggleControl
                            label={__('Enable Icon', 'ska-builder-core')}
                            checked={hasIcon}
                            onChange={(val) => setAttributes({ hasIcon: val })}
                        />
                        {hasIcon && (
                            <>
                                <TextControl
                                    label={__('Icon Name (Material Symbols)', 'ska-builder-core')}
                                    value={iconName}
                                    onChange={(val) => setAttributes({ iconName: val })}
                                    help="e.g. play_arrow, star, shopping_cart"
                                />
                                <TextControl
                                    label={__('Icon Custom Classes', 'ska-builder-core')}
                                    value={iconClasses}
                                    onChange={(val) => setAttributes({ iconClasses: val })}
                                    help="Tailwind classes: text-4xl text-primary animate-pulse"
                                />
                                <SelectControl
                                    label={__('Icon Position', 'ska-builder-core')}
                                    value={iconPosition}
                                    options={[
                                        { label: 'Left', value: 'left' },
                                        { label: 'Right', value: 'right' }
                                    ]}
                                    onChange={(val) => setAttributes({ iconPosition: val })}
                                />
                            </>
                        )}
                    </PanelBody>

                    <PanelBody title={__('Dynamic Data', 'ska-builder-core')} initialOpen={false}>
                        <h4 style={{ margin: '0 0 8px 0', fontSize: '13px' }}>{__('Text Source', 'ska-builder-core')}</h4>
                        <SelectControl
                            value={dynamic.text_source}
                            options={[
                                { label: 'Static Text', value: 'static' },
                                { label: 'Post Title', value: 'post_title' },
                                { label: 'Post Meta', value: 'meta' },
                                { label: 'SCF Field', value: 'scf' }
                            ]}
                            onChange={(val) => updateDynamic('text_source', val)}
                        />
                        {(dynamic.text_source === 'meta' || dynamic.text_source === 'scf') && (
                            <TextControl
                                label={__('Field Key', 'ska-builder-core')}
                                value={dynamic.text_key}
                                onChange={(val) => updateDynamic('text_key', val)}
                            />
                        )}

                        <hr style={{ margin: '16px 0' }} />
                        
                        <h4 style={{ margin: '0 0 8px 0', fontSize: '13px' }}>{__('URL Source', 'ska-builder-core')}</h4>
                        <SelectControl
                            value={dynamic.url_source}
                            options={[
                                { label: 'Static URL', value: 'static' },
                                { label: 'Post Permalink', value: 'post_url' },
                                { label: 'Post Meta', value: 'meta' },
                                { label: 'SCF Field', value: 'scf' }
                            ]}
                            onChange={(val) => updateDynamic('url_source', val)}
                        />
                        {(dynamic.url_source === 'meta' || dynamic.url_source === 'scf') && (
                            <TextControl
                                label={__('Field Key', 'ska-builder-core')}
                                value={dynamic.url_key}
                                onChange={(val) => updateDynamic('url_key', val)}
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

                <Tag 
                    {...blockProps}
                    href={Tag === 'a' ? url : undefined}
                    target={Tag === 'a' ? target : undefined}
                    type={actionType === 'submit' ? 'submit' : undefined}
                    onClick={(e) => e.preventDefault()}
                >
                    {hasIcon && iconPosition === 'left' && (
                        <span aria-hidden="true" className={`material-symbols-outlined ${iconClasses}`.trim()}>{iconName}</span>
                    )}
                    {dynamic.text_source === 'static' ? (
                        <RichText
                            tagName="span"
                            value={text}
                            onChange={(val) => setAttributes({ text: val })}
                            placeholder={__('Button text...', 'ska-builder-core')}
                            allowedFormats={[]}
                        />
                    ) : (
                        <span className="ska-dynamic-placeholder" style={{ opacity: 0.7 }}>
                            [{dynamic.text_source === 'meta' ? `Meta: ${dynamic.text_key}` : dynamic.text_source}]
                        </span>
                    )}
                    {hasIcon && iconPosition === 'right' && (
                        <span aria-hidden="true" className={`material-symbols-outlined ${iconClasses}`.trim()}>{iconName}</span>
                    )}
                </Tag>
            </>
        );
    },
    save: () => {
        return null; // Dynamic block rendered in PHP
    },
});
