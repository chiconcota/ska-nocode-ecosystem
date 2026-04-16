import { createHigherOrderComponent } from '@wordpress/compose';
import { PanelBody, TextControl, SelectControl, TextareaControl, Button, Icon } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';

// Import our molecule variations so they register in the Gutenberg inserter
import './molecules-variations';

/**
 * 1. Add `htmlAttributes` to all Ska blocks schema
 */
function addHtmlAttributesToSchema(settings, name) {
    if (name && name.startsWith('ska-builder/')) {
        settings.attributes = {
            ...settings.attributes,
            htmlAttributes: {
                type: 'array',
                default: []
            }
        };
    }
    return settings;
}
addFilter('blocks.registerBlockType', 'ska-builder/extensions/html-attributes-schema', addHtmlAttributesToSchema);

/**
 * 2. HOC for the Key-Value Panel in InspectorControls
 */
const withHTMLAttributesPanel = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (!props.name || !props.name.startsWith('ska-builder/')) {
            return <BlockEdit {...props} />;
        }

        const { attributes, setAttributes } = props;
        const htmlAttributes = attributes.htmlAttributes || [];

        const updateAttribute = (index, key, value) => {
            const newAttributes = [...htmlAttributes];
            newAttributes[index] = { ...newAttributes[index], [key]: value };
            setAttributes({ htmlAttributes: newAttributes });
        };

        const addAttribute = () => {
            setAttributes({ htmlAttributes: [...htmlAttributes, { key: '', value: '' }] });
        };

        const removeAttribute = (index) => {
            const newAttributes = htmlAttributes.filter((_, i) => i !== index);
            setAttributes({ htmlAttributes: newAttributes });
        };

        // Detect if any key is Alpine.js
        const hasAlpine = htmlAttributes.some(attr => attr.key.startsWith('x-') || attr.key.startsWith('@'));

        return (
            <>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody 
                        title={(
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                {__('Ska HTML Attributes', 'ska-no-code-design')}
                                {hasAlpine && (
                                    <span 
                                        className="material-symbols-outlined" 
                                        title={__('Alpine.js Active', 'ska-no-code-design')}
                                        style={{ 
                                            fontSize: '14px', 
                                            color: '#3490dc', // Alpine logo blue
                                            textShadow: '0 0 5px rgba(52, 144, 220, 0.4)',
                                            lineHeight: '1',
                                            fontFamily: 'Material Symbols Outlined'
                                        }}
                                    >
                                        bolt
                                    </span>
                                )}
                            </div>
                        )} 
                        initialOpen={false}
                    >
                        <p style={{ fontSize: '12px', color: '#666', marginBottom: '16px' }}>
                            {__('Quản lý thuộc tính HTML tùy chỉnh hoặc cấu hình JS như Alpine.js (x-data, x-show).', 'ska-no-code-design')}
                        </p>
                        {htmlAttributes.map((attr, index) => (
                            <div key={index} style={{ border: '1px solid #e2e8f0', padding: '12px', marginBottom: '12px', borderRadius: '6px', background: '#f8fafc' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                    <span style={{ fontSize: '11px', fontWeight: 'bold', color: '#64748b', textTransform: 'uppercase' }}>Property #{index + 1}</span>
                                    <Button 
                                        isDestructive 
                                        isSmall 
                                        onClick={() => removeAttribute(index)}
                                        style={{ minWidth: 'auto', padding: '0 4px', color: '#ef4444' }}
                                        title={__('Xóa', 'ska-no-code-design')}
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    </Button>
                                </div>

                                {(() => {
                                    const isFormContext = props.name === 'ska-builder/container' && attributes.tagName === 'form';
                                    const isInputComponent = ['ska-builder/input', 'ska-builder/select'].includes(props.name);

                                    let COMMON_KEYS = [
                                        { label: '-- Chọn một thuộc tính --', value: '' },
                                        { label: '🎨 Style (Inline CSS)', value: 'style' },
                                        { label: '🏷️ ID định danh', value: 'id' },
                                    ];

                                    // Form-specific properties
                                    if (isFormContext) {
                                        COMMON_KEYS.push(
                                            { label: '🔗 Form Action', value: 'action' },
                                            { label: '📮 Form Method', value: 'method' },
                                            { label: '📤 @submit.prevent (Alpine Form)', value: '@submit.prevent' }
                                        );
                                    }

                                    // General Alpine & DOM attributes
                                    COMMON_KEYS.push(
                                        { label: '⚡ x-data (AlpineJS State)', value: 'x-data' },
                                        { label: '👁️ x-show (Alpine Ẩn/Hiện)', value: 'x-show' },
                                        { label: '✨ x-transition:enter', value: 'x-transition:enter' },
                                        { label: '✨ x-transition:enter-start', value: 'x-transition:enter-start' },
                                        { label: '✨ x-transition:enter-end', value: 'x-transition:enter-end' },
                                        { label: '✨ x-transition:leave', value: 'x-transition:leave' },
                                        { label: '✨ x-transition:leave-start', value: 'x-transition:leave-start' },
                                        { label: '✨ x-transition:leave-end', value: 'x-transition:leave-end' },
                                        { label: '🖱️ @click (Alpine Click)', value: '@click' },
                                        { label: '🛑 @click.prevent (Chặn cuộn/load trang)', value: '@click.prevent' }
                                    );

                                    // Data Binding (Hide on Input/Select as they have their own settings)
                                    if (!isInputComponent) {
                                        COMMON_KEYS.push(
                                            { label: '🔄 x-model (Alpine Data Bind)', value: 'x-model' },
                                            { label: '📝 x-text (Alpine Text)', value: 'x-text' }
                                        );
                                    }

                                    COMMON_KEYS.push({ label: '✏️ --- Tùy chỉnh (Gõ tay) ---', value: 'custom' });

                                    const isStandard = COMMON_KEYS.some(k => k.value === (attr.key || '')) && (attr.key || '') !== 'custom';
                                    const selectValue = isStandard ? (attr.key || '') : 'custom';
                                    const isCustom = selectValue === 'custom';

                                    return (
                                        <>
                                            <SelectControl
                                                options={COMMON_KEYS}
                                                value={selectValue}
                                                onChange={(val) => {
                                                    if (val === 'custom') {
                                                        // Tạm lưu cờ 'custom_mode' hoặc chuỗi rỗng để TextControl rỗng
                                                        updateAttribute(index, 'key', 'custom_mode_on');
                                                    } else {
                                                        updateAttribute(index, 'key', val);
                                                    }
                                                }}
                                                style={{ marginBottom: isCustom ? '4px' : '8px' }}
                                            />
                                            {isCustom && (
                                                <TextControl
                                                    placeholder="Nhập tên thuộc tính (VD: aria-label)"
                                                    value={attr.key === 'custom_mode_on' ? '' : (attr.key || '')}
                                                    onChange={(val) => updateAttribute(index, 'key', val)}
                                                    autoComplete="off"
                                                    style={{ marginBottom: '8px' }}
                                                />
                                            )}
                                        </>
                                    );
                                })()}
                                
                                {(() => {
                                    // === SMART VALUE INPUT ===
                                    // Khi key là x-show → hiện dropdown preset thay vì textarea
                                    const XSHOW_PRESETS = [
                                        { label: '-- Chọn điều kiện --', value: '' },
                                        { label: '✅ Hiện khi Form gửi Thành công', value: "status === 'success'" },
                                        { label: '❌ Hiện khi Form gửi Lỗi', value: "status === 'error'" },
                                        { label: '⏳ Hiện khi Form đang Gửi', value: 'isSubmitting' },
                                        { label: '👻 Ẩn khi Form đang Gửi', value: '!isSubmitting' },
                                        { label: '✏️ Tùy chỉnh (Gõ tay)', value: '__custom__' },
                                    ];

                                    const isXShow = attr.key === 'x-show';
                                    const isPresetValue = isXShow && XSHOW_PRESETS.some(p => p.value === (attr.value || '')) && attr.value !== 'custom_mode_on' && attr.value !== '__custom__';

                                    if (isXShow) {
                                        return (
                                            <>
                                                <SelectControl
                                                    options={XSHOW_PRESETS}
                                                    value={isPresetValue ? (attr.value || '') : '__custom__'}
                                                    onChange={(val) => {
                                                        if (val === '__custom__') {
                                                            updateAttribute(index, 'value', 'custom_mode_on');
                                                        } else {
                                                            updateAttribute(index, 'value', val);
                                                        }
                                                    }}
                                                    style={{ marginBottom: '0' }}
                                                />
                                                {!isPresetValue && attr.value !== undefined && (
                                                    <TextareaControl
                                                        placeholder="Nhập biểu thức Alpine (VD: open === true)"
                                                        value={attr.value === 'custom_mode_on' ? '' : (attr.value || '')}
                                                        onChange={(val) => updateAttribute(index, 'value', val)}
                                                        autoComplete="off"
                                                        style={{ marginBottom: '0', fontSize: '13px', fontFamily: 'SFMono-Regular, Consolas, Liberation Mono, Menlo, monospace' }}
                                                        rows={2}
                                                    />
                                                )}
                                            </>
                                        );
                                    }

                                    return (
                                        <TextareaControl
                                            placeholder="Giá trị (VD: { open: false, items: [] })"
                                            value={attr.value || ''}
                                            onChange={(val) => updateAttribute(index, 'value', val)}
                                            autoComplete="off"
                                            style={{ marginBottom: '0', fontSize: '13px', fontFamily: 'SFMono-Regular, Consolas, Liberation Mono, Menlo, monospace' }}
                                            rows={3}
                                        />
                                    );
                                })()}
                            </div>
                        ))}
                        <Button isSecondary onClick={addAttribute} style={{ width: '100%', justifyContent: 'center', marginTop: '10px' }}>
                            {__('+ Thêm Thuộc Tính', 'ska-no-code-design')}
                        </Button>
                    </PanelBody>
                </InspectorControls>
            </>
        );
    };
}, 'withHTMLAttributesPanel');

addFilter('editor.BlockEdit', 'ska-builder/extensions/html-attributes-panel', withHTMLAttributesPanel);
