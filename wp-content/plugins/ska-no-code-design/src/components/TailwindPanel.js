import { PanelBody, TextControl, Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';
import { StylePopoverDrawer } from './StylePopoverDrawer';

export const TailwindPanel = ({ className, setClassName }) => {
    const [inputValue, setInputValue] = useState('');
    const [isSavingPreset, setIsSavingPreset] = useState(false);
    const [presetName, setPresetName] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [saveError, setSaveError] = useState('');
    const [categories, setCategories] = useState({
        spacing: [],
        typography: [],
        layout: [],
        styling: [],
        animation: [],
        advanced: []
    });

    // Phân loại logic
    const categorizeClasses = (classString) => {
        if (!classString) return { spacing: [], typography: [], layout: [], styling: [], animation: [], advanced: [] };

        const classArray = classString.split(' ').filter(c => c.trim() !== '');
        const newCategories = { spacing: [], typography: [], layout: [], styling: [], animation: [], advanced: [] };

        classArray.forEach(cls => {
            const trimmed = cls.trim();
            if (/^-?(m|p|gap|space)-?/.test(trimmed)) {
                newCategories.spacing.push(trimmed);
            } else if (/^(text|font|leading|tracking|italic|not-italic|uppercase|lowercase|capitalize|normal-case|line-through|underline|no-underline|overline|antialiased|whitespace|truncate|line-clamp)/.test(trimmed)) {
                newCategories.typography.push(trimmed);
            } else if (/^-?(w-|h-|size-|flex|grid|block|inline|hidden|justify|items|content|self|place|columns|aspect|min-|max-|absolute|relative|fixed|sticky|inset|top-|bottom-|left-|right-|z-|overflow|translate|rotate|skew)/.test(trimmed)) {
                newCategories.layout.push(trimmed);
            } else if (/^(bg-|border|rounded|shadow|opacity|ring|outline|fill|stroke|text-|from-|via-|to-)/.test(trimmed)) {
                // Lưu ý bg-/border-/etc có thể overlap nhẹ với spacing/text nhưng text- color thường vào text-
                if (trimmed.startsWith('text-') && !trimmed.startsWith('text-left') && !trimmed.startsWith('text-center') && !trimmed.startsWith('text-right') && !trimmed.startsWith('text-justify') && !trimmed.startsWith('text-lg') && !trimmed.startsWith('text-sm') && !trimmed.match(/^text-[0-9]+$/)) {
                    // Xử lý text-color vào styling hoặc typo tuỳ ý, tạm đưa text-color vào styling
                    newCategories.styling.push(trimmed);
                } else if (trimmed.startsWith('text-')) {
                    newCategories.typography.push(trimmed);
                } else {
                    newCategories.styling.push(trimmed);
                }
            } else if (/^(transition|animate|duration|ease|delay|scale|rotate|translate|skew|origin|hover:|focus:|active:|group-hover:)/.test(trimmed)) {
                newCategories.animation.push(trimmed);
            } else {
                newCategories.advanced.push(trimmed);
            }
        });

        return newCategories;
    };

    // Update categories when parent className changes
    useEffect(() => {
        setCategories(categorizeClasses(className));
    }, [className]);

    const handleKeyDown = (e) => {
        e.stopPropagation();
        if (e.key === 'Enter') {
            e.preventDefault();
            addClasses(inputValue);
        }
    };

    const lookupPreset = (name) => {
        const components = window.skaDesignTokens?.components;
        if (!components) return null;

        const searchName = name.toLowerCase();

        if (Array.isArray(components)) {
            const preset = components.find(c => c.name && c.name.toLowerCase() === searchName);
            return preset ? preset.value : null;
        } else if (typeof components === 'object') {
            const key = searchName.replace(/[-\s]+/g, '_');
            return components[key] || null;
        }

        return null;
    };

    const addClasses = (classesToAdd) => {
        if (!classesToAdd || !classesToAdd.trim()) return;

        // Check if the input is a preset name
        const presetClasses = lookupPreset(classesToAdd.trim());
        const finalClassesString = presetClasses ? presetClasses : classesToAdd;

        // Split by spaces or commas to handle multiple pasted classes
        const newClassesArray = finalClassesString.split(/[\s,]+/).filter(c => c.trim() !== '');

        // Get current classes array
        let currentClassesArray = className ? className.split(' ').filter(c => c.trim() !== '') : [];

        // Add new classes that don't already exist
        newClassesArray.forEach(cls => {
            if (!currentClassesArray.includes(cls)) {
                currentClassesArray.push(cls);
            }
        });

        // Pass all classes to parent — parent callback decides storage strategy
        const combined = currentClassesArray.join(' ');
        setClassName(combined, '');
        setInputValue('');
    };

    const handlePaste = (e) => {
        // TextControl natively handles basic pastes but we want to intercept if it's a preset
        let pastedText = '';
        if (e.clipboardData && e.clipboardData.getData) {
            pastedText = e.clipboardData.getData('text/plain') || e.clipboardData.getData('text');
        } else if (window.clipboardData && window.clipboardData.getData) {
            pastedText = window.clipboardData.getData('Text');
        }

        if (pastedText && lookupPreset(pastedText.trim())) {
            e.preventDefault();
            e.stopPropagation();
            addClasses(pastedText);
        }
    };

    const removeClass = (classToRemove) => {
        if (!className) return;
        const currentClassesArray = className.split(' ').filter(c => c.trim() !== '');
        const updatedClasses = currentClassesArray.filter(c => c !== classToRemove);
        const combined = updatedClasses.join(' ');
        setClassName(combined, '');
    };

    const copyAll = (e) => {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!className) return;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(className).then(() => {
                alert(__('Classes copied to clipboard!', 'ska-builder-core'));
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                fallbackCopy(className);
            });
        } else {
            fallbackCopy(className);
        }
    };

    const fallbackCopy = (text) => {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            alert(__('Classes copied to clipboard!', 'ska-builder-core'));
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
    };

    const handleSavePreset = async () => {
        if (!presetName.trim() || !className) return;
        
        setIsSaving(true);
        setSaveError('');

        const newPreset = {
            name: presetName.trim(),
            value: className
        };

        try {
            // Lấy dữ liệu mới nhất từ database để tránh ghi đè làm mất token cũ
            const getRes = await apiFetch({
                path: '/ska-design/v1/tokens',
                method: 'GET'
            });

            let currentTokens = {};
            if (getRes && getRes.success && getRes.data) {
                currentTokens = getRes.data;
            } else {
                currentTokens = window.skaDesignTokens || {};
            }

            let currentComponents = currentTokens.components || [];
            
            // Ensure currentComponents is an array
            if (!Array.isArray(currentComponents)) {
                currentComponents = Object.values(currentComponents);
            }
            
            // Prevent duplicate preset names
            if (currentComponents.some(c => c.name && c.name.toLowerCase() === newPreset.name.toLowerCase())) {
                setSaveError(__(__( 'This preset name already exists. ', 'ska-no-code-design' ), 'ska-builder-core'));
                setIsSaving(false);
                return;
            }

            const updatedData = {
                ...currentTokens,
                components: [...currentComponents, newPreset]
            };

            const postRes = await apiFetch({
                path: '/ska-design/v1/tokens',
                method: 'POST',
                data: updatedData
            });
            
            if (postRes && postRes.success) {
                window.skaDesignTokens = updatedData;
                setIsSavingPreset(false);
                setPresetName('');
                setSaveError('');
                alert(__(__( 'Preset saved successfully!', 'ska-no-code-design' ), 'ska-builder-core'));
            } else {
                setSaveError(postRes?.message || __(__( 'Error unable to save Preset.', 'ska-no-code-design' ), 'ska-builder-core'));
            }
        } catch (err) {
            console.error(err);
            setSaveError(__(__( 'Network error when saving Preset.', 'ska-no-code-design' ), 'ska-builder-core'));
        } finally {
            setIsSaving(false);
        }
    };

    const Tag = ({ value }) => (
        <span style={{
            display: 'inline-flex',
            alignItems: 'center',
            backgroundColor: '#f0f0f1',
            padding: '2px 8px',
            borderRadius: '4px',
            marginRight: '4px',
            marginBottom: '4px',
            fontSize: '12px',
            color: '#1e1e1e',
            border: '1px solid #ddd'
        }}>
            {value}
            <Button
                isSmall
                style={{ minWidth: 'auto', padding: '0', marginLeft: '4px', color: '#666' }}
                onClick={() => removeClass(value)}
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
            </Button>
        </span>
    );

    const CategorySection = ({ title, items }) => {
        if (!items || items.length === 0) return null;
        return (
            <div style={{ marginBottom: '16px' }}>
                <h4 style={{ margin: '0 0 8px 0', fontSize: '13px', color: '#1e1e1e', fontWeight: '500' }}>{title}</h4>
                <div style={{ display: 'flex', flexWrap: 'wrap' }}>
                    {items.map(cls => <Tag key={cls} value={cls} />)}
                </div>
            </div>
        );
    };

    const hasClasses = className && className.trim() !== '';

    return (
        <PanelBody
            title={(
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        {__('Ska Tailwind', 'ska-builder-core')}
                    </div>
                    <div 
                        style={{ display: 'flex', gap: '4px', marginRight: '8px' }}
                        onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                        }}
                        onPointerDown={(e) => e.stopPropagation()}
                    >
                        <StylePopoverDrawer className={className} setClassName={setClassName} />
                        
                        <span
                            role="button"
                            tabIndex={hasClasses ? 0 : -1}
                            aria-label={__('Copy Classes', 'ska-builder-core')}
                            aria-disabled={!hasClasses}
                            onPointerDown={copyAll}
                            onClick={(e) => { e.preventDefault(); e.stopPropagation(); }}
                            onKeyDown={(e) => {
                                if (hasClasses && (e.key === 'Enter' || e.key === ' ')) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    copyAll(e);
                                }
                            }}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: '28px',
                                height: '28px',
                                border: '1px solid #e2e8f0',
                                borderRadius: '2px',
                                cursor: hasClasses ? 'pointer' : 'not-allowed',
                                backgroundColor: '#fff',
                                color: hasClasses ? '#475569' : '#cbd5e1'
                            }}
                            title={__('Copy Classes', 'ska-builder-core')}
                        >
                            <span aria-hidden="true" className="material-symbols-outlined" style={{ fontSize: '16px' }}>content_copy</span>
                        </span>

                        <span
                            role="button"
                            tabIndex={hasClasses ? 0 : -1}
                            aria-label={__('Save Preset', 'ska-builder-core')}
                            aria-disabled={!hasClasses}
                            onPointerDown={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                if (hasClasses) setIsSavingPreset(true);
                            }}
                            onClick={(e) => { e.preventDefault(); e.stopPropagation(); }}
                            onKeyDown={(e) => {
                                if (hasClasses && (e.key === 'Enter' || e.key === ' ')) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    setIsSavingPreset(true);
                                }
                            }}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: '28px',
                                height: '28px',
                                border: '1px solid #e2e8f0',
                                borderRadius: '2px',
                                cursor: hasClasses ? 'pointer' : 'not-allowed',
                                backgroundColor: '#fff',
                                color: hasClasses ? '#475569' : '#cbd5e1'
                            }}
                            title={__('Save Preset', 'ska-builder-core')}
                        >
                            <span aria-hidden="true" className="material-symbols-outlined" style={{ fontSize: '16px' }}>save</span>
                        </span>
                    </div>
                </div>
            )}
            initialOpen={true}
            style={{ borderLeft: 'none' }}
        >
            <div style={{ marginBottom: '16px' }}>

                <div style={{ display: 'flex', gap: '8px' }}>
                    <TextControl
                        value={inputValue}
                        onChange={(val) => setInputValue(val)}
                        onKeyDown={handleKeyDown}
                        onPaste={handlePaste}
                        placeholder={__('Add classes or utility...', 'ska-builder-core')}
                        style={{ flexGrow: 1 }}
                    />
                    <Button
                        isSecondary
                        onClick={() => addClasses(inputValue)}
                        disabled={!inputValue.trim()}
                        style={{ marginRight: '4px' }}
                    >
                        +
                    </Button>
                    <Button
                        isDestructive
                        isTertiary
                        onClick={() => {
                            if (window.confirm(__('Are you sure you want to remove all classes?', 'ska-builder-core'))) {
                                setClassName('', '');
                            }
                        }}
                        disabled={!className || className.trim() === ''}
                    >
                        {__('Reset', 'ska-builder-core')}
                    </Button>
                </div>
            </div>



            <div className="ska-tailwind-categories">
                <CategorySection title="Ska Spacing" items={categories.spacing} />
                <CategorySection title="Ska Typography" items={categories.typography} />
                <CategorySection title="Ska Layout" items={categories.layout} />
                <CategorySection title="Ska Styling" items={categories.styling} />
                <CategorySection title="Transitions & Animation" items={categories.animation} />
                <CategorySection title="Advanced" items={categories.advanced} />

                {(!className || className.trim() === '') && (
                    <p style={{ color: '#757575', fontSize: '12px', fontStyle: 'italic' }}>
                        {__('No Tailwind classes applied yet.', 'ska-builder-core')}
                    </p>
                )}
            </div>

            {isSavingPreset && (
                <Modal
                    title={__(__( 'Save the new UI Preset', 'ska-no-code-design' ), 'ska-builder-core')}
                    onRequestClose={() => {
                        setIsSavingPreset(false);
                        setSaveError('');
                        setPresetName('');
                    }}
                    style={{ width: '400px' }}
                >
                    <div style={{ marginBottom: '16px' }}>
                        <p style={{ fontSize: '13px', color: '#64748b', marginBottom: '12px' }}>
                            {__(__( 'This preset will collect current classes and you can easily reuse them later. ', 'ska-no-code-design' ), 'ska-builder-core')}
                        </p>
                        
                        {saveError && (
                            <div style={{ padding: '8px 12px', backgroundColor: '#fef2f2', color: '#ef4444', border: '1px solid #fca5a5', borderRadius: '4px', marginBottom: '12px', fontSize: '13px' }}>
                                {saveError}
                            </div>
                        )}

                        <TextControl
                            label={__(__( 'Preset name', 'ska-no-code-design' ), 'ska-builder-core')}
                            value={presetName}
                            onChange={(val) => {
                                setPresetName(val);
                                setSaveError('');
                            }}
                            placeholder="e.g. Hero Button, Card Shadow..."
                            disabled={isSaving}
                        />

                        <div style={{ marginTop: '12px', padding: '12px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '4px' }}>
                            <span style={{ fontSize: '12px', fontWeight: '500', color: '#64748b', display: 'block', marginBottom: '4px' }}>Classes included:</span>
                            <code style={{ fontSize: '12px', color: '#0f172a', wordBreak: 'break-all' }}>{className}</code>
                        </div>
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                        <Button isSecondary onClick={() => setIsSavingPreset(false)} disabled={isSaving}>
                            {__(__( 'Cancel', 'ska-no-code-design' ), 'ska-builder-core')}
                        </Button>
                        <Button isPrimary onClick={handleSavePreset} disabled={!presetName.trim() || isSaving}>
                            {isSaving ? __(__( 'Saving...', 'ska-no-code-design' ), 'ska-builder-core') : __(__( 'Save Presets', 'ska-no-code-design' ), 'ska-builder-core')}
                        </Button>
                    </div>
                </Modal>
            )}

        </PanelBody>
    );
};
