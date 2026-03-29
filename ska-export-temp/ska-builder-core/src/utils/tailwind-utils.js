/**
 * Utility to split Tailwind classes into groups.
 * 
 * Layout classes are safe to apply to block wrappers because they don't have
 * visual "Red Box" elements like background, border, or shadow.
 * 
 * @param {string} classes 
 * @returns {object} { layout, styling }
 */
export const splitTailwindClasses = (classes) => {
    if (!classes) return { layout: '', styling: '' };

    // Regex for layout/positioning/sizing classes that Gutenberg should handle on the WRAPPER.
    // These are safe and won't cause the "Red Box" visual regression.
    const layoutPattern = /^(?:[a-z0-9_-]+:)?(w-|h-|flex|grid|block|inline|hidden|justify|items|content|self|place|columns|aspect|min-|max-|absolute|relative|fixed|sticky|m-|gap-|space-|top-|left-|right-|bottom-|z-|overflow-|group|container|isolate)/;
    
    const classArray = classes.split(/\s+/).filter(c => c.trim() !== '');
    const layout = [];
    const styling = [];

    classArray.forEach(cls => {
        if (layoutPattern.test(cls)) {
            layout.push(cls);
        } else {
            styling.push(cls);
        }
    });

    return {
        layout: layout.join(' '),
        styling: styling.join(' ')
    };
};
