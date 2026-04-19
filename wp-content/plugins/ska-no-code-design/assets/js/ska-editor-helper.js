(function () {
    /**
     * Inject Tailwind CDN into the provided document context.
     */
    function injectTailwind(doc) {
        if (!doc || doc.getElementById('ska-tailwind-cdn')) return;

        // 1. Set Tailwind Configuration BEFORE Script Load
        const config = doc.createElement('script');
        
        // 1. Inject "Source of Truth" Brand Colors CSS from Plugin Core
        if (window.skaEditorConfig && window.skaEditorConfig.brandColorsCss) {
            const truthStyle = doc.createElement('style');
            truthStyle.id = 'ska-brand-colors-truth';
            truthStyle.innerHTML = window.skaEditorConfig.brandColorsCss;
            doc.head.appendChild(truthStyle);
            console.log('Ska Builder: Brand Colors loaded from Plugin Source.');
        }

        // 2. Load script first, then mutate config to trigger CDN Proxy rebuild
        const customBrandColors = (window.skaEditorConfig && window.skaEditorConfig.brandColorsJson) ? window.skaEditorConfig.brandColorsJson : {};
        
        const script = doc.createElement('script');
        script.id = 'ska-tailwind-cdn';
        script.src = 'https://cdn.tailwindcss.com';
        
        script.onload = () => {
            const tw = doc.defaultView.tailwind;
            if (tw && tw.config) {
                tw.config.important = true;
                tw.config.corePlugins = { preflight: false };
                tw.config.theme = { extend: { colors: customBrandColors } };
                console.log("Ska Builder: Forcing Tailwind CDN Rebuild via Config Mutation");
            }
        };
        doc.head.appendChild(script);

        // 4. Fix Gutenberg Layout Issues
        const style = doc.createElement('style');
        style.id = 'ska-editor-fixes';
        style.innerHTML = `
            @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap');

            /* Nuke Gutenberg Input Overrides with structural specificity (no !important) so Tailwind's !important wins */
            .editor-styles-wrapper .block-editor-block-list__block.wp-block-ska-builder-input:not([type="checkbox"]):not([type="radio"]),
            .editor-styles-wrapper .block-editor-block-list__block.wp-block-ska-builder-select select {
                appearance: none;
                background-color: transparent;
                border-width: 0;
                border-style: solid;
                border-color: #e5e7eb; /* Parity with frontend base reset */
                border-radius: 0;
                padding: 0;
                outline-width: 0;
                outline-style: solid;
                outline-color: transparent;
                margin: 0;
                box-shadow: none;
            }

            /* Tailwind V4 Layout Parity Shims for Editor */
            .\-outline-offset-1 { outline-offset: -1px !important; }
            .\-outline-offset-2 { outline-offset: -2px !important; }
            .focus\:\-outline-offset-1:focus, .focus\:\-outline-offset-1:focus-within { outline-offset: -1px !important; }
            .focus\:\-outline-offset-2:focus, .focus\:\-outline-offset-2:focus-within { outline-offset: -2px !important; }

            /* V4 Polyfill: Indeterminate & Group-Has State for SVG Checkboxes (CDN v3 doesn't support) */
            .group:has(:checked) .group-has-checked\:opacity-100 { opacity: 1 !important; }
            .group:has(:disabled) .group-has-disabled\:stroke-gray-950\/25 { stroke: rgba(3, 7, 18, 0.25) !important; }
            :indeterminate.indeterminate\:bg-indigo-600 { background-color: #4f46e5 !important; }
            :indeterminate.indeterminate\:border-indigo-600 { border-color: #4f46e5 !important; }
            :disabled:checked.disabled\:checked\:bg-gray-100 { background-color: #f3f4f6 !important; }

            /* Ensure outline width utilities force solid style if Preflight is disabled */
            [class*="outline-1"], [class*="outline-2"], [class*="outline-4"], [class*="outline-8"] {
                outline-style: solid !important;
            }

            /* GLOBAL UI REFINEMENT: Remove all vertical lines from Sidebar panels */
            .components-panel__body {
                border-left: none !important;
            }

            /* Force root container to not be a flexbox */
            .is-root-container.block-editor-block-list__layout {
                display: block !important;
            }

            /* Remove unwanted 1px border on Ska Button blocks in editor */
            .wp-block-ska-builder-button:not(.border) {
                border: none !important;
            }

            /* Ensure border class always works (preflight: false may not set border-style: solid) */
            .wp-block-ska-builder-container.border {
                border-style: solid !important;
            }

            /* 
             * FLAT DOM FIX (useInnerBlocksProps):
             * Container block IS now the .block-editor-block-list__layout.
             * Children (.wp-block) are direct descendants.
             * No more .block-editor-inner-blocks intermediate divs.
             *
             * For Video/List blocks (still using <InnerBlocks>), keep display:contents fallback.
             */
            .wp-block-ska-builder-video .block-editor-inner-blocks,
            .wp-block-ska-builder-video .block-editor-block-list__layout {
                display: contents !important;
            }

            /* 
             * CORE/HTML SVG WRAPPER FIX:
             * When SVG code is mapped to core/html, the .wp-block-html wrapper breaks Flex/Grid positioning 
             * (like overlapping checkbox inside a size-4 grid group). This removes the wrapper constraint.
             */
            [class*='wp-block-ska-builder'] > .wp-block-html {
                display: contents !important;
            }

            /* Video block specific editor fixes */
            .wp-block-ska-builder-video {
                isolation: isolate !important;
            }

            .wp-block-ska-builder-video .ska-video-wrapper {
                min-width: 100%;
            }

            /* 
             * CHILD BLOCK WIDTH RESET:
             * Break out of Gutenberg's narrow 600px default formatting.
             * Container children (.wp-block) need auto width unless user specifies max-w-*, .container, or size-*.
             * size-* excluded: Tailwind size utilities (size-10, size-12...) must keep their explicit dimensions.
             */
            .wp-block-ska-builder-container:not(.flex) > .wp-block:not([class*="max-w-"]):not(.container):not([class*="size-"]):not([class*="w-"]),
            .wp-block-ska-builder-container.flex-col > .wp-block:not([class*="max-w-"]):not(.container):not([class*="size-"]):not([class*="w-"]) {
                width: auto !important;
                max-width: none !important;
            }

            /* Ensure .container works by allowing margin: auto and limits */
            .wp-block-ska-builder-container > .wp-block.container {
                width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            /* 
             * POSITIONING OVERRIDES (CRITICAL):
             * With useInnerBlocksProps, ALL Container blocks get .block-editor-block-list__layout.
             * WordPress CSS sets position: relative on .block-editor-block-list__layout.
             * This overrides Tailwind absolute on child containers.
             *
             * Solution: Override DIRECTLY on the element using high-specificity selectors.
             * .wp-block-ska-builder-container.absolute targets the container ITSELF (not children).
             */

            /* Direct element override: Container block with absolute class */
            .wp-block-ska-builder-container.absolute,
            .wp-block-ska-builder-container.absolute.block-editor-block-list__layout {
                position: absolute !important;
                width: auto !important;
                height: auto !important;
                margin: 0 !important;
            }

            /* Child blocks (non-container) with absolute inside a container */
            .wp-block-ska-builder-container > .wp-block.absolute {
                position: absolute !important;
                width: auto !important;
                height: auto !important;
                margin: 0 !important;
            }

            /* Sticky positioning */
            .wp-block-ska-builder-container.sticky,
            .wp-block-ska-builder-container.sticky.block-editor-block-list__layout,
            .wp-block-ska-builder-container > .wp-block.sticky {
                position: sticky !important;
                width: auto !important;
                height: auto !important;
            }

            /* Relative positioning — explicit override to ensure it wins over WP defaults */
            .wp-block-ska-builder-container.relative,
            .wp-block-ska-builder-container.relative.block-editor-block-list__layout,
            .wp-block-ska-builder-container > .wp-block.relative {
                position: relative !important;
            }

            /* 
             * INSET UTILITY CLASSES:
             * Ensure inset values are applied properly on container blocks & child wrappers
             */
            .wp-block-ska-builder-container[class*="inset-"],
            .wp-block-ska-builder-container > .wp-block[class*="inset-"] {
                width: auto !important;
                height: auto !important;
            }

            /*
             * FLEX-COL ALIGNMENT FIX:
             * Gutenberg's .block-editor-block-list__layout sets align-items: center
             * on flex containers, centering children (icons) horizontally.
             * align-self on children ALWAYS overrides parent align-items (CSS spec).
             * stretch keeps children full-width (no page layout break).
             * Icons align left naturally via default text-align inside full-width wrapper.
             * :not([class*="items-"]) ensures user-set Tailwind alignment wins.
             */
            .wp-block-ska-builder-container.flex-col:not([class*="items-"]) > .wp-block {
                align-self: stretch !important;
            }
        `;
        doc.head.appendChild(style);

        // 4. MutationObserver for Dynamic Elements (like Video or Iframe)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Node.ELEMENT_NODE
                        if (node.classList.contains('ska-video-wrapper') || node.querySelector('.ska-video-wrapper')) {
                            // Re-trigger tailwind processing if needed
                            if (window.tailwind) {
                                // Tailwind CDN usually watches the body, but sometimes we need to nudge it
                                // window.tailwind.render(); 
                            }
                        }
                    }
                });
            });
        });

        observer.observe(doc.body, { childList: true, subtree: true });

        console.log('Ska Builder: Tailwind CDN successfully injected and configured in iframe.');
    }

    /**
     * Scan the main document for the editor canvas iframe.
     */
    function scanForIframes() {
        // Gutenberg uses different iframe selectors depending on version/context
        const iframeSelectors = [
            'iframe[name="editor-canvas"]',
            '.edit-site-visual-editor__editor-canvas iframe',
            '.block-editor-iframe__container iframe'
        ];

        let found = false;
        iframeSelectors.forEach(selector => {
            const iframes = document.querySelectorAll(selector);
            iframes.forEach(iframe => {
                found = true;
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc) {
                        if (doc.readyState === 'complete' || doc.readyState === 'interactive') {
                            injectTailwind(doc);
                        } else {
                            iframe.addEventListener('load', () => injectTailwind(doc), { once: true });
                        }
                    }
                } catch (e) { }
            });
        });

        if (!found && !document.getElementById('ska-tailwind-cdn')) {
            // Check if we are in a non-iframe editor context
            if (document.querySelector('.block-editor-block-list__layout')) {
                injectTailwind(document);
            }
        }
    }

    /**
     * Set up a MutationObserver to watch for the iframe being added.
     */
    function observeEditor() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    scanForIframes();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Inject UI refinements into the MAIN document (sidebar, etc.)
     */
    function injectMainDocStyles() {
        if (document.getElementById('ska-main-ui-refinements')) return;

        const style = document.createElement('style');
        style.id = 'ska-main-ui-refinements';
        style.innerHTML = `
            /* GLOBAL UI REFINEMENT: Remove all vertical lines from Sidebar panels */
            .components-panel__body,
            .components-panel__row,
            .interface-complementary-area {
                border-left: none !important;
            }

            /* Indicators for debugging - Standardized icon style placeholder if needed */
            .ska-editor-active-indicator {
                position: fixed; 
                bottom: 12px; 
                right: 12px; 
                color: #10b981; 
                font-family: 'Material Symbols Outlined';
                font-size: 18px;
                z-index: 999999; 
                pointer-events: none;
                text-shadow: 0 0 5px rgba(16, 185, 129, 0.4);
            }

            /* GLOBAL UI REFINEMENT: Remove all vertical lines from Sidebar panels */
        `;
        document.head.appendChild(style);
    }

    // Initial scan
    if (document.readyState === 'complete') {
        injectMainDocStyles();
        scanForIframes();
        observeEditor();
    } else {
        window.addEventListener('load', () => {
            injectMainDocStyles();
            scanForIframes();
            observeEditor();
        });
    }

    // Fail-safe poll (much slower)
    setInterval(() => {
        injectMainDocStyles();
        scanForIframes();
    }, 5000);
})();
