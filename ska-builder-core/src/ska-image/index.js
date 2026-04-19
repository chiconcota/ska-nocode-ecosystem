import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl, Button, ResponsiveControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { url, alt, id, tailwindClasses = '', className = '', dynamic = { source: 'static', key: '' }, logic = { enabled: false, key: '', operator: '==', value: '' }, customStyle } = attributes;

        const { useEffect } = wp.element;
        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                setAttributes({ className: '' });
            }
        }, []);

        // Helper to parse inline style string to React object
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

        const blockProps = useBlockProps({
            className: `ska-image-block ${tailwindClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        const onSelectImage = (media) => {
            if (!media || !media.url) return;
            console.log('Ska Image Selected:', media);
            setAttributes({
                url: media.url,
                id: media.id,
                alt: media.alt || ''
            });
        };

        const updateDynamic = (key, val) => {
            setAttributes({ dynamic: { ...dynamic, [key]: val } });
        };

        const updateLogic = (key, val) => {
            setAttributes({ logic: { ...logic, [key]: val } });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Image Settings', 'ska-builder-core')} initialOpen={true}>
                        {dynamic.source === 'static' && (
                            <div className="ska-image-upload-control" style={{ marginBottom: '15px' }}>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    value={id}
                                    render={({ open }) => (
                                        <Button
                                            onClick={open}
                                            variant="secondary"
                                            isPrimary={!url}
                                            style={{ width: '100%', justifyContent: 'center', height: 'auto', padding: '10px' }}
                                        >
                                            {!url ? __('Select Image', 'ska-builder-core') : __('Replace Image', 'ska-builder-core')}
                                        </Button>
                                    )}
                                />
                                {url && (
                                    <div style={{ marginTop: '10px', textAlign: 'center' }}>
                                        <Button
                                            isDestructive
                                            isLink
                                            onClick={() => setAttributes({ url: '', id: null, alt: '' })}
                                        >
                                            {__('Remove Image', 'ska-builder-core')}
                                        </Button>
                                    </div>
                                )}
                            </div>
                        )}
                        <TextControl
                            label={__('Alt Text', 'ska-builder-core')}
                            value={alt}
                            onChange={(val) => setAttributes({ alt: val })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Dynamic Data', 'ska-builder-core')} initialOpen={false}>
                        <SelectControl
                            label={__('Source', 'ska-builder-core')}
                            value={dynamic.source}
                            options={[
                                { label: 'Static (Upload)', value: 'static' },
                                { label: 'Featured Image', value: 'featured_image' },
                                { label: 'SCF Image Field', value: 'scf_image' }
                            ]}
                            onChange={(val) => updateDynamic('source', val)}
                        />
                        {dynamic.source === 'scf_image' && (
                            <TextControl
                                label={__('Field Name', 'ska-builder-core')}
                                value={dynamic.key}
                                onChange={(val) => updateDynamic('key', val)}
                            />
                        )}
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                        customStyle={customStyle}
                        setCustomStyle={(val) => setAttributes({ customStyle: val })}
                    />

                    <PanelBody title={__('Logic (Visibility)', 'ska-builder-core')} initialOpen={false}>
                        <ToggleControl
                            label={__('Enable Logic', 'ska-builder-core')}
                            checked={logic.enabled}
                            onChange={(val) => updateLogic('enabled', val)}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {dynamic.source === 'static' ? (
                        url ? (
                            <img src={url} alt={alt} className={tailwindClasses || ''} style={{ maxWidth: '100%', height: 'auto', display: 'block' }} />
                        ) : (
                                <div className={tailwindClasses || ''} style={{
                                    padding: '40px',
                                    border: '2px dashed #ccc',
                                    background: '#f9f9f9',
                                    textAlign: 'center',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    alignItems: 'center'
                                }}>
                                    <span className="material-symbols-outlined" style={{ fontSize: '48px', color: '#ccc', marginBottom: '10px' }} aria-hidden="true">image</span>
                                    <p style={{ margin: 0, color: '#666' }}>{__('No image selected', 'ska-builder-core')}</p>
                                </div>
                        )
                    ) : (
                        <div style={{
                            padding: '20px',
                            background: '#eee',
                            borderRadius: '4px',
                            textAlign: 'center',
                            opacity: 0.7,
                            border: '1px solid #ccc'
                        }}>
                            <span className="material-symbols-outlined" style={{ verticalAlign: 'middle', marginRight: '5px' }} aria-hidden="true">settings_backup_restore</span>
                            <strong>{__('Dynamic Image:', 'ska-builder-core')}</strong> {dynamic.source === 'scf_image' ? `SCF: ${dynamic.key}` : 'Featured Image'}
                        </div>
                    )}
                </div>
            </>
        );
    },
    save: () => {
        return null; // Dynamic block
    },
});
