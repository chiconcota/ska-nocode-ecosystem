/**
 * Skaaa Button Block
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
            text, 
            url, 
            target, 
            tagName = 'a', 
            hasIcon = false,
            iconName = '',
            iconPosition = 'left',
            iconClasses = '',
            actionType = 'link',
            fieldName = '',
            fieldValue = '',
            tailwindClasses = '', 
            className = '',
            dynamic = { text_source: 'static', text_key: '', url_source: 'static', url_key: '' }, 
            logic = { enabled: false, key: '', operator: '==', value: '' },
            link = { url: '', target: '_self', dynamic: { source: 'static', key: '' } },
            htmlAttributes = []
        } = attributes;

        const { useEffect } = wp.element;

        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
            } else if (!tailwindClasses && !className) {
                // Apply default design token if no classes exist (newly inserted block)
                if (typeof window !== 'undefined' && window.skaaaDesignTokens?.components?.button?.primary) {
                    setAttributes({ tailwindClasses: window.skaaaDesignTokens.components.button.primary });
                }
            }

            // Auto-migrate old url to link object
            if (url && url !== '#' && !link.url) {
                setAttributes({
                    link: {
                        url: url,
                        target: target || '_self',
                        dynamic: {
                            source: dynamic.url_source || 'static',
                            key: dynamic.url_key || ''
                        }
                    }
                });
            }
        }, []);
        
        const fullClasses = (tailwindClasses + ' ' + className).trim();

        const blockProps = useBlockProps({
            className: `skaaa-button-block wp-block-skaaaaa-builder-button ${fullClasses}`.trim()});

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
                    <PanelBody title={__('Button Settings', 'skaaaaa-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('Action Type', 'skaaaaa-builder-core')}
                            value={actionType}
                            options={[
                                { label: 'Link URL', value: 'link' },
                                { label: 'Submit Form', value: 'submit' },
                                { label: 'Trigger Logic Workflow', value: 'logic_api' },
                                { label: 'Toggle Dark Mode', value: 'theme_toggle' }
                            ]}
                            onChange={(val) => {
                                const newTagName = val === 'link' ? 'a' : 'button';
                                let newHtmlAttributes = [...htmlAttributes];
                                
                                if (val === 'theme_toggle') {
                                    const hasThemeToggle = newHtmlAttributes.some(attr => attr.key === '@click.prevent' && attr.value === '$store.skaaaTheme.toggle()');
                                    if (!hasThemeToggle) {
                                        newHtmlAttributes.push({ key: '@click.prevent', value: '$store.skaaaTheme.toggle()' });
                                    }
                                    const hasXData = newHtmlAttributes.some(attr => attr.key === 'x-data');
                                    if (!hasXData) {
                                        newHtmlAttributes.push({ key: 'x-data', value: '' });
                                    }
                                } else if (actionType === 'theme_toggle') {
                                    newHtmlAttributes = newHtmlAttributes.filter(attr => !((attr.key === '@click.prevent' || attr.key === '@click') && attr.value === '$store.skaaaTheme.toggle()'));
                                    // Only remove x-data if its value is empty (auto-injected one)
                                    newHtmlAttributes = newHtmlAttributes.filter(attr => !(attr.key === 'x-data' && attr.value === ''));
                                }

                                setAttributes({ 
                                    actionType: val, 
                                    tagName: newTagName,
                                    htmlAttributes: newHtmlAttributes 
                                });
                            }}
                        />
                        {actionType === 'link' && (
                            <div style={{ marginTop: '15px' }}>
                                <SkaaaLinkControl
                                    link={link}
                                    onChange={(newLink) => setAttributes({ link: newLink })}
                                />
                            </div>
                        )}
                        {actionType === 'submit' && (
                            <>
                                <TextControl
                                    label={__('Field Name (Data Key)', 'skaaaaa-builder-core')}
                                    value={fieldName}
                                    onChange={(val) => setAttributes({ fieldName: val })}
                                    help={__( 'Identifier of the button (Example: action_type)', 'skaaa-no-code-design' )}
                                />
                                <TextControl
                                    label={__('Field Value', 'skaaaaa-builder-core')}
                                    value={fieldValue}
                                    onChange={(val) => setAttributes({ fieldValue: val })}
                                    help={__( 'Value sent to Server (For example: publish, draft)', 'skaaa-no-code-design' )}
                                />
                            </>
                        )}
                        {actionType === 'logic_api' && (
                            <TextControl
                                label={__('Workflow ID', 'skaaaaa-builder-core')}
                                value={fieldName}
                                onChange={(val) => setAttributes({ fieldName: val })}
                                help={__( 'Enter the Workflow ID to run (Example: api_test)', 'skaaa-no-code-design' )}
                            />
                        )}
                    </PanelBody>

                    <PanelBody title={__('Icon Settings', 'skaaaaa-builder-core')} initialOpen={false}>
                        <ToggleControl
                            label={__('Enable Icon', 'skaaaaa-builder-core')}
                            checked={hasIcon}
                            onChange={(val) => setAttributes({ hasIcon: val })}
                        />
                        {hasIcon && (
                            <>
                                <TextControl
                                    label={__('Icon Name (Material Symbols)', 'skaaaaa-builder-core')}
                                    value={iconName}
                                    onChange={(val) => setAttributes({ iconName: val })}
                                    help="e.g. play_arrow, star, shopping_cart"
                                />
                                <TextControl
                                    label={__('Icon Custom Classes', 'skaaaaa-builder-core')}
                                    value={iconClasses}
                                    onChange={(val) => setAttributes({ iconClasses: val })}
                                    help="Tailwind classes: text-4xl text-primary animate-pulse"
                                />
                                <SelectControl
                                    label={__('Icon Position', 'skaaaaa-builder-core')}
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

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                    />

                    </InspectorControls>

                <Tag 
                    {...blockProps}
                    href={Tag === 'a' ? (link.dynamic.source === 'static' ? link.url : '#dynamic-link') : undefined}
                    target={Tag === 'a' ? link.target : undefined}
                    type={actionType === 'submit' ? 'submit' : undefined}
                    name={actionType === 'submit' && fieldName ? fieldName : undefined}
                    value={actionType === 'submit' && fieldValue ? fieldValue : undefined}
                    onClick={(e) => e.preventDefault()}
                >
                    {hasIcon && iconPosition === 'left' && (
                        <span className={`material-symbols-outlined ${iconClasses}`.trim()}>{iconName}</span>
                    )}
                    {dynamic.text_source === 'static' ? (
                        <RichText
                            tagName="span"
                            value={text}
                            onChange={(val) => setAttributes({ text: val })}
                            placeholder={__('Button text...', 'skaaaaa-builder-core')}
                            allowedFormats={[]}
                        />
                    ) : (
                        <span className="skaaa-dynamic-placeholder" style={{ opacity: 0.7 }}>
                            [{dynamic.text_source === 'meta' ? `Meta: ${dynamic.text_key}` : dynamic.text_source}]
                        </span>
                    )}
                    {hasIcon && iconPosition === 'right' && (
                        <span className={`material-symbols-outlined ${iconClasses}`.trim()}>{iconName}</span>
                    )}
                </Tag>
            </>
        );
    },
    save: () => {
        return null; // Dynamic block rendered in PHP
    }});

