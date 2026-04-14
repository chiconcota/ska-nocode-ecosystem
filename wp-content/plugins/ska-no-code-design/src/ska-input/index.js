import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const {
            fieldName,
            fieldId,
            inputType,
            fieldValue,
            placeholder,
            isChecked,
            isRequired,
            tailwindClasses = '',
            className = ''
        } = attributes;

        const { useEffect } = wp.element;

        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
            }
        }, []);

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
            className: `ska-input-block wp-block-ska-builder-input base-transition ${fullClasses}`.trim()});

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Input Settings', 'ska-builder-core')} initialOpen={true}>
                        <TextControl
                            label={__('Field Name (Data Key)', 'ska-builder-core')}
                            value={fieldName}
                            onChange={(val) => setAttributes({ fieldName: val })}
                            help="Tên biến chứa dữ liệu (Vd: first_name, email)."
                        />
                        <TextControl
                            label={__('Field ID (Optional)', 'ska-builder-core')}
                            value={fieldId}
                            onChange={(val) => setAttributes({ fieldId: val })}
                            help="Cần thiết cho Radio/Checkbox để nối với thẻ Label gốc (for=id)."
                        />
                        <SelectControl
                            label={__('Input Type', 'ska-builder-core')}
                            value={inputType}
                            options={[
                                { label: 'Text', value: 'text' },
                                { label: 'Email', value: 'email' },
                                { label: 'Number', value: 'number' },
                                { label: 'Password', value: 'password' },
                                { label: 'Checkbox', value: 'checkbox' },
                                { label: 'Radio', value: 'radio' },
                                { label: 'Date', value: 'date' },
                                { label: 'Time', value: 'time' },
                                { label: 'File', value: 'file' },
                                { label: 'Hidden', value: 'hidden' }
                            ]}
                            onChange={(val) => setAttributes({ inputType: val })}
                        />
                        {['text', 'email', 'number', 'password'].includes(inputType) && (
                            <TextControl
                                label={__('Placeholder', 'ska-builder-core')}
                                value={placeholder}
                                onChange={(val) => setAttributes({ placeholder: val })}
                            />
                        )}
                        <TextControl
                            label={__('Static Value (Optional)', 'ska-builder-core')}
                            value={fieldValue}
                            onChange={(val) => setAttributes({ fieldValue: val })}
                            help="Giá trị gán sẵn cho Input hoặc Value cố định cho Checkbox/Radio"
                        />
                        {['checkbox', 'radio'].includes(inputType) && (
                            <ToggleControl
                                label={__('Checked Default', 'ska-builder-core')}
                                checked={isChecked}
                                onChange={(val) => setAttributes({ isChecked: val })}
                            />
                        )}
                        <ToggleControl
                            label={__('Required', 'ska-builder-core')}
                            checked={isRequired}
                            onChange={(val) => setAttributes({ isRequired: val })}
                        />
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                    />
                </InspectorControls>

                {inputType !== 'hidden' ? (
                    <input 
                        {...blockProps} 
                        type={inputType} 
                        name={fieldName} 
                        id={fieldId || undefined}
                        placeholder={placeholder} 
                        value={fieldValue}
                        checked={['checkbox', 'radio'].includes(inputType) ? isChecked : undefined}
                        required={isRequired}
                        readOnly={true}
                    />
                ) : (
                    <div {...blockProps} style={{ opacity: 0.5, border: '1px dashed #ccc', padding: '5px' }}>
                        [Hidden Input: {fieldName}]
                    </div>
                )}
            </>
        );
    },
    save: () => {
        return null;
    }});

