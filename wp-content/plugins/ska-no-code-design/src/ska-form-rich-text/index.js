import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { field, label, tailwindClasses } = attributes;
        
        const blockProps = useBlockProps({
            className: `ska-atomic-block ska-form-rich-text ${tailwindClasses || ''}`.trim(),
        });

        // The Editor just shows a mock representation of the Rich Text Block.
        // It does not launch the Scratchpad inside the Gutenberg Editor of Theme Builder.
        // The Scratchpad is only launched on the frontend.
        return (
            <>
                <InspectorControls>
                    <PanelBody title="Rich Text Settings" initialOpen={true}>
                        <TextControl
                            label="Field Name (Variable)"
                            value={field}
                            onChange={(val) => setAttributes({ field: val })}
                            help={__( 'Variable name in the database (eg: name_dung).', 'ska-no-code-design' )}
                        />
                        <TextControl
                            label="Label"
                            value={label}
                            onChange={(val) => setAttributes({ label: val })}
                        />
                        <TextControl
                            label="Tailwind Classes"
                            value={tailwindClasses}
                            onChange={(val) => setAttributes({ tailwindClasses: val })}
                            help="Utility classes wrapper"
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps} style={{ marginBottom: '24px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h3 style={{ margin: 0, fontSize: '18px', fontWeight: 'bold', color: '#1e293b' }}>{label}</h3>
                        <button type="button" style={{ display: 'inline-flex', alignItems: 'center', padding: '6px 12px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '12px', fontWeight: '600', color: '#334155', background: '#fff', cursor: 'pointer' }}>
                            <span className="dashicons dashicons-edit" style={{ fontSize: '14px', width: '14px', height: '14px', marginRight: '4px', display: 'inline-block', verticalAlign: 'middle' }}></span>
                            Open Design Editor
                        </button>
                    </div>
                    <div style={{ border: '1px solid #cbd5e1', borderRadius: '8px', overflow: 'hidden', background: '#fff', boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#f8fafc', borderBottom: '1px solid #e2e8f0', padding: '8px 12px' }}>
                            <div style={{ display: 'inline-flex', gap: '4px' }}>
                                <button type="button" style={{ padding: '4px 12px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '12px', background: '#fff', color: '#334155', cursor: 'pointer' }}>Add Media</button>
                            </div>
                            <div style={{ display: 'inline-flex', background: '#f1f5f9', padding: '2px', borderRadius: '6px', border: '1px solid #e2e8f0' }}>
                                <button type="button" style={{ padding: '4px 12px', border: 'none', borderRadius: '4px', fontSize: '12px', fontWeight: '600', background: '#fff', color: '#0f172a', boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1)' }}>Visual</button>
                                <button type="button" style={{ padding: '4px 12px', border: 'none', borderRadius: '4px', fontSize: '12px', fontWeight: '500', background: 'transparent', color: '#64748b' }}>Code</button>
                            </div>
                        </div>
                        <div style={{ padding: '40px 20px', textAlign: 'center', color: '#94a3b8', fontSize: '13px' }}>
                            [Rich Text Area - Sẽ tải TinyMCE ở Frontend]
                        </div>
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // Dynamic block
});
