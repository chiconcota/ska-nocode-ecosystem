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
        <div className="skaaa-options-builder" style={{ marginBottom: '16px' }}>
            <div style={{ marginBottom: '10px' }}>
                <strong style={{ fontSize: '12px', textTransform: 'uppercase', color: '#1e1e1e' }}>{__( '{__(\'Configuration Options\', \'skaaaaa-builder-core\')}', 'skaaa-no-code-design' )}</strong>
            </div>
            
            {options.length === 0 && (
                <div style={{ padding: '16px', textAlign: 'center', background: '#f8f9fa', border: '1px dashed #ccc', borderRadius: '4px', marginBottom: '8px' }}>
                    <p style={{ color: '#666', fontSize: '13px', margin: 0 }}>{__( 'There are no options yet.', 'skaaa-no-code-design' )}</p>
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
                    {showRaw ? __( 'Hide Text mode (Dev Mode)', 'skaaa-no-code-design' ) : __( 'Show Text mode (Dev Mode)', 'skaaa-no-code-design' )}
                </Button>
                
                {showRaw && (
                    <div style={{ marginTop: '12px' }}>
                        <TextareaControl
                            value={value}
                            onChange={onChange}
                            rows={5}
                            style={{ fontSize: '12px', fontFamily: 'monospace' }}
                            help={__( 'Syntax: Label:value (1 line per option)', 'skaaa-no-code-design' )}
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
            skaaaDynamicBinding,
            isRequired,
            isMultiple,
            displayStyle,
            tailwindClasses = '',
            className = ''
        } = attributes;

        const { useEffect, useMemo } = wp.element;

        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
            }
        }, []);

        let currentTable = '';
        let currentColumn = '';
        if (skaaaDynamicBinding && typeof skaaaDynamicBinding === 'string') {
            const match = skaaaDynamicBinding.match(/{{#foreach\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]*)\s*}}/);
            if (match) {
                currentTable = match[1];
                currentColumn = match[2] || '';
            }
        }

        const tableOptions = useMemo(() => {
            const opts = [{ label: __( '-- Select Data Table --', 'skaaa-no-code-design' ), value: '' }];
            if (window.skaaaDataDictionary) {
                Object.keys(window.skaaaDataDictionary).forEach(key => {
                    const table = window.skaaaDataDictionary[key];
                    const val = key.replace(/^wp_/, '');
                    const labelName = table.__table_info ? table.__table_info.name : val;
                    opts.push({ label: `${labelName} (${val})`, value: val });
                });
            }
            return opts;
        }, []);

        const columnOptions = useMemo(() => {
            const opts = [{ label: __( '-- Select Column --', 'skaaa-no-code-design' ), value: '' }];
            if (currentTable && window.skaaaDataDictionary) {
                let tableDict = window.skaaaDataDictionary[currentTable] || window.skaaaDataDictionary['wp_' + currentTable];
                if (tableDict) {
                    Object.keys(tableDict).forEach(colKey => {
                        if (colKey !== '__table_info') {
                            const col = tableDict[colKey];
                            // Chỉ hiển thị các cột có chứa danh sách options (loại select/radio/checkbox)
                            if (col.options || col.type === 'select') {
                                opts.push({ label: (col.label || colKey) + ` (${colKey})`, value: colKey });
                            }
                        }
                    });
                }
            }
            return opts;
        }, [currentTable]);

        const updateDynamicBinding = (newTable, newColumn) => {
            if (newTable && newColumn) {
                setAttributes({ 
                    skaaaDynamicBinding: `{{#foreach ${newTable}.${newColumn}}}`,
                    fieldName: newColumn
                });
            } else if (newTable) {
                setAttributes({ skaaaDynamicBinding: `{{#foreach ${newTable}.}}` });
            } else {
                setAttributes({ 
                    skaaaDynamicBinding: '',
                    fieldName: ''
                });
            }
        };

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
            className: `skaaa-select-block wp-block-skaaaaa-builder-select base-transition ${fullClasses}`.trim()});

        let optionsList = [];
        if (skaaaDynamicBinding && currentTable && currentColumn) {
            let tableDict = window.skaaaDataDictionary && (window.skaaaDataDictionary[currentTable] || window.skaaaDataDictionary['wp_' + currentTable]);
            if (tableDict && tableDict[currentColumn] && tableDict[currentColumn].options) {
                optionsList = tableDict[currentColumn].options.split(',').filter(opt => opt.trim() !== '').map(opt => ({
                    label: opt.trim(),
                    value: opt.trim()
                }));
            } else {
                optionsList = [
                    { label: '[Dynamic Data (Frontend)...]', value: '' }
                ];
            }
        } else {
            optionsList = (optionsText || '').split('\n').filter(line => line.trim() !== '').map(line => {
                const parts = line.split(':');
                return {
                    label: parts[0] ? parts[0].trim() : '',
                    value: parts.length > 1 ? parts[1].trim() : (parts[0] ? parts[0].trim() : '')
                };
            });
        }

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__(__( 'Data Source (Dynamic)', 'skaaa-no-code-design' ), 'skaaaaa-builder-core')} initialOpen={true}>
                        {!window.skaaaDataDictionary ? (
                            <div style={{ padding: '12px', background: '#fffbeb', color: '#b45309', fontSize: '13px', borderRadius: '4px', border: '1px solid #fcd34d' }}>
                                <strong>{__( 'Skaaa Data Pro has not been activated yet', 'skaaa-no-code-design' )}</strong><br/>
                                Tính năng <i>{__( 'Dynamic Data Source', 'skaaa-no-code-design' )}</i> yêu cầu phải có hệ sinh thái <b>Skaaa Data Pro</b>. Vui lòng cài đặt và kích hoạt plugin này để trải nghiệm tính năng Zero N+1 Query.
                            </div>
                        ) : (
                            <>
                                <ToggleControl
                                    label={__(__( 'Dynamic data connection', 'skaaa-no-code-design' ), 'skaaaaa-builder-core')}
                                    checked={!!skaaaDynamicBinding}
                                    onChange={(val) => {
                                        if (!val) {
                                            setAttributes({ skaaaDynamicBinding: '' });
                                        } else {
                                            setAttributes({ skaaaDynamicBinding: 'true' });
                                        }
                                    }}
                                    help={<>Lấy danh sách option tự động từ Database của <strong style={{color: '#dc2626', textTransform: 'uppercase'}}>Skaaa Data Pro</strong></>}
                                />
                                {!!skaaaDynamicBinding && (
                                    <>
                                        <SelectControl
                                            label={__(__( 'Data Table', 'skaaa-no-code-design' ), 'skaaaaa-builder-core')}
                                            value={currentTable}
                                            options={tableOptions}
                                            onChange={(val) => updateDynamicBinding(val, '')}
                                        />
                                        {currentTable && (
                                            <SelectControl
                                                label={__(__( 'Data Column (Option)', 'skaaa-no-code-design' ), 'skaaaaa-builder-core')}
                                                value={currentColumn}
                                                options={columnOptions}
                                                onChange={(val) => updateDynamicBinding(currentTable, val)}
                                            />
                                        )}
                                        {skaaaDynamicBinding && currentTable && currentColumn && (
                                            <div style={{ padding: '8px', background: '#ecfdf5', border: '1px solid #10b981', borderRadius: '4px', fontSize: '12px', marginTop: '10px' }}>
                                                <strong>Binding:</strong> <code>{skaaaDynamicBinding}</code>
                                                <p style={{ margin: '4px 0 0', color: '#047857' }}>{__( 'Options data will be loaded automatically when loading the Frontend page (Zero N+1).', 'skaaa-no-code-design' )}</p>
                                            </div>
                                        )}
                                    </>
                                )}
                            </>
                        )}
                    </PanelBody>

                    <PanelBody title={__('Choice Settings', 'skaaaaa-builder-core')} initialOpen={false}>
                        <TextControl
                            label={__('Field Name (Data Key)', 'skaaaaa-builder-core')}
                            value={fieldName}
                            onChange={(val) => setAttributes({ fieldName: val })}
                        />
                        <SelectControl
                            label={__('Display Style', 'skaaaaa-builder-core')}
                            value={displayStyle}
                            options={[
                                { label: __( 'Dropdown (Default)', 'skaaa-no-code-design' ), value: 'dropdown' },
                                { label: __( 'Radio List (Choose 1)', 'skaaa-no-code-design' ), value: 'radio' },
                                { label: __( 'Checkbox List (Select multiple)', 'skaaa-no-code-design' ), value: 'checkbox' }
                            ]}
                            onChange={(val) => {
                                setAttributes({ displayStyle: val });
                                if (val === 'checkbox') setAttributes({ isMultiple: true });
                                if (val === 'radio') setAttributes({ isMultiple: false });
                            }}
                        />
                        {displayStyle === 'dropdown' && (
                            <ToggleControl
                                label={__(__( 'Allows multiple selection (Multi-select)', 'skaaa-no-code-design' ), 'skaaaaa-builder-core')}
                                checked={isMultiple}
                                onChange={(val) => setAttributes({ isMultiple: val })}
                            />
                        )}
                        
                        {!skaaaDynamicBinding ? (
                            <OptionsBuilderControl
                                value={optionsText}
                                onChange={(val) => setAttributes({ optionsText: val })}
                            />
                        ) : (
                            <div style={{ padding: '12px', background: '#f1f5f9', color: '#64748b', fontSize: '13px', borderRadius: '4px', fontStyle: 'italic', marginBottom: '15px' }}>
                                Tùy chọn tĩnh đã bị vô hiệu hóa do bạn đang sử dụng Nguồn dữ liệu động {currentTable ? `từ bảng ` : ''}<strong>{currentTable}</strong>.
                            </div>
                        )}
                        
                        <ToggleControl
                            label={__('Required', 'skaaaaa-builder-core')}
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

