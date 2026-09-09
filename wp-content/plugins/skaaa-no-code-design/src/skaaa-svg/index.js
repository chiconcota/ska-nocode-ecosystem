/**
 * Skaaa SVG Block
 * 
 * An inline SVG icon and vector graphic block with JIT Tailwind support.
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, Button, Placeholder, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useMemo } from '@wordpress/element';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';

// Pre-packaged popular SVG sample icons for quick insertion
const SAMPLE_SVGS = [
    {
        name: 'Check',
        code: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'
    },
    {
        name: 'Star',
        code: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'
    },
    {
        name: 'Arrow Right',
        code: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>'
    },
    {
        name: 'Heart',
        code: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
    },
    {
        name: 'Close',
        code: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>'
    }
];

/**
 * Extract SVG details (viewBox, shapes, attributes) from an SVG DOM element.
 */
function parseSvgElement(svgEl) {
    if (!svgEl) return null;

    const attributes = {};
    for (let i = 0; i < svgEl.attributes.length; i++) {
        const attr = svgEl.attributes[i];
        attributes[attr.name] = attr.value;
    }

    return {
        viewBox: svgEl.getAttribute('viewBox') || '0 0 24 24',
        fill: svgEl.getAttribute('fill') || undefined,
        stroke: svgEl.getAttribute('stroke') || undefined,
        strokeWidth: svgEl.getAttribute('stroke-width') || undefined,
        strokeLinecap: svgEl.getAttribute('stroke-linecap') || undefined,
        strokeLinejoin: svgEl.getAttribute('stroke-linejoin') || undefined,
        xmlns: svgEl.getAttribute('xmlns') || 'http://www.w3.org/2000/svg',
        className: svgEl.getAttribute('class') || '',
        innerHTML: svgEl.innerHTML || '',
        rawAttributes: attributes
    };
}

