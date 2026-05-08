/**
 * Ska Container Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';
import { SkaLinkControl } from '../components/SkaLinkControl.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { tagName = 'div', tailwindClasses = '', className = '', logic = { enabled: false, key: '', operator: '==', value: '' }, isSkaForm = false, formActionId = '', usePersist = false, templateLock = 'false', link = { url: '', target: '_self', dynamic: { source: 'static', key: '' } } } = attributes;

        const { useEffect } = wp.element;
        useEffect(() => {
            // Auto-migrate from old className to new tailwindClasses (backward compatibility)
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                // Merge className into tailwindClasses to avoid data loss
                const merged = `${tailwindClasses} ${className}`.trim();
                setAttributes({ tailwindClasses: merged, className: '' });
            }
            // NOTE: Do NOT set className here - TailwindPanel callback handles proper split
        }, []);// For structural blocks, we combine everything on the wrapper to avoid layout breaks
        const fullClasses = (tailwindClasses + ' ' + className).trim();

        // Extract positioning from Tailwind classes and apply as inline styles
        // Inline styles ALWAYS override external CSS (even !important from Gutenberg)
        const getPositionStyles = (classes) => {
            const styles = {};
            if (!classes) return styles;
            const classList = classes.split(' ').filter(c => c.trim() !== '');

            classList.forEach(cls => {
                // Position type
                if (cls === 'absolute') styles.position = 'absolute';
                else if (cls === 'relative') styles.position = 'relative';
                else if (cls === 'fixed') styles.position = 'fixed';
                else if (cls === 'sticky') styles.position = 'sticky';
                else if (cls === 'static') styles.position = 'static';
                // Inset
                else if (cls === 'inset-0') styles.inset = '0';
                else if (cls.match(/^inset-(\d+)$/)) {
                    styles.inset = (parseFloat(cls.match(/^inset-(\d+)$/)[1]) * 0.25) + 'rem';
                }
                // Top/Right/Bottom/Left
                else if (cls.match(/^top-(\d+)$/)) {
                    styles.top = (parseFloat(cls.match(/^top-(\d+)$/)[1]) * 0.25) + 'rem';
                }
                else if (cls.match(/^right-(\d+)$/)) {
                    styles.right = (parseFloat(cls.match(/^right-(\d+)$/)[1]) * 0.25) + 'rem';
                }
                else if (cls.match(/^bottom-(\d+)$/)) {
                    styles.bottom = (parseFloat(cls.match(/^bottom-(\d+)$/)[1]) * 0.25) + 'rem';
                }
                else if (cls.match(/^left-(\d+)$/)) {
                    styles.left = (parseFloat(cls.match(/^left-(\d+)$/)[1]) * 0.25) + 'rem';
                }
                // Z-index
                else if (cls.match(/^z-(\d+)$/)) {
                    styles.zIndex = parseInt(cls.match(/^z-(\d+)$/)[1]);
                }
            });

            return styles;
        };

        const positionStyles = getPositionStyles(tailwindClasses);

        const blockProps = useBlockProps({
            className: `ska-container-block wp-block-ska-builder-container ${tailwindClasses}`.trim(),
            style: {
                ...positionStyles,
                minHeight: '30px'  // Preserve placeholder without wrapper div (Flat DOM)
            }
        });

        const ALLOWED_BLOCKS = [
            'ska-builder/text',
            'ska-builder/image',
            'ska-builder/button',
            'ska-builder/icon',
            'ska-builder/video',
            'ska-builder/container',
            'ska-builder/list',
            'ska-builder/html2tailwind',
            'ska-builder/form',
            'ska-builder/input',
            'ska-builder/select',
            'ska-builder/loop',
            'core/paragraph',
            'core/heading',
            'core/image'
        ];

        // If the tag is 'template', we must render it as a 'div' in the Gutenberg Editor
        // because native <template> tags hide their child elements in the browser DOM.
        const CustomTag = (tagName === 'template') ? 'div' : (tagName || 'div');

        // useInnerBlocksProps: merge inner blocks directly into wrapper
        // → eliminates block-editor-inner-blocks wrapper div
        // → absolute positioning works correctly in editor
        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            allowedBlocks: ALLOWED_BLOCKS,
            renderAppender: templateLock !== 'all' ? InnerBlocks.ButtonBlockAppender : false,
            templateLock: templateLock !== 'false' ? templateLock : false
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Container Settings', 'ska-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('HTML Tag', 'ska-builder-core')}
                            value={tagName}
                            options={[
                                { label: 'div', value: 'div' },
                                { label: 'section', value: 'section' },
                                { label: 'nav', value: 'nav' },
                                { label: 'header', value: 'header' },
                                { label: 'footer', value: 'footer' },
                                { label: 'main', value: 'main' },
                                { label: 'article', value: 'article' },
                                { label: 'aside', value: 'aside' },
                                { label: 'form', value: 'form' },
                                { label: 'template', value: 'template' },
                            ]}
                            onChange={(value) => setAttributes({ tagName: value })}
                        />
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                    />

                    <PanelBody title={__('Link Settings', 'ska-builder-core')} initialOpen={false}>
                        <SkaLinkControl
                            link={link}
                            onChange={(newLink) => setAttributes({ link: newLink })}
                        />
                    </PanelBody>
                    {tagName === 'form' && (
                        <PanelBody title={__('⚡ Ska Form Engine', 'ska-builder-core')} initialOpen={true}>
                            <ToggleControl
                                label={__('Kích hoạt Form Engine', 'ska-builder-core')}
                                checked={isSkaForm}
                                onChange={(val) => setAttributes({ isSkaForm: val })}
                                help="Bật để tự động xử lý Submit, Validation, Loading State."
                            />
                            {isSkaForm && (
                                <>
                                    <TextControl
                                        label={__('Logic Action ID', 'ska-builder-core')}
                                        value={formActionId}
                                        onChange={(val) => setAttributes({ formActionId: val })}
                                        help="ID của Workflow trong Ska Logic Engine (VD: contact_form)."
                                    />
                                    <ToggleControl
                                        label={__('Lưu nháp (Auto-Save)', 'ska-builder-core')}
                                        checked={usePersist}
                                        onChange={(val) => setAttributes({ usePersist: val })}
                                        help="Lưu dữ liệu đang điền vào LocalStorage, tránh mất khi F5."
                                    />
                                </>
                            )}
                        </PanelBody>
                    )}

                    </InspectorControls>

                <CustomTag {...innerBlocksProps} />
                {tagName === 'template' && (
                    <div className="ska-template-badge" style={{ position: 'absolute', top: 0, right: 0, background: '#f59e0b', color: '#fff', fontSize: '10px', padding: '2px 6px', zIndex: 10 }}>TEMPLATE</div>
                )}
            </>
        );
    },
    save: () => {
        return <InnerBlocks.Content />;
    }});

