import { useState, useRef, useEffect } from '@wordpress/element';
import { Button, TextControl, Modal, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const ClassChipInput = ({ className, setClassName }) => {
    const [inputValue, setInputValue] = useState('');
    const [isSavingPreset, setIsSavingPreset] = useState(false);
    const [presetName, setPresetName] = useState('');
    const inputRef = useRef(null);

    // Get current classes as an array
    const classes = className ? className.split(' ').filter(c => c.trim() !== '') : [];

    // Lookup preset by name (exact match, case insensitive)
    const lookupPreset = (name) => {
        const components = window.skaDesignTokens?.components;
        if (!components) return null;
        
        const searchName = name.toLowerCase();

        if (Array.isArray(components)) {
            const preset = components.find(c => c.name && c.name.toLowerCase() === searchName);
            return preset ? preset.value : null;
        } else if (typeof components === 'object') {
            // Handle legacy object format: { "button_primary": "bg-blue-500" }
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

        // Split by spaces or newlines to handle multiple pasted classes
        const newClassesArray = finalClassesString.split(/[\s,]+/).filter(c => c.trim() !== '');

        let currentClassesArray = [...classes];

        // Add new classes that don't already exist
        newClassesArray.forEach(cls => {
            if (!currentClassesArray.includes(cls)) {
                currentClassesArray.push(cls);
            }
        });

        setClassName(currentClassesArray.join(' '), '');
        setInputValue('');
    };

    const removeClass = (classToRemove) => {
        const updatedClasses = classes.filter(c => c !== classToRemove);
        setClassName(updatedClasses.join(' '), '');
    };

    const handleKeyDown = (e) => {
        e.stopPropagation(); // Stop Gutenberg from intercepting space/enter keys
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (inputValue.trim()) {
                addClasses(inputValue);
            }
        } else if (e.key === 'Backspace' && inputValue === '' && classes.length > 0) {
            // Remove the last class when pressing backspace on an empty input
            e.preventDefault();
            const updatedClasses = [...classes];
            updatedClasses.pop();
            setClassName(updatedClasses.join(' '), '');
        }
    };

    const handlePaste = (e) => {
        e.stopPropagation(); // Stop Gutenberg from intercepting Paste
        e.preventDefault();
        
        let pastedText = '';
        if (e.clipboardData && e.clipboardData.getData) {
            pastedText = e.clipboardData.getData('text/plain') || e.clipboardData.getData('text');
        } else if (window.clipboardData && window.clipboardData.getData) {
            pastedText = window.clipboardData.getData('Text');
        }

        if (pastedText) {
            addClasses(pastedText);
        }
    };

    const copyAll = () => {
        navigator.clipboard.writeText(classes.join(' ')).then(() => {
            // Optional: show a toast or temporary visual feedback
            console.log('Classes copied to clipboard!');
        });
    };

    const handleSavePreset = async () => {
        if (!presetName.trim()) return;
        
        const newPreset = {
            name: presetName.trim(),
            value: classes.join(' ')
        };

        // Get existing tokens
        const currentTokens = window.skaDesignTokens || {};
        const currentComponents = currentTokens.components || [];
        
        const updatedData = {
            ...currentTokens,
            components: [...currentComponents, newPreset]
        };

        try {
            const res = await apiFetch({
                path: '/ska-design/v1/tokens',
                method: 'POST',
                data: updatedData
            });
            
            if (res && res.success) {
                // Update local window object so it's immediately available
                window.skaDesignTokens = updatedData;
                setIsSavingPreset(false);
                setPresetName('');
                alert(__('Preset saved successfully!', 'ska-builder-core'));
            } else {
                alert(res?.message || __('Failed to save preset.', 'ska-builder-core'));
            }
        } catch (err) {
            console.error(err);
            alert(__('Network error while saving preset.', 'ska-builder-core'));
        }
    };

    return (
        <div className="ska-class-chip-input-container" style={{ marginBottom: '16px' }}>
            <TextareaControl
                value={className || ''}
                onChange={(newVal) => setClassName(newVal)}
                onPaste={handlePaste}
                placeholder={__('Nhập class hoặc dán tên Preset...', 'ska-builder-core')}
                style={{ 
                    fontFamily: 'monospace', 
                    fontSize: '13px',
                    width: '100%',
                    resize: 'vertical'
                }}
                rows={4}
            />

            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '8px' }}>
                <div style={{ display: 'flex', gap: '4px' }}>
                    <Button
                        isSecondary
                        isSmall
                        onClick={copyAll}
                        disabled={classes.length === 0}
                        style={{ fontSize: '12px' }}
                    >
                        {__('Copy All', 'ska-builder-core')}
                    </Button>
                    <Button
                        isDestructive
                        isTertiary
                        isSmall
                        onClick={() => {
                            if (window.confirm(__('Bạn có chắc chắn muốn xóa toàn bộ class?', 'ska-builder-core'))) {
                                setClassName('', '');
                            }
                        }}
                        disabled={classes.length === 0}
                        style={{ fontSize: '12px' }}
                    >
                        {__('Reset', 'ska-builder-core')}
                    </Button>
                </div>
                
                <Button
                    isPrimary
                    isSmall
                    onClick={() => setIsSavingPreset(true)}
                    disabled={classes.length === 0}
                    style={{ fontSize: '12px' }}
                >
                    {__('Save Preset', 'ska-builder-core')}
                </Button>
            </div>

            {isSavingPreset && (
                <Modal
                    title={__('Lưu UI Preset mới', 'ska-builder-core')}
                    onRequestClose={() => setIsSavingPreset(false)}
                    style={{ width: '400px' }}
                >
                    <div style={{ marginBottom: '16px' }}>
                        <p style={{ fontSize: '13px', color: '#64748b', marginBottom: '8px' }}>
                            {__('Preset này sẽ gom các class hiện tại lại và bạn có thể tái sử dụng dễ dàng sau này.', 'ska-builder-core')}
                        </p>
                        <TextControl
                            label={__('Tên Preset', 'ska-builder-core')}
                            value={presetName}
                            onChange={(val) => setPresetName(val)}
                            placeholder="e.g. Hero Button, Card Shadow..."
                        />
                    </div>
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px' }}>
                        <Button isSecondary onClick={() => setIsSavingPreset(false)}>
                            {__('Hủy', 'ska-builder-core')}
                        </Button>
                        <Button isPrimary onClick={handleSavePreset} disabled={!presetName.trim()}>
                            {__('Lưu Preset', 'ska-builder-core')}
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
};