registerBlockType(metadata.name, {
    edit: ({ attributes, setAttributes }) => {
        const { svgCode = '', tailwindClasses = '', className = '', logic } = attributes;
        const [pastedInput, setPastedInput] = useState('');

        // Synchronize legacy className to tailwindClasses (Single Source of Truth)
        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                const merged = `${tailwindClasses} ${className}`.trim();
                setAttributes({ tailwindClasses: merged, className: '' });
            }
        }, []);

        // Parse SVG string into structured DOM representation
        const parsedSvg = useMemo(() => {
            if (!svgCode || typeof svgCode !== 'string' || !svgCode.trim()) {
                return null;
            }

            try {
                const parser = new DOMParser();
                // 1. Try XML parsing
                const doc = parser.parseFromString(svgCode.trim(), 'image/svg+xml');
                const parserError = doc.querySelector('parsererror');

                if (parserError) {
                    // Fallback to text/html parsing for tolerant HTML5 SVG syntax
                    const htmlDoc = parser.parseFromString(svgCode.trim(), 'text/html');
                    const htmlSvg = htmlDoc.querySelector('svg');
                    if (htmlSvg) {
                        return parseSvgElement(htmlSvg);
                    }
                    return { error: true, message: parserError.textContent };
                }

                const svgEl = doc.querySelector('svg');
                if (svgEl) {
                    return parseSvgElement(svgEl);
                }

                // If user supplied inner SVG elements without <svg> wrapper (e.g. just <path ...>)
                return {
                    viewBox: '0 0 24 24',
                    fill: 'currentColor',
                    xmlns: 'http://www.w3.org/2000/svg',
                    innerHTML: svgCode.trim(),
                    rawAttributes: {}
                };
            } catch (err) {
                return { error: true, message: err.message };
            }
        }, [svgCode]);

        // Handler to apply pasted code
        const handleApplySvg = (rawCode) => {
            if (!rawCode || !rawCode.trim()) return;
            const codeToApply = rawCode.trim();

            // Try to extract existing class attribute from SVG if user hasn't set custom tailwind classes
            try {
                const parser = new DOMParser();
                const doc = parser.parseFromString(codeToApply, 'text/html');
                const svgEl = doc.querySelector('svg');
                if (svgEl && svgEl.hasAttribute('class')) {
                    const extractedClasses = svgEl.getAttribute('class').trim();
                    if (extractedClasses && (!tailwindClasses || tailwindClasses === 'w-6 h-6 text-current')) {
                        setAttributes({ svgCode: codeToApply, tailwindClasses: extractedClasses });
                        setPastedInput('');
                        return;
                    }
                }
            } catch (e) {
                // Ignore extraction error and continue
            }

            setAttributes({ svgCode: codeToApply });
            setPastedInput('');
        };

        // Render Inspector Controls
        const inspectorControls = (
            <InspectorControls>
                <TailwindPanel
                    className={tailwindClasses || ''}
                    setClassName={(newClasses) => setAttributes({ tailwindClasses: newClasses, className: '' })}
                />
                <PanelBody title={__('SVG Source Code', 'skaaa-no-code-design')} initialOpen={false}>
                    <TextareaControl
                        label={__('Raw SVG Markup', 'skaaa-no-code-design')}
                        value={svgCode}
                        onChange={(val) => setAttributes({ svgCode: val })}
                        rows={8}
                        help={__('Edit XML SVG markup or replace it with new SVG code.', 'skaaa-no-code-design')}
                    />
                    <div style={{ display: 'flex', gap: '8px', marginTop: '12px' }}>
                        <Button
                            isSecondary
                            isSmall
                            onClick={() => setAttributes({ svgCode: '' })}
                            disabled={!svgCode}
                        >
                            {__('Clear SVG', 'skaaa-no-code-design')}
                        </Button>
                    </div>
                </PanelBody>
            </InspectorControls>
        );

        // Empty state: show Placeholder
        if (!svgCode || !svgCode.trim()) {
            const blockProps = useBlockProps({
                className: 'skaaa-svg-placeholder-block'
            });

            return (
                <div {...blockProps}>
                    {inspectorControls}
                    <Placeholder
                        icon="art"
                        label={__('Skaaa SVG', 'skaaa-no-code-design')}
                        instructions={__('Paste raw SVG code or choose a sample icon to get started.', 'skaaa-no-code-design')}
                    >
                        <div style={{ width: '100%', display: 'flex', flexDirection: 'column', gap: '10px' }}>
                            <TextareaControl
                                placeholder="<svg ...> ... </svg>"
                                value={pastedInput}
                                onChange={(val) => setPastedInput(val)}
                                rows={4}
                            />
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '8px' }}>
                                <Button
                                    isPrimary
                                    onClick={() => handleApplySvg(pastedInput)}
                                    disabled={!pastedInput.trim()}
                                >
                                    {__('Insert SVG', 'skaaa-no-code-design')}
                                </Button>
                                <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                                    <span style={{ fontSize: '11px', color: '#64748b' }}>
                                        {__('Quick samples:', 'skaaa-no-code-design')}
                                    </span>
                                    {SAMPLE_SVGS.map((sample) => (
                                        <Button
                                            key={sample.name}
                                            isSmall
                                            isTertiary
                                            onClick={() => handleApplySvg(sample.code)}
                                        >
                                            {sample.name}
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </Placeholder>
                </div>
            );
        }

        // Parsing error state
        if (parsedSvg?.error) {
            const blockProps = useBlockProps({
                className: 'skaaa-svg-error-block'
            });

            return (
                <div {...blockProps}>
                    {inspectorControls}
                    <Notice status="error" isDismissible={false}>
                        {__('Failed to parse SVG markup:', 'skaaa-no-code-design')} {parsedSvg.message}
                    </Notice>
                    <Button isSecondary onClick={() => setAttributes({ svgCode: '' })} style={{ marginTop: '8px' }}>
                        {__('Reset SVG', 'skaaa-no-code-design')}
                    </Button>
                </div>
            );
        }

        // Flat DOM Native SVG render in Editor Canvas
        const blockProps = useBlockProps({
            className: `skaaa-svg-block wp-block-skaaaaa-builder-svg ${tailwindClasses}`.trim(),
            viewBox: parsedSvg?.viewBox || '0 0 24 24',
            fill: parsedSvg?.fill,
            stroke: parsedSvg?.stroke,
            strokeWidth: parsedSvg?.strokeWidth,
            strokeLinecap: parsedSvg?.strokeLinecap,
            strokeLinejoin: parsedSvg?.strokeLinejoin,
            xmlns: parsedSvg?.xmlns || 'http://www.w3.org/2000/svg',
            dangerouslySetInnerHTML: { __html: parsedSvg?.innerHTML || '' }
        });

        return (
            <>
                {inspectorControls}
                <svg {...blockProps} />
            </>
        );
    },
    save: () => null
});
