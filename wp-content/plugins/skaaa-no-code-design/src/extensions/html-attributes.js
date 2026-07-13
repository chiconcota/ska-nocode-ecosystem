import { createHigherOrderComponent } from '@wordpress/compose';
import { PanelBody, TextControl, SelectControl, TextareaControl, Button, Icon } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';

// Import our molecule variations so they register in the Gutenberg inserter
import './molecules-variations';

// Tích hợp hệ thống Skaaapine (Alpine in Gutenberg)
import './skaaapine/withSkaaapine';

// Tích hợp hệ thống Skaaa Symbols (Save as Organism)
import './skaaa-symbols';

// Kéo thả Skaaa Symbols ra Frontend (Thư viện)
import './skaaa-inserter';

// Skaaa Dynamic Link for RichText
import './skaaa-link-format';

/**
 * 1. Add `htmlAttributes` to all Skaaa blocks schema
 */
function addHtmlAttributesToSchema(settings, name) {
    if (name && name.startsWith('skaaaaa-builder/')) {
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
addFilter('blocks.registerBlockType', 'skaaaaa-builder/extensions/html-attributes-schema', addHtmlAttributesToSchema);

/**
 * 2. HOC for the Key-Value Panel in InspectorControls
 */
const withHTMLAttributesPanel = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (!props.name || !props.name.startsWith('skaaaaa-builder/')) {
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
                                {__('Skaaa HTML Attributes', 'skaaa-no-code-design')}
                                {hasAlpine && (
                                    <span 
                                        className="material-symbols-outlined" 
                                        title={__('Alpine.js Active', 'skaaa-no-code-design')}
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
                            {__(__( 'Manage custom HTML attributes or JS configuration like Alpine.js (x-data, x-show).', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
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
                                        title={__(__( 'Erase', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    </Button>
                                </div>

                                {(() => {
                                    const isFormContext = props.name === 'skaaaaa-builder/container' && attributes.tagName === 'form';
                                    const isInputComponent = ['skaaaaa-builder/input', 'skaaaaa-builder/select'].includes(props.name);

                                    let COMMON_KEYS = [
                                        { label: __( '-- Select an attribute --', 'skaaa-no-code-design' ), value: '' },
                                    ];

                                    // 🌟 NHÓM 1: HIỆU ỨNG & TƯƠNG TÁC (Chạy trong Preview Mode)
                                    COMMON_KEYS.push(
                                        { label: __( '--- ✨ EFFECTS & ANIMATION ---', 'skaaa-no-code-design' ), value: 'opt-group-1', disabled: true },
                                        { label: __( '👁️ x-show (Hide/Show using Script)', 'skaaa-no-code-design' ), value: 'x-show' },
                                        { label: __( '🖱️ @click (Mouse Click)', 'skaaa-no-code-design' ), value: '@click' },
                                        { label: __( '👆 @mouseenter (Mouse over)', 'skaaa-no-code-design' ), value: '@mouseenter' },
                                        { label: __( '👇 @mouseleave (Mouse out)', 'skaaa-no-code-design' ), value: '@mouseleave' },
                                        { label: __( '🛑 @click.prevent (Prevent Submit/Load)', 'skaaa-no-code-design' ), value: '@click.prevent' },
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
                                            { label: __( '--- 🔢 DATA & LOGIC ---', 'skaaa-no-code-design' ), value: 'opt-group-2', disabled: true },
                                            { label: __( '⚡ x-data (Initialize State Component)', 'skaaa-no-code-design' ), value: 'x-data' },
                                            { label: __( '🔄 x-model (2-way binding)', 'skaaa-no-code-design' ), value: 'x-model' },
                                            { label: __( '📝 x-text (Add Dynamic Text)', 'skaaa-no-code-design' ), value: 'x-text' },
                                            { label: __( '🌐 x-html (Dynamic HTML binding)', 'skaaa-no-code-design' ), value: 'x-html' }
                                        );
                                    }

                                    // Form-specific properties
                                    if (isFormContext) {
                                        COMMON_KEYS.push(
                                            { label: __( '--- 📮 CUSTOMIZE FORM ---', 'skaaa-no-code-design' ), value: 'opt-group-form', disabled: true },
                                            { label: __( '🔗 action (Send link)', 'skaaa-no-code-design' ), value: 'action' },
                                            { label: '📮 method (POST/GET)', value: 'method' },
                                            { label: __( '📤 @submit.prevent (Form submission key)', 'skaaa-no-code-design' ), value: '@submit.prevent' }
                                        );
                                    }

                                    // General DOM attributes
                                    COMMON_KEYS.push(
                                        { label: __( '--- 🔧 BASIC HTML ---', 'skaaa-no-code-design' ), value: 'opt-group-3', disabled: true },
                                        { label: __( '🎨 style (inline CSS)', 'skaaa-no-code-design' ), value: 'style' },
                                        { label: __( '🏷️ id (Unique identifier)', 'skaaa-no-code-design' ), value: 'id' },
                                        { label: __( '--- ✏️ CUSTOMIZE ---', 'skaaa-no-code-design' ), value: 'opt-group-4', disabled: true },
                                        { label: __( 'Customizable auto-typing (Example: aria-label)', 'skaaa-no-code-design' ), value: 'custom' }
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
                                                    placeholder={__( 'Enter the attribute name (eg aria-label)', 'skaaa-no-code-design' )}
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
                                        { label: __( '-- Select condition --', 'skaaa-no-code-design' ), value: '' },
                                        { label: __( '✅ Show when Form sent successfully', 'skaaa-no-code-design' ), value: "status === 'success'" },
                                        { label: __( '❌ Shows when Form submit Error', 'skaaa-no-code-design' ), value: "status === 'error'" },
                                        { label: __( '⏳ Shows when Form is Submitting', 'skaaa-no-code-design' ), value: 'isSubmitting' },
                                        { label: __( '👻 Hide when Form is Submitting', 'skaaa-no-code-design' ), value: '!isSubmitting' },
                                        { label: __( '✏️ Customize (Hand Typing)', 'skaaa-no-code-design' ), value: '__custom__' },
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
                                                        placeholder={__( 'Enter an Alpine expression (eg: open === true)', 'skaaa-no-code-design' )}
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
                                            placeholder={__( 'Value (eg: { open: false, items: [] })', 'skaaa-no-code-design' )}
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
                            {__(__( '+ Add Attributes', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                        </Button>
                    </PanelBody>
                </InspectorControls>
            </>
        );
    };
}, 'withHTMLAttributesPanel');

addFilter('editor.BlockEdit', 'skaaaaa-builder/extensions/html-attributes-panel', withHTMLAttributesPanel);
