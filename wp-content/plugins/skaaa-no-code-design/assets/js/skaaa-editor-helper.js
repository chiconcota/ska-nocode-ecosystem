(function () {
    let compilerInstance = null;
    let previousHash = '';
    let lastUnresolvedString = '';
    let activeIframeDoc = null; // Lưu trữ document hiện tại của iframe để tránh gán trùng lặp
    const stylesheetsMap = new Map(); // doc -> CSSStyleSheet

    // Gutenberg Editor CSS Fixes (will be bundled with compiled JIT CSS)
    const editorFixesCss = `
        @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap');

        /* Nuke Gutenberg Input Overrides with structural specificity (no !important) so Tailwind's !important wins */
        .editor-styles-wrapper .block-editor-block-list__block.wp-block-skaaaaa-builder-input:not([type="checkbox"]):not([type="radio"]),
        .editor-styles-wrapper .block-editor-block-list__block.wp-block-skaaaaa-builder-select select {
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
        .\\-outline-offset-1 { outline-offset: -1px !important; }
        .\\-outline-offset-2 { outline-offset: -2px !important; }
        .focus\:\-outline-offset-1:focus, .focus\:\-outline-offset-1:focus-within { outline-offset: -1px !important; }
        .focus\:\-outline-offset-2:focus, .focus\:\-outline-offset-2:focus-within { outline-offset: -2px !important; }

        /* V4 Polyfill: Indeterminate & Group-Has State for SVG Checkboxes (JIT CSS handles dynamically, this is helper) */
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

        /* Remove unwanted 1px border on Skaaa Button blocks in editor */
        .wp-block-skaaaaa-builder-button:not(.border) {
            border: none !important;
        }

        /* Ensure border class always works (preflight: false may not set border-style: solid) */
        .wp-block-skaaaaa-builder-container.border {
            border-style: solid !important;
        }

        .wp-block-skaaaaa-builder-video .block-editor-inner-blocks,
        .wp-block-skaaaaa-builder-video .block-editor-block-list__layout {
            display: contents !important;
        }

        [class*='wp-block-skaaaaa-builder'] > .wp-block-html {
            display: contents !important;
        }

        /* Video block specific editor fixes */
        .wp-block-skaaaaa-builder-video {
            isolation: isolate !important;
        }

        .wp-block-skaaaaa-builder-video .skaaa-video-wrapper {
            min-width: 100%;
        }

        /* CHILD BLOCK WIDTH RESET */
        .wp-block-skaaaaa-builder-container:not(.flex) > .wp-block:not([class*="max-w-"]):not(.container):not([class*="size-"]):not([class*="w-"]),
        .wp-block-skaaaaa-builder-container.flex-col > .wp-block:not([class*="max-w-"]):not(.container):not([class*="size-"]):not([class*="w-"]) {
            width: auto !important;
            max-width: none !important;
        }

        /* Ensure .container works by allowing margin: auto and limits */
        .wp-block-skaaaaa-builder-container > .wp-block.container {
            width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* POSITIONING OVERRIDES (CRITICAL) */
        .wp-block-skaaaaa-builder-container.absolute,
        .wp-block-skaaaaa-builder-container.absolute.block-editor-block-list__layout {
            position: absolute !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
        }

        .wp-block-skaaaaa-builder-container > .wp-block.absolute {
            position: absolute !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
        }

        .wp-block-skaaaaa-builder-container.sticky,
        .wp-block-skaaaaa-builder-container.sticky.block-editor-block-list__layout,
        .wp-block-skaaaaa-builder-container > .wp-block.sticky {
            position: sticky !important;
            width: auto !important;
            height: auto !important;
        }

        .wp-block-skaaaaa-builder-container.relative,
        .wp-block-skaaaaa-builder-container.relative.block-editor-block-list__layout,
        .wp-block-skaaaaa-builder-container > .wp-block.relative {
            position: relative !important;
        }

        .wp-block-skaaaaa-builder-container[class*="inset-"],
        .wp-block-skaaaaa-builder-container > .wp-block[class*="inset-"] {
            width: auto !important;
            height: auto !important;
        }

        .wp-block-skaaaaa-builder-container.flex-col:not([class*="items-"]) > .wp-block {
            align-self: stretch !important;
        }
    `;

    function getCompiler() {
        if (!compilerInstance && window.SkaaaWindCompiler) {
            compilerInstance = new window.SkaaaWindCompiler();
        }
        return compilerInstance;
    }

    /**
     * Run JIT compilation on all classes collected from Gutenberg block tree.
     */
    function runJITCompilation() {
        const compiler = getCompiler();
        if (!compiler) return;

        if (!window.wp || !window.wp.data || !window.wp.data.select) return;
        const select = window.wp.data.select;
        if (!select('core/block-editor')) return;

        const blocks = select('core/block-editor').getBlocks();
        const classes = [];

        // Recursively scan blocks for tailwind classes
        function scanBlocks(blockList) {
            if (!blockList) return;
            blockList.forEach(block => {
                if (block.attributes) {
                    if (block.attributes.tailwindClasses) {
                        classes.push(block.attributes.tailwindClasses);
                    }
                    if (block.attributes.className) {
                        classes.push(block.attributes.className);
                    }
                }
                if (block.innerBlocks && block.innerBlocks.length > 0) {
                    scanBlocks(block.innerBlocks);
                }
            });
        }

        scanBlocks(blocks);

        // Deduplicate classes
        const uniqueClasses = [...new Set(classes.join(' ').split(/\s+/).filter(Boolean))];
        const allClassStr = uniqueClasses.join(' ');

        if (allClassStr !== previousHash) {
            previousHash = allClassStr;

            const compiled = compiler.compile(allClassStr);
            
            // Build absolute unified stylesheet (Resets + Brand colors + Editor Fixes + Compiled classes)
            const brandColorsCss = (window.skaaaEditorConfig && window.skaaaEditorConfig.brandColorsCss) || '';
            const unifiedCss = `${brandColorsCss}\n${editorFixesCss}\n${compiled.css}`;

            updateEditorStylesheets(unifiedCss);

            // Log unresolved classes to console & trigger CDN Fallback dynamically
            if (compiled.unresolved && compiled.unresolved.length > 0) {
                const unresolvedStr = compiled.unresolved.join(', ');
                if (unresolvedStr !== lastUnresolvedString) {
                    lastUnresolvedString = unresolvedStr;
                    console.warn(`[SkaaaWind Warning] The following classes are not supported by the local JIT compiler: [${unresolvedStr}]`);
                }
                
                // Dynamically trigger CDN Fallback
                triggerCDNFallback();
            } else {
                lastUnresolvedString = '';
            }
        }
    }

    /**
     * Dynamically loads Tailwind CDN into all active iframe contexts as a fallback.
     */
    function triggerCDNFallback() {
        const iframeSelectors = [
            'iframe[name="editor-canvas"]',
            '.edit-site-visual-editor__editor-canvas iframe',
            '.block-editor-iframe__container iframe'
        ];

        iframeSelectors.forEach(selector => {
            const iframes = document.querySelectorAll(selector);
            iframes.forEach(iframe => {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc) {
                        loadCDNFallback(doc);
                    }
                } catch (e) {}
            });
        });
    }

    /**
     * Load Tailwind CDN script into target document body.
     */
    function loadCDNFallback(doc) {
        if (!doc || doc.getElementById('skaaawind-cdn-fallback')) return;

        const script = doc.createElement('script');
        script.id = 'skaaawind-cdn-fallback';
        script.src = 'https://cdn.tailwindcss.com';
        script.onload = () => {
            const tw = doc.defaultView.tailwind;
            if (tw && tw.config) {
                tw.config.important = true;
                tw.config.darkMode = 'class';
                tw.config.corePlugins = { preflight: false };
                const customBrandColors = (window.skaaaEditorConfig && window.skaaaEditorConfig.brandColorsJson) ? window.skaaaEditorConfig.brandColorsJson : {};
                tw.config.theme = { extend: { colors: customBrandColors } };
                console.log("Skaaa JIT: CDN Fallback dynamically loaded and configured in iframe.");
            }
        };
        doc.body.appendChild(script);
    }

    /**
     * Inject stylesheet into the target document using DOM style tags.
     */
    function updateDocStyle(doc, css) {
        try {
            let style = doc.getElementById('skaaawind-compiled-css');
            
            if (!style) {
                style = doc.createElement('style');
                style.id = 'skaaawind-compiled-css';
                
                const container = doc.head || doc.body;
                if (container) {
                    container.appendChild(style);
                }
            }
            
            if (style) {
                style.innerHTML = css;
            }
        } catch (e) {
            console.error('SkaaaWind stylesheet injection error:', e);
        }
    }

    /**
     * Inject style blocks into all active iframe contexts.
     * Prevents main document style injection to completely eliminate Gutenberg warning.
     */
    function updateEditorStylesheets(css) {
        const iframeSelectors = [
            'iframe[name="editor-canvas"]',
            '.edit-site-visual-editor__editor-canvas iframe',
            '.block-editor-iframe__container iframe'
        ];

        iframeSelectors.forEach(selector => {
            const iframes = document.querySelectorAll(selector);
            iframes.forEach(iframe => {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc) {
                        updateDocStyle(doc, css);
                    }
                } catch (e) {}
            });
        });
    }

    // Subscribe to Gutenberg store changes
    if (window.wp && window.wp.data) {
        window.wp.data.subscribe(() => {
            try {
                runJITCompilation();
            } catch (e) {
                console.error('Skaaa JIT compilation error in subscriber:', e);
            }
        });
    }

    /**
     * Inject SkaaaWind offline stylesheet context.
     */
    function injectTailwind(doc) {
        if (!doc || doc === window.document) return;

        // 1. Tạo đồng bộ thẻ style trong iframe ngay lập tức để bypass Gutenberg warning check
        let style = doc.getElementById('skaaawind-compiled-css');
        if (!style) {
            style = doc.createElement('style');
            style.id = 'skaaawind-compiled-css';
            const container = doc.body || doc.head;
            if (container) {
                container.appendChild(style);
            }
        }

        // 2. Chạy biên dịch đồng bộ lần đầu
        runJITCompilation();

        // MutationObserver for Dynamic Elements (e.g. video added later)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (node.classList.contains('skaaa-video-wrapper') || node.querySelector('.skaaa-video-wrapper')) {
                            runJITCompilation();
                        }
                    }
                });
            });
        });

        observer.observe(doc.body, { childList: true, subtree: true });
        console.log('Skaaa Builder: SkaaaWind offline JIT compiler active.');
    }

    /**
     * Scan the main document for the editor canvas iframe.
     */
    function scanForIframes() {
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
                        // Chỉ chạy inject khi phát hiện iframe mới (document context thay đổi)
                        if (doc !== activeIframeDoc) {
                            activeIframeDoc = doc;
                            previousHash = ''; // Reset một lần duy nhất để force compile cho context mới
                            
                            if (doc.readyState === 'complete' || doc.readyState === 'interactive') {
                                injectTailwind(doc);
                            } else {
                                iframe.addEventListener('load', () => {
                                    activeIframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                                    previousHash = '';
                                    injectTailwind(activeIframeDoc);
                                }, { once: true });
                            }
                        }
                    }
                } catch (e) { }
            });
        });

        if (!found) {
            // Adopt style in main doc if block editor layout is present directly (non-iframe mode)
            if (document.querySelector('.block-editor-block-list__layout')) {
                if (activeIframeDoc !== document) {
                    activeIframeDoc = document;
                    previousHash = '';
                    injectTailwind(document);
                }
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
     * Inject UI refinements into the MAIN document.
     */
    function injectMainDocStyles() {
        if (document.getElementById('skaaa-main-ui-refinements')) return;

        const style = document.createElement('style');
        style.id = 'skaaa-main-ui-refinements';
        style.innerHTML = `
            .components-panel__body,
            .components-panel__row,
            .interface-complementary-area {
                border-left: none !important;
            }

            body.skaaaaa-builder .interface-complementary-area input[type="text"],
            body.skaaaaa-builder .interface-complementary-area input[type="number"],
            body.skaaaaa-builder .interface-complementary-area input[type="url"],
            body.skaaaaa-builder .interface-complementary-area input[type="email"],
            body.skaaaaa-builder .interface-complementary-area input[type="search"],
            body.skaaaaa-builder .interface-complementary-area input.components-text-control__input,
            body.skaaaaa-builder .interface-complementary-area textarea.components-textarea-control__input,
            body.skaaaaa-builder .interface-complementary-area select.components-select-control__input,
            body.skaaaaa-builder .interface-complementary-area .components-base-control__help {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
                font-size: 14px !important;
                overflow: visible !important;
            }

            body.skaaaaa-builder .interface-complementary-area {
                --wp-admin-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
            }

            .skaaa-editor-active-indicator {
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
        `;
        if (document.body) {
            document.body.appendChild(style);
        } else {
            document.head.appendChild(style);
        }
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

    // Fail-safe poll
    setInterval(() => {
        injectMainDocStyles();
        scanForIframes();
    }, 5000);
})();
