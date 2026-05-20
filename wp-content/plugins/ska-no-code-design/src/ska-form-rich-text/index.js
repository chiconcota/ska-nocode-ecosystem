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
                            help="Tên biến trong cơ sở dữ liệu (vd: noi_dung)."
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

                <div {...blockProps} style={{ border: '1px dashed #ccc', padding: '10px', background: '#f9f9f9' }}>
                    <div style={{ marginBottom: '8px', fontWeight: 'bold' }}>{label}</div>
                    <div style={{ padding: '20px', background: '#fff', border: '1px solid #ddd', color: '#888' }}>
                        [Rich Text Area - Sẽ tải TinyMCE & Nút mở Trình thiết kế ở Frontend]
                    </div>
                </div>
            </>
        );
    },
    save: () => null, // Dynamic block
});
