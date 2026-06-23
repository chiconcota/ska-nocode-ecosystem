import { useState, useMemo, useEffect } from '@wordpress/element';
import { createPortal } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, Icon } from '@wordpress/components';
import { search, closeSmall, check, chevronDown, chevronUp } from '@wordpress/icons';
import { TAILWIND_CATEGORIES } from '../utils/tailwind-dictionary';

export const StylePopoverDrawer = ({ className, setClassName }) => {
    const [isVisible, setIsVisible] = useState(false);
    const [initialClassName, setInitialClassName] = useState('');
    const [searchQuery, setSearchQuery] = useState('');
    const [activeAccordion, setActiveAccordion] = useState('layout');

    const toggleVisible = () => {
        if (!isVisible) {
            setInitialClassName(className || '');
        }
        setIsVisible((state) => !state);
        setSearchQuery('');
    };

    const handleApply = () => {
        setIsVisible(false);
    };

    const handleBack = () => {
        setClassName(initialClassName, '');
        setIsVisible(false);
    };

    const handleToggleClass = (cls) => {
        const currentClassesArray = className ? className.split(' ').filter(c => c.trim() !== '') : [];
        if (currentClassesArray.includes(cls)) {
            const newArray = currentClassesArray.filter(c => c !== cls);
            setClassName(newArray.join(' '), '');
        } else {
            currentClassesArray.push(cls);
            setClassName(currentClassesArray.join(' '), '');
        }
    };

    const handleApplyPreset = (presetClassesString) => {
        setClassName(presetClassesString.trim(), '');
    };

    const handleAddCustomClass = () => {
        if (!searchQuery.trim()) return;
        const currentClassesArray = className ? className.split(' ').filter(c => c.trim() !== '') : [];
        const newClasses = searchQuery.split(' ').filter(c => c.trim() !== '');
        
        let updatedArray = [...currentClassesArray];
        newClasses.forEach(cls => {
            if (!updatedArray.includes(cls)) {
                updatedArray.push(cls);
            }
        });
        
        setClassName(updatedArray.join(' '), '');
        setSearchQuery('');
    };

    const presets = useMemo(() => {
        const tokens = window.skaDesignTokens || {};
        let comps = tokens.components || [];
        if (!Array.isArray(comps)) {
            comps = Object.values(comps);
        }
        
        const typoPresets = [];
        const typo = tokens.typography_scale || {};
        Object.keys(typo).forEach(key => {
            typoPresets.push({
                name: `Typo: ${key.toUpperCase()}`,
                value: typo[key]
            });
        });

        return [...typoPresets, ...comps];
    }, []);

    const currentClassesArray = className ? className.split(' ').filter(c => c.trim() !== '') : [];

    const allCategories = useMemo(() => {
        const cats = [];
        const themeColors = window.skaDesignTokens?.colors || {};
        
        if (Object.keys(themeColors).length > 0) {
            const colorClasses = [];
            Object.keys(themeColors).forEach(key => {
                colorClasses.push(`bg-${key}`, `text-${key}`, `border-${key}`);
            });
            cats.push({
                id: 'theme-colors',
                title: '🎨 Theme Colors',
                classes: colorClasses
            });
        }
        
        cats.push({
            id: 'default-colors',
            title: '🎨 Standard Colors',
            classes: [
                'bg-black', 'bg-white', 'bg-gray-100', 'bg-gray-500', 'bg-gray-900', 'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
                'text-black', 'text-white', 'text-gray-100', 'text-gray-500', 'text-gray-900', 'text-red-500', 'text-blue-500', 'text-green-500', 'text-yellow-500',
                'border-black', 'border-white', 'border-gray-100', 'border-gray-500', 'border-gray-900', 'border-red-500', 'border-blue-500', 'border-green-500', 'border-yellow-500'
            ]
        });
        cats.push(...TAILWIND_CATEGORIES);
        return cats;
    }, []);

    const filteredCategories = useMemo(() => {
        if (!searchQuery.trim()) return allCategories;
        const lowerQuery = searchQuery.toLowerCase();
        
        return allCategories.map(cat => {
            const matchedClasses = cat.classes.filter(c => c.toLowerCase().includes(lowerQuery));
            return {
                ...cat,
                classes: matchedClasses
            };
        }).filter(cat => cat.classes.length > 0);
    }, [searchQuery, allCategories]);

    useEffect(() => {
        if (searchQuery.trim() && filteredCategories.length > 0) {
            if (!filteredCategories.find(c => c.id === activeAccordion)) {
                setActiveAccordion(filteredCategories[0].id);
            }
        }
    }, [searchQuery, filteredCategories, activeAccordion]);

    const drawerUI = isVisible ? createPortal(
        <>
            {/* Backdrop overlay */}
            <div 
                style={{
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(0, 0, 0, 0.2)',
                    zIndex: 999998,
                }}
                onClick={handleBack}
            />
            {/* Drawer */}
            <aside 
                style={{
                    position: 'fixed',
                    top: '0',
                    right: '0',
                    height: '100vh',
                    width: '320px',
                    backgroundColor: '#fff',
                    zIndex: 999999,
                    boxShadow: '-4px 0 24px rgba(0,0,0,0.1)',
                    display: 'flex',
                    flexDirection: 'column',
                    boxSizing: 'border-box',
                    fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif',
                }}
                onClick={(e) => e.stopPropagation()}
                onPointerDown={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    padding: '16px',
                    borderBottom: '1px solid #e0e0e0',
                    backgroundColor: '#fff',
                }}>
                    <h2 style={{ margin: 0, fontSize: '16px', fontWeight: 600, color: '#1e1e1e' }}>
                        {__('Tailwind Browser', 'ska-builder-core')}
                    </h2>
                    <Button 
                        icon={closeSmall} 
                        onClick={handleBack} 
                        label={__('Close', 'ska-builder-core')}
                    />
                </div>

                {/* Content */}
                <div style={{ flexGrow: 1, overflowY: 'auto', padding: '16px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
                    
                    {/* Search */}
                    <div style={{ position: 'relative' }}>
                        <TextControl
                            value={searchQuery}
                            onChange={setSearchQuery}
                            placeholder={__('Search or add custom class...', 'ska-builder-core')}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    handleAddCustomClass();
                                }
                            }}
                        />
                        {searchQuery.trim() !== '' && (
                            <button
                                onClick={handleAddCustomClass}
                                style={{
                                    marginTop: '8px',
                                    width: '100%',
                                    padding: '6px 12px',
                                    backgroundColor: '#1e1e1e',
                                    color: '#fff',
                                    border: 'none',
                                    borderRadius: '3px',
                                    cursor: 'pointer',
                                    fontSize: '13px',
                                    display: 'flex',
                                    justifyContent: 'center',
                                    alignItems: 'center',
                                    gap: '6px'
                                }}
                            >
                                <Icon icon={check} size={16} />
                                {__(`Add custom class: "${searchQuery.trim()}"`, 'ska-builder-core')}
                            </button>
                        )}
                    </div>

                    {/* Presets */}
                    {(!searchQuery || searchQuery.trim() === '') && presets.length > 0 && (
                        <div>
                            <h3 style={{ fontSize: '11px', fontWeight: 600, color: '#757575', textTransform: 'uppercase', marginBottom: '8px', marginTop: 0 }}>
                                {__('Presets', 'ska-builder-core')}
                            </h3>
                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px' }}>
                                {presets.map(preset => (
                                    <button
                                        key={preset.name}
                                        onClick={() => handleApplyPreset(preset.value)}
                                        title={preset.value}
                                        style={{
                                            padding: '4px 10px',
                                            backgroundColor: '#f0f0f0',
                                            border: '1px solid #e0e0e0',
                                            borderRadius: '4px',
                                            fontSize: '12px',
                                            color: '#1e1e1e',
                                            cursor: 'pointer'
                                        }}
                                    >
                                        {preset.name}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Selected Classes */}
                    {currentClassesArray.length > 0 && (
                        <div>
                            <h3 style={{ fontSize: '11px', fontWeight: 600, color: '#007cba', textTransform: 'uppercase', marginBottom: '8px', marginTop: 0 }}>
                                {__('Selected', 'ska-builder-core')}
                            </h3>
                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }}>
                                {currentClassesArray.map(cls => (
                                    <div key={cls} style={{ 
                                        display: 'flex', 
                                        alignItems: 'center', 
                                        padding: '2px 6px', 
                                        backgroundColor: '#e6f2f8', 
                                        border: '1px solid #007cba', 
                                        borderRadius: '3px', 
                                        fontSize: '12px',
                                        color: '#007cba'
                                    }}>
                                        {cls}
                                        <button 
                                            onClick={() => handleToggleClass(cls)}
                                            style={{
                                                background: 'transparent',
                                                border: 'none',
                                                padding: '0 0 0 4px',
                                                margin: 0,
                                                cursor: 'pointer',
                                                display: 'flex',
                                                alignItems: 'center',
                                                color: '#007cba'
                                            }}
                                        >
                                            <Icon icon={closeSmall} size={14} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Categories */}
                    <div>
                        <h3 style={{ fontSize: '11px', fontWeight: 600, color: '#757575', textTransform: 'uppercase', marginBottom: '8px', marginTop: 0 }}>
                            {__('Categories', 'ska-builder-core')}
                        </h3>
                        
                        <div style={{ borderTop: '1px solid #e0e0e0' }}>
                            {filteredCategories.map(cat => {
                                const isOpen = activeAccordion === cat.id;
                                return (
                                    <div key={cat.id} style={{ borderBottom: '1px solid #e0e0e0' }}>
                                        <button 
                                            onClick={() => setActiveAccordion(isOpen ? null : cat.id)}
                                            style={{
                                                width: '100%',
                                                display: 'flex',
                                                justifyContent: 'space-between',
                                                alignItems: 'center',
                                                padding: '12px 0',
                                                backgroundColor: 'transparent',
                                                border: 'none',
                                                cursor: 'pointer',
                                                fontSize: '13px',
                                                fontWeight: 500,
                                                color: '#1e1e1e'
                                            }}
                                        >
                                            <span>{cat.title}</span>
                                            <Icon icon={isOpen ? chevronUp : chevronDown} size={18} />
                                        </button>
                                        
                                        {isOpen && (
                                            <div style={{ paddingBottom: '12px' }}>
                                                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }}>
                                                    {cat.classes.map(cls => {
                                                        const isSelected = currentClassesArray.includes(cls);
                                                        
                                                        let swatchColor = null;
                                                        if (cat.id === 'theme-colors') {
                                                            const key = cls.replace(/^(bg-|text-|border-)/, '');
                                                            const themeColors = window.skaDesignTokens?.colors || {};
                                                            swatchColor = themeColors[key];
                                                        } else if (cat.id === 'default-colors') {
                                                            const key = cls.replace(/^(bg-|text-|border-)/, '');
                                                            swatchColor = key === 'black' ? '#000000' : 
                                                                          key === 'white' ? '#ffffff' :
                                                                          key === 'gray-100' ? '#f3f4f6' :
                                                                          key === 'gray-500' ? '#6b7280' :
                                                                          key === 'gray-900' ? '#111827' :
                                                                          key === 'red-500' ? '#ef4444' :
                                                                          key === 'blue-500' ? '#3b82f6' :
                                                                          key === 'green-500' ? '#22c55e' :
                                                                          key === 'yellow-500' ? '#eab308' : null;
                                                        }

                                                        return (
                                                            <button
                                                                key={cls}
                                                                onClick={() => handleToggleClass(cls)}
                                                                style={{
                                                                    display: 'inline-flex',
                                                                    alignItems: 'center',
                                                                    gap: swatchColor ? '6px' : '0',
                                                                    padding: '4px 8px',
                                                                    backgroundColor: isSelected ? '#1e1e1e' : '#f0f0f0',
                                                                    color: isSelected ? '#ffffff' : '#1e1e1e',
                                                                    border: '1px solid transparent',
                                                                    borderRadius: '3px',
                                                                    fontSize: '12px',
                                                                    cursor: 'pointer'
                                                                }}
                                                            >
                                                                {swatchColor && (
                                                                    <span style={{
                                                                        width: '12px',
                                                                        height: '12px',
                                                                        borderRadius: '50%',
                                                                        backgroundColor: swatchColor,
                                                                        border: '1px solid rgba(0,0,0,0.1)'
                                                                    }} />
                                                                )}
                                                                {cls}
                                                            </button>
                                                        )
                                                    })}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                        
                        {filteredCategories.length === 0 && (
                            <p style={{ fontSize: '13px', color: '#757575', textAlign: 'center', marginTop: '16px' }}>
                                {__('No classes found matching', 'ska-builder-core')} "{searchQuery}"
                            </p>
                        )}
                    </div>

                </div>

                {/* Footer Actions */}
                <div style={{ 
                    padding: '16px', 
                    borderTop: '1px solid #e0e0e0', 
                    display: 'flex', 
                    gap: '12px',
                    backgroundColor: '#fff'
                }}>
                    <Button 
                        isSecondary
                        onClick={handleBack}
                        style={{ flex: 1, justifyContent: 'center' }}
                    >
                        {__('Cancel', 'ska-builder-core')}
                    </Button>
                    <Button 
                        isPrimary
                        onClick={handleApply}
                        style={{ flex: 1, justifyContent: 'center' }}
                    >
                        {__('Apply', 'ska-builder-core')}
                    </Button>
                </div>
            </aside>
        </>,
        document.body
    ) : null;

    return (
        <>
            <span
                role="button"
                tabIndex={0}
                aria-label={__('Visual Tailwind Browser', 'ska-builder-core')}
                onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleVisible();
                }}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleVisible();
                    }
                }}
                onPointerDown={(e) => e.stopPropagation()}
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: '28px',
                    height: '28px',
                    border: '1px solid #e2e8f0',
                    borderRadius: '2px',
                    cursor: 'pointer',
                    backgroundColor: isVisible ? '#f8fafc' : '#fff',
                    color: '#475569'
                }}
                title={__('Visual Tailwind Browser', 'ska-builder-core')}
            >
                <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M12 2C6.49 2 2 6.49 2 12s4.49 10 9.95 10c1.38 0 2.5-1.12 2.5-2.5 0-.61-.23-1.16-.62-1.57-.38-.41-.61-.95-.61-1.55 0-1.22.99-2.21 2.21-2.21h1.53c3.05 0 5.54-2.49 5.54-5.54C22 6.16 17.51 2 12 2zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 8 6.5 8 8 8.67 8 9.5 7.33 11 6.5 11zm3-4c-.83 0-1.5-.67-1.5-1.5S8.67 4 9.5 4 11 4.67 11 5.5 10.33 7 9.5 7zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 4 14.5 4 16 4.67 16 5.5 15.33 7 14.5 7zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 8 17.5 8 19 8.67 19 9.5 18.33 11 17.5 11z"/>
                </svg>
            </span>

            {drawerUI}
        </>
    );
};
