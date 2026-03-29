/**
 * Ska Builder: html2tailwind Parser
 * 
 * Logic to convert raw HTML/Tailwind strings into Gutenberg blocks.
 */

window.ska = window.ska || {};
console.log('Ska Builder: html-to-blocks.js (V2 WHITESPACE FIX ACTIVE) is executing...');
window.ska.bridge = (function() {

    const TAG_MAP = {
        'DIV': 'ska-builder/container',
        'SECTION': 'ska-builder/container',
        'ARTICLE': 'ska-builder/container',
        'MAIN': 'ska-builder/container',
        'HEADER': 'ska-builder/container',
        'FOOTER': 'ska-builder/container',
        'ASIDE': 'ska-builder/container',
        'NAV': 'ska-builder/container',
        'H1': 'ska-builder/text',
        'H2': 'ska-builder/text',
        'H3': 'ska-builder/text',
        'H4': 'ska-builder/text',
        'H5': 'ska-builder/text',
        'H6': 'ska-builder/text',
        'P': 'ska-builder/text',
        'SPAN': 'ska-builder/text',
        'IMG': 'ska-builder/image',
        'UL': 'ska-builder/list',
        'OL': 'ska-builder/list',
        'LI': 'ska-builder/list-item',
        'BUTTON': 'ska-builder/button',
        'A': 'ska-builder/button',
    };

    /**
     * Helper to strip classes that are not currently supported by Ska Builder.
     */
    const stripUnsupportedClasses = (classes) => {
        if (!classes) return '';
        // Keeping classes as is (Tailwind will ignore unsupported classes if config is off)
        return classes.replace(/\s+/g, ' ').trim();
    };

    /**
     * Sanitize and clean HTML input.
     * Returns an object with content and body metadata.
     */
    const cleanHTML = (html) => {
        let bodyClass = '';
        let content = html;

        // If it's a full document, extract body content and classes
        if (html.includes('<body')) {
            const bodyMatch = html.match(/<body[^>]*class=["']([^"']+)["'][^>]*>([\s\S]*?)<\/body>/i);
            const contentMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);

            if (bodyMatch) {
                bodyClass = stripUnsupportedClasses(bodyMatch[1]);
                content = bodyMatch[2];
            } else if (contentMatch) {
                content = contentMatch[1];
            }
        }

        content = content
            .replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gim, "") // Remove scripts
            .replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gim, "") // Remove styles
            .replace(/<!--[\s\S]*?-->/g, "") // Remove comments
            .trim();

        return { content, bodyClass };
    };

    /**
     * Recursive function to transform DOM Node to Gutenberg Block.
     */
    const transformNode = (node) => {
        // Handle Text Nodes
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent.replace(/\s+/g, ' ').trim();
            if (!text) return null;
            
            return wp.blocks.createBlock('ska-builder/text', {
                content: text,
                tagName: 'span'
            });
        }

        if (node.nodeType !== Node.ELEMENT_NODE) return null;

        const tagName = node.tagName.toUpperCase();
        const desiredBlock = TAG_MAP[tagName] || 'ska-builder/container';
        let blockName = wp.blocks.getBlockType(desiredBlock) ? desiredBlock : 'core/group';
        const rawClasses = node.className || '';
        const classes = stripUnsupportedClasses(rawClasses);

        // SPECIAL: Check for Material Icons / Symbols
        if (tagName === 'SPAN' && (classes.includes('material-symbols-outlined') || classes.includes('material-icons-outlined'))) {
            blockName = wp.blocks.getBlockType('ska-builder/icon') ? 'ska-builder/icon' : blockName;
        }
        
        const attributes = {
            tailwindClasses: classes,
            customStyle: node.getAttribute('style') || '',
        };

        // Specific block logic
        if (blockName === 'ska-builder/container') {
            attributes.tagName = tagName.toLowerCase();
        }

        // SMART TEXT DETECTION: Neu text tag (p, span, h1-h6) chua icon span
        // thi promote thanh container de xu ly children thanh inner blocks
        if (blockName === 'ska-builder/text') {
            const hasBlockableChildren = Array.from(node.children).some(child => {
                const childClasses = child.className || '';
                return (
                    childClasses.includes('material-symbols-outlined') ||
                    childClasses.includes('material-icons-outlined') ||
                    child.tagName === 'IMG' ||
                    child.tagName === 'BUTTON'
                );
            });

            if (hasBlockableChildren) {
                // Promote to container - children se duoc xu ly recursive
                blockName = wp.blocks.getBlockType('ska-builder/container') ? 'ska-builder/container' : 'core/group';
                // p/span khong the chua block-level children -> dung div
                const phrasingTags = ['P', 'SPAN', 'A', 'LABEL', 'STRONG', 'EM'];
                attributes.tagName = phrasingTags.includes(tagName) ? 'div' : tagName.toLowerCase();
            } else {
                attributes.content = (node.innerHTML || '').replace(/\s+/g, ' ').trim();
                attributes.tagName = tagName.toLowerCase();
                attributes.tailwindClasses = classes; // Ensure tailwind classes are explicitly passed
                attributes.customStyle = node.getAttribute('style') || '';
            }
        }

        // Specific: list block
        if (blockName === 'ska-builder/list') {
            attributes.listType = tagName.toLowerCase(); // 'ul' or 'ol'
        }

        if (blockName === 'ska-builder/image') {
            attributes.url = node.getAttribute('src') || '';
            attributes.alt = node.getAttribute('alt') || node.getAttribute('data-alt') || '';
        }

        if (blockName === 'ska-builder/button') {
            // Check for icon inside button
            const iconSpan = node.querySelector('span.material-symbols-outlined, span.material-icons-outlined');
            if (iconSpan) {
                attributes.hasIcon = true;
                attributes.iconName = iconSpan.textContent.replace(/\s+/g, ' ').trim();
                
                // Extract custom icon classes
                let rawIconClasses = iconSpan.className || '';
                rawIconClasses = rawIconClasses.replace(/material-symbols-outlined/g, '')
                                               .replace(/material-icons-outlined/g, '')
                                               .replace(/\s+/g, ' ')
                                               .trim();
                if (rawIconClasses !== '') {
                    attributes.iconClasses = rawIconClasses;
                }
                
                // Determine position based on text nodes
                const childNodes = Array.from(node.childNodes);
                const iconIndex = childNodes.findIndex(n => n === iconSpan);
                const textNodesAfter = childNodes.slice(iconIndex + 1).filter(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim().length > 0);
                
                if (textNodesAfter.length > 0) {
                    attributes.iconPosition = 'left';
                } else {
                    attributes.iconPosition = 'right';
                }
                
                // Remove the span so its text doesn't flow into button text
                iconSpan.parentNode.removeChild(iconSpan);
            }

            attributes.text = (node.textContent || '').replace(/\s+/g, ' ').trim();
            
            if (tagName === 'A') {
                attributes.actionType = 'link';
                attributes.tagName = 'a';
                attributes.url = node.getAttribute('href') || '#';
                attributes.target = node.getAttribute('target') || '_self';
            } else if (tagName === 'BUTTON') {
                if (node.getAttribute('type') === 'submit') {
                    attributes.actionType = 'submit';
                    attributes.tagName = 'button';
                } else if (node.hasAttribute('data-popup-target')) {
                    attributes.actionType = 'popup';
                    attributes.tagName = 'button';
                } else {
                    // Force a button that isn't submit or popup to behave as a semantic link
                    attributes.actionType = 'link'; 
                    attributes.tagName = 'a';
                }
            }
        }

        if (blockName === 'ska-builder/icon') {
            attributes.iconName = node.innerText.trim() || 'star';
            // Strip font classes from tailwindClasses - render.php tu them material-symbols-outlined
            attributes.tailwindClasses = attributes.tailwindClasses
                .replace(/material-symbols-outlined/g, '')
                .replace(/material-icons-outlined/g, '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        // Handle inner blocks for non-atomic tags
        const atomicBlocks = ['ska-builder/image', 'ska-builder/icon', 'ska-builder/button'];
        
        if (atomicBlocks.includes(blockName)) {
            return wp.blocks.createBlock(blockName, attributes);
        }

        // If it's a text block, we don't transform children as blocks, we keep them as HTML content
        if (blockName === 'ska-builder/text') {
            return wp.blocks.createBlock(blockName, attributes);
        }

        // Recursive traversal for children
        const innerBlocks = Array.from(node.childNodes)
            .map(child => transformNode(child))
            .filter(block => block !== null);

        return wp.blocks.createBlock(blockName, attributes, innerBlocks);
    };

    /**
     * Entry point for conversion.
     */
    const convert = (html, clientId = null) => {
        const { content, bodyClass } = cleanHTML(html);
        const parser = new DOMParser();
        const doc = parser.parseFromString(content, 'text/html');
        
        let blocks = Array.from(doc.body.childNodes)
            .map(child => transformNode(child))
            .filter(block => block !== null);

        // If we have body classes, wrap everything in a root container to preserve global styles
        if (bodyClass && blocks.length > 0) {
            const rootBlockType = wp.blocks.getBlockType('ska-builder/container') ? 'ska-builder/container' : 'core/group';
            const rootContainer = wp.blocks.createBlock(rootBlockType, {
                tailwindClasses: bodyClass,
                tagName: 'div'
            }, blocks);
            blocks = [rootContainer];
        }

        if (blocks.length > 0) {
            if (clientId) {
                wp.data.dispatch('core/block-editor').replaceBlocks(clientId, blocks);
            } else {
                wp.data.dispatch('core/block-editor').insertBlocks(blocks);
            }
            return true;
        }

        return false;
    };

    return {
        convert: convert
    };

})();
