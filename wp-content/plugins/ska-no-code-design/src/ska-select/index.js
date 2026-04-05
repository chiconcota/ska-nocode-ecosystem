import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const {
            fieldName,
            optionsText,
            isRequired,
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
            className: `ska-select-block wp-block-ska-builder-select base-transition ${fullClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        // Parse options text to array
        const optionsList = optionsText.split('\n').filter(line => line.trim() !== '').map(line => {
            const parts = line.split(':');
            return {
                label: parts[0].trim(),
                value: parts.length > 1 ? parts[1].trim() : parts[0].trim()
            };
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Select Settings', 'ska-builder-core')} initialOpen={true}>
                        <TextControl
                            label={__('Field Name (Data Key)', 'ska-builder-core')}
                            value={fieldName}
                            onChange={(val) => setAttributes({ fieldName: val })}
                        />
                        <TextareaControl
                            label={__('Options List', 'ska-builder-core')}
                            value={optionsText}
                            onChange={(val) => setAttributes({ optionsText: val })}
                            help="Nhập mỗi tùy chọn 1 dòng. Cú pháp: Nhãn:giá_trị (VD: Nam:M)"
                            rows={6}
                        />
                        <ToggleControl
                            label={__('Required', 'ska-builder-core')}
                            checked={isRequired}
                            onChange={(val) => setAttributes({ isRequired: val })}
                        />
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                        customStyle={customStyle}
                        setCustomStyle={(val) => setAttributes({ customStyle: val })}
                    />
                </InspectorControls>

                <select {...blockProps} disabled={true} style={{ ...blockProps.style, minHeight: '38px', minWidth: '120px' }}>
                    {optionsList.map((opt, idx) => (
                        <option key={idx} value={opt.value}>{opt.label}</option>
                    ))}
                </select>
            </>
        );
    },
    save: () => {
        return null;
    },
});
