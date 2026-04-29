import { PanelBody, TextControl, Button, Icon, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

export const TailwindPanel = ({ className, setClassName, customStyle, setCustomStyle }) => {
    const [inputValue, setInputValue] = useState('');
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
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            addClasses(inputValue);
        }
    };

    const addClasses = (classesToAdd) => {
        if (!classesToAdd.trim()) return;

        // Split by spaces to handle multiple pasted classes
        const newClassesArray = classesToAdd.split(' ').filter(c => c.trim() !== '');

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

    const removeClass = (classToRemove) => {
        if (!className) return;
        const currentClassesArray = className.split(' ').filter(c => c.trim() !== '');
        const updatedClasses = currentClassesArray.filter(c => c !== classToRemove);
        const combined = updatedClasses.join(' ');
        setClassName(combined, '');
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
                aria-label={__('Remove class', 'ska-builder-core')}
            >
                <svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    {__('Ska Tailwind', 'ska-builder-core')}
                    {hasClasses && (
                        <span 
                            className="material-symbols-outlined" 
                            title={__('Tailwind Active', 'ska-builder-core')}
                            aria-hidden="true"
                            style={{ 
                                fontSize: '14px', 
                                color: '#10b981', 
                                textShadow: '0 0 5px rgba(16, 185, 129, 0.4)',
                                lineHeight: '1',
                                fontFamily: 'Material Symbols Outlined'
                            }}
                        >
                            auto_awesome
                        </span>
                    )}
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

            {setCustomStyle && (
                <div style={{ marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e0e0e0' }}>
                    <TextareaControl
                        label={__('Custom Inline Style', 'ska-builder-core')}
                        value={customStyle || ''}
                        onChange={(val) => setCustomStyle(val)}
                        help={__('Ví dụ: background-image: url(...);', 'ska-builder-core')}
                        rows={3}
                    />
                </div>
            )}
        </PanelBody>
    );
};
