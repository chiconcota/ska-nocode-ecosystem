import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, useInnerBlocksProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const {
            actionName,
            formMethod,
            formAction,
            tailwindClasses = '',
            className = '',
            customStyle
        } = attributes;

        const { useEffect } = wp.element;

        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
            }
        }, []);

        const parseStyle = (styleString) => {
            if (!styleString) return {};
            return styleString.split(';').reduce((acc, style) => {
                const parts = style.split(/:(.+)/);
                if (parts.length >= 2) {
                    const prop = parts[0].trim();
                    const value = parts[1].trim();
                    if (prop && value) {
                        const camelProp = prop.replace(/-([a-z])/g, g => g[1].toUpperCase());
                        acc[camelProp] = value;
                    }
                }
                return acc;
            }, {});
        };

        const fullClasses = (tailwindClasses + ' ' + className).trim();

        const blockProps = useBlockProps({
            className: `ska-form-block wp-block-ska-builder-form base-transition ${fullClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        const ALLOWED_BLOCKS = [
            'ska-builder/input',
            'ska-builder/select',
            'ska-builder/button',
            'ska-builder/container',
            'ska-builder/text',
            'ska-builder/image',
            'core/paragraph',
            'core/heading'
        ];

        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            allowedBlocks: ALLOWED_BLOCKS
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Form Trigger Settings', 'ska-builder-core')} initialOpen={true}>
                        <div style={{ paddingBottom: '15px' }}>
                            <Notice status="info" isDismissible={false}>
                                Ska Logic Engine: Đặt tên Event ID ở dưới. Khi user Submit, Form sẽ phát tín hiệu này về cho Logic Runner hứng.
                            </Notice>
                        </div>
                        <TextControl
                            label={__('Logic Event ID', 'ska-builder-core')}
                            value={actionName}
                            onChange={(val) => setAttributes({ actionName: val })}
                            help="Ví dụ: submit_contact, create_user"
                        />
                        <SelectControl
                            label={__('Fallback Method', 'ska-builder-core')}
                            value={formMethod}
                            options={[
                                { label: 'POST', value: 'POST' },
                                { label: 'GET', value: 'GET' }
                            ]}
                            onChange={(val) => setAttributes({ formMethod: val })}
                        />
                        <TextControl
                            label={__('Fallback Action URL (Webhook)', 'ska-builder-core')}
                            value={formAction}
                            onChange={(val) => setAttributes({ formAction: val })}
                            help="Dành cho việc không dùng Ska Logic, form sẽ post dữ liệu thẳng sang URL này."
                        />
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                        customStyle={customStyle}
                        setCustomStyle={(val) => setAttributes({ customStyle: val })}
                    />
                </InspectorControls>

                <form {...innerBlocksProps} onSubmit={(e) => e.preventDefault()} />
            </>
        );
    },
    save: () => {
        return <InnerBlocks.Content />;
    },
});
