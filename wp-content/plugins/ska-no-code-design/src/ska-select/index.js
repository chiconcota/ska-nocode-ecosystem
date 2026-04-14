import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, Button, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';

const OptionsBuilderControl = ({ value, onChange }) => {
    const { useState } = wp.element;

    const getOptions = (text) => {
        if (!text) return [];
        return text.split('\n').filter(line => line.trim() !== '').map(line => {
            const parts = line.split(':');
            return {
                label: parts[0] ? parts[0].trim() : '',
                val: parts.length > 1 ? parts[1].trim() : (parts[0] ? parts[0].trim() : '')
            };
        });
    };

    const options = getOptions(value);

    const updateOptions = (newOptions) => {
        const newText = newOptions.map(opt => `${opt.label}:${opt.val}`).join('\n');
        onChange(newText);
    };

    const addOption = () => {
        updateOptions([...options, { label: 'New Option', val: 'new_option' }]);
    };

    const removeOption = (index) => {
        const newOptions = [...options];
        newOptions.splice(index, 1);
        updateOptions(newOptions);
    };

    const updateOption = (index, key, newValue) => {
        const newOptions = [...options];
        newOptions[index][key] = newValue;
        updateOptions(newOptions);
    };

    const [showRaw, setShowRaw] = useState(false);

    return (
        <div className="ska-options-builder" style={{ marginBottom: '16px' }}>
            <div style={{ marginBottom: '10px' }}>
                <strong style={{ fontSize: '12px', textTransform: 'uppercase', color: '#1e1e1e' }}>{__('Cấu hình Tùy chọn', 'ska-builder-core')}</strong>
            </div>
            
            {options.length === 0 && (
                <div style={{ padding: '16px', textAlign: 'center', background: '#f8f9fa', border: '1px dashed #ccc', borderRadius: '4px', marginBottom: '8px' }}>
                    <p style={{ color: '#666', fontSize: '13px', margin: 0 }}>Chưa có tùy chọn nào.</p>
                </div>
            )}

            {options.map((opt, idx) => (
                <div key={idx} style={{ display: 'flex', gap: '8px', marginBottom: '8px', alignItems: 'center', background: '#f8f9fa', padding: '8px', borderRadius: '6px', border: '1px solid #e2e8f0' }}>
                    <div style={{ flex: 1 }}>
                        {idx === 0 && <span style={{ fontSize: '11px', color: '#666', marginBottom: '4px', display: 'block' }}>Label</span>}
                        <TextControl
                            value={opt.label}
                            onChange={(val) => updateOption(idx, 'label', val)}
                            __nextHasNoMarginBottom
                        />
                    </div>
                    <div style={{ flex: 1 }}>
                        {idx === 0 && <span style={{ fontSize: '11px', color: '#666', marginBottom: '4px', display: 'block' }}>Value</span>}
                        <TextControl
                            value={opt.val}
                            onChange={(val) => updateOption(idx, 'val', val)}
                            __nextHasNoMarginBottom
                        />
                    </div>
                    <div style={{ marginTop: idx === 0 ? '16px' : '0' }}>
                        <Button
                            isDestructive
                            isSmall
                            icon="trash"
                            onClick={() => removeOption(idx)}
                            style={{ padding: '0 4px', minWidth: '24px' }}
                        />
                    </div>
                </div>
            ))}
            
            <Button
                variant="secondary"
                onClick={addOption}
                style={{ width: '100%', justifyContent: 'center', marginTop: '8px', borderStyle: 'dashed' }}
            >
                + Thêm tùy chọn mới
            </Button>
            
            <div style={{ marginTop: '16px', borderTop: '1px solid #e2e8f0', paddingTop: '12px' }}>
                <Button 
                    isLink 
                    onClick={() => setShowRaw(!showRaw)}
                    style={{ fontSize: '12px', textDecoration: 'none' }}
                >
                    {showRaw ? "Ẩn chế độ Text (Dev Mode)" : "Hiện chế độ Text (Dev Mode)"}
                </Button>
                
                {showRaw && (
                    <div style={{ marginTop: '12px' }}>
                        <TextareaControl
                            value={value}
                            onChange={onChange}
                            rows={5}
                            style={{ fontSize: '12px', fontFamily: 'monospace' }}
                            help="Cú pháp: Label:value (Mỗi tùy chọn 1 dòng)"
                            __nextHasNoMarginBottom
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const {
            fieldName,
            optionsText,
            isRequired,
            isMultiple,
            displayStyle,
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
            className: `ska-select-block wp-block-ska-builder-select base-transition ${fullClasses}`.trim()});

        const optionsList = optionsText.split('\n').filter(line => line.trim() !== '').map(line => {
            const parts = line.split(':');
            return {
                label: parts[0].trim(),
                value: parts.length > 1 ? parts[1].trim() : parts[0].trim()
            };
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Choice Settings', 'ska-builder-core')} initialOpen={true}>
                        <TextControl
                            label={__('Field Name (Data Key)', 'ska-builder-core')}
                            value={fieldName}
                            onChange={(val) => setAttributes({ fieldName: val })}
                        />
                        <SelectControl
                            label={__('Display Style', 'ska-builder-core')}
                            value={displayStyle}
                            options={[
                                { label: 'Dropdown (Mặc định)', value: 'dropdown' },
                                { label: 'Radio List (Chọn 1)', value: 'radio' },
                                { label: 'Checkbox List (Chọn nhiều)', value: 'checkbox' }
                            ]}
                            onChange={(val) => {
                                setAttributes({ displayStyle: val });
                                if (val === 'checkbox') setAttributes({ isMultiple: true });
                                if (val === 'radio') setAttributes({ isMultiple: false });
                            }}
                        />
                        {displayStyle === 'dropdown' && (
                            <ToggleControl
                                label={__('Cho phép chọn nhiều (Multi-select)', 'ska-builder-core')}
                                checked={isMultiple}
                                onChange={(val) => setAttributes({ isMultiple: val })}
                            />
                        )}
                        <OptionsBuilderControl
                            value={optionsText}
                            onChange={(val) => setAttributes({ optionsText: val })}
                        />
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

                {displayStyle === 'dropdown' ? (
                    <select {...blockProps} multiple={isMultiple} style={{ ...blockProps.style, minHeight: '38px', minWidth: '120px', pointerEvents: 'none' }}>
                        {optionsList.map((opt, idx) => (
                            <option key={idx} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                ) : (
                    <div {...blockProps} style={{ ...blockProps.style, display: 'flex', flexDirection: 'column', gap: '8px' }}>
                        {optionsList.map((opt, idx) => (
                            <label key={idx} style={{ display: 'flex', alignItems: 'center', gap: '6px', opacity: 0.7 }}>
                                <input 
                                    type={displayStyle === 'radio' ? 'radio' : 'checkbox'} 
                                    disabled={true} 
                                /> 
                                <span>{opt.label}</span>
                            </label>
                        ))}
                    </div>
                )}
            </>
        );
    },
    save: () => {
        return null;
    }});

