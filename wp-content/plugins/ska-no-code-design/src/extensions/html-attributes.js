import { createHigherOrderComponent } from '@wordpress/compose';
import { PanelBody, TextControl, SelectControl, TextareaControl, Button, Icon } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';

// Import our molecule variations so they register in the Gutenberg inserter
import './molecules-variations';

// Tích hợp hệ thống Skapine (Alpine in Gutenberg)
import './skapine/withSkapine';

// Tích hợp hệ thống Ska Symbols (Save as Organism)
import './ska-symbols';

// Kéo thả Ska Symbols ra Frontend (Thư viện)
import './ska-inserter';

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
                                    ];

                                    // 🌟 NHÓM 1: HIỆU ỨNG & TƯƠNG TÁC (Chạy trong Preview Mode)
                                    COMMON_KEYS.push(
                                        { label: '--- ✨ HIỆU ỨNG & ANIMATION ---', value: 'opt-group-1', disabled: true },
                                        { label: '👁️ x-show (Ẩn/Hiện bằng Script)', value: 'x-show' },
                                        { label: '🖱️ @click (Click Chuột)', value: '@click' },
                                        { label: '👆 @mouseenter (Rê chuột vào)', value: '@mouseenter' },
                                        { label: '👇 @mouseleave (Rê chuột ra)', value: '@mouseleave' },
                                        { label: '🛑 @click.prevent (Chống Submit/Load)', value: '@click.prevent' },
                                        { label: '✨ x-transition:enter', value: 'x-transition:enter' },
                                        { label: '✨ x-transition:enter-start', value: 'x-transition:enter-start' },
                                        { label: '✨ x-transition:enter-end', value: 'x-transition:enter-end' },
                                        { label: '✨ x-transition:leave', value: 'x-transition:leave' },
                                        { label: '✨ x-transition:leave-start', value: 'x-transition:leave-start' },
                                        { label: '✨ x-transition:leave-end', value: 'x-transition:leave-end' }
                                    );

                                    // 🌟 NHÓM 2: DỮ LIỆU & LOGIC (Không ảnh hưởng Preview)
                                    if (!isInputComponent) {
                                        COMMON_KEYS.push(
                                            { label: '--- 🔢 DỮ LIỆU & LOGIC ---', value: 'opt-group-2', disabled: true },
                                            { label: '⚡ x-data (Khởi tạo State Component)', value: 'x-data' },
                                            { label: '🔄 x-model (Ràng buộc 2 chiều)', value: 'x-model' },
                                            { label: '📝 x-text (Gắn Text động)', value: 'x-text' },
                                            { label: '🌐 x-html (Gắn HTML động)', value: 'x-html' }
                                        );
                                    }

                                    // Form-specific properties
                                    if (isFormContext) {
                                        COMMON_KEYS.push(
                                            { label: '--- 📮 TÙY CHỈNH FORM ---', value: 'opt-group-form', disabled: true },
                                            { label: '🔗 action (Đường dẫn gửi)', value: 'action' },
                                            { label: '📮 method (POST/GET)', value: 'method' },
                                            { label: '📤 @submit.prevent (Khóa gửi Form)', value: '@submit.prevent' }
                                        );
                                    }

                                    // General DOM attributes
                                    COMMON_KEYS.push(
                                        { label: '--- 🔧 HTML CƠ BẢN ---', value: 'opt-group-3', disabled: true },
                                        { label: '🎨 style (CSS nội tuyến)', value: 'style' },
                                        { label: '🏷️ id (Định danh riêng)', value: 'id' },
                                        { label: '--- ✏️ TUỲ CHỈNH ---', value: 'opt-group-4', disabled: true },
                                        { label: 'Tùy chỉnh tự gõ (Ví dụ: aria-label)', value: 'custom' }
                                    );

                                    const isStandard = COMMON_KEYS.some(k => k.value === (attr.key || '')) && (attr.key || '') !== 'custom';
                                    const selectValue = isStandard ? (attr.key || '') : 'custom';
                                    const isCustom = selectValue === 'custom';

                                    return (
                                        <>
                                            <SelectControl
                                                options={COMMON_KEYS}
                                                value={selectValue}
                                                onChange={(val) => {
                                                    if (val.startsWith('opt-group')) return;

                                                    if (val === 'custom') {
                                                        updateAttribute(index, 'key', 'custom_mode_on');
                                                    } else {
                                                        // Tự động điền dữ liệu mẫu nếu giá trị hiện đang trống
                                                        let defaultValue = attr.value || '';
                                                        if (defaultValue === '' || defaultValue === 'custom_mode_on') {
                                                            if (val === 'x-data') defaultValue = '{ open: false, hover: false }';
                                                            if (val.startsWith('@click')) defaultValue = 'open = !open';
                                                            if (val.startsWith('@mouseenter')) defaultValue = 'hover = true';
                                                            if (val.startsWith('@mouseleave')) defaultValue = 'hover = false';
                                                            if (val === 'x-show') defaultValue = 'open || hover';
                                                            if (val.includes('transition')) defaultValue = 'transition ease-in duration-300';
                                                        }

                                                        const newAttributes = [...htmlAttributes];
                                                        newAttributes[index] = { key: val, value: defaultValue };
                                                        setAttributes({ htmlAttributes: newAttributes });
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
