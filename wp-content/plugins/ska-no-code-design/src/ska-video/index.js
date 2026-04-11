import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { 
            url, 
            videoType, 
            autoplay, 
            loop, 
            muted, 
            controls, 
            aspectRatio = 'aspect-video', 
            tailwindClasses = '', 
            className = '',
            dynamic = { url_source: 'static', url_key: '' }, 
            logic = { enabled: false, key: '', operator: '==', value: '' },
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
            className: `ska-video-block overflow-hidden relative ${aspectRatio} ${tailwindClasses}`.trim(),
            style: parseStyle(customStyle)
        });

        const updateDynamic = (key, val) => {
            setAttributes({ dynamic: { ...dynamic, [key]: val } });
        };

        const updateLogic = (key, val) => {
            setAttributes({ logic: { ...logic, [key]: val } });
        };

        const onSelectVideo = (media) => {
            setAttributes({ url: media.url, videoType: 'local' });
        };

        // Simple helper to get Embed URL preview
        const getEmbedUrl = () => {
            if (!url) return '';
            if (videoType === 'youtube') {
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length === 11) ? `https://www.youtube.com/embed/${match[2]}` : url;
            }
            if (videoType === 'vimeo') {
                const regExp = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
                const match = url.match(regExp);
                return match ? `https://player.vimeo.com/video/${match[3]}` : url;
            }
            return url;
        };

        const embedUrl = getEmbedUrl();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Video Settings', 'ska-builder-core')} initialOpen={true}>
                        <SelectControl
                            label={__('Source Type', 'ska-builder-core')}
                            value={videoType}
                            options={[
                                { label: 'YouTube', value: 'youtube' },
                                { label: 'Vimeo', value: 'vimeo' },
                                { label: 'Local File', value: 'local' }
                            ]}
                            onChange={(val) => setAttributes({ videoType: val })}
                        />

                        {videoType === 'local' ? (
                            <div style={{ marginBottom: '16px' }}>
                                <MediaUploadCheck>
                                    <MediaUpload
                                        onSelect={onSelectVideo}
                                        allowedTypes={['video']}
                                        value={url}
                                        render={({ open }) => (
                                            <Button isSecondary onClick={open}>
                                                {url ? __('Change Video', 'ska-builder-core') : __('Select Video', 'ska-builder-core')}
                                            </Button>
                                        )}
                                    />
                                </MediaUploadCheck>
                                {url && <p style={{ fontSize: '11px', marginTop: '8px', overflow: 'hidden', textOverflow: 'ellipsis' }}>{url}</p>}
                            </div>
                        ) : (
                            <TextControl
                                label={__('Video URL', 'ska-builder-core')}
                                value={url}
                                onChange={(val) => setAttributes({ url: val })}
                                help={__('Enter the full URL of the video.', 'ska-builder-core')}
                            />
                        )}

                        <SelectControl
                            label={__('Aspect Ratio', 'ska-builder-core')}
                            value={aspectRatio}
                            options={[
                                { label: '16:9 (Video)', value: 'aspect-video' },
                                { label: '1:1 (Square)', value: 'aspect-square' },
                                { label: '4:3', value: 'aspect-4/3' },
                                { label: 'Auto', value: 'aspect-auto' }
                            ]}
                            onChange={(val) => setAttributes({ aspectRatio: val })}
                        />

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
                            <ToggleControl
                                label={__('Autoplay', 'ska-builder-core')}
                                checked={autoplay}
                                onChange={(val) => setAttributes({ autoplay: val })}
                            />
                            <ToggleControl
                                label={__('Loop', 'ska-builder-core')}
                                checked={loop}
                                onChange={(val) => setAttributes({ loop: val })}
                            />
                            <ToggleControl
                                label={__('Muted', 'ska-builder-core')}
                                checked={muted}
                                onChange={(val) => setAttributes({ muted: val })}
                            />
                            <ToggleControl
                                label={__('Controls', 'ska-builder-core')}
                                checked={controls}
                                onChange={(val) => setAttributes({ controls: val })}
                            />
                        </div>
                    </PanelBody>

                    <TailwindPanel
                        className={tailwindClasses || ''}
                        setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses, className: '' })}
                        customStyle={customStyle}
                        setCustomStyle={(val) => setAttributes({ customStyle: val })}
                    />

                    </InspectorControls>

                <div {...blockProps}>
                    <div className="w-full h-full bg-gray-100 flex items-center justify-center">
                        {dynamic.url_source !== 'static' ? (
                            <div className="p-4 text-center opacity-70">
                                <strong>[Dynamic Video Source: {dynamic.url_key || 'Empty Key'}]</strong>
                            </div>
                        ) : url ? (
                            videoType === 'local' ? (
                                <video src={url} style={{ width: '100%', height: '100%', objectFit: 'cover' }} muted />
                            ) : (
                                <iframe 
                                    src={embedUrl} 
                                    style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%' }}
                                    frameBorder="0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                />
                            )
                        ) : (
                            <div className="p-10 text-center border-2 border-dashed border-gray-300 rounded">
                                {__('Please select a video source or enter a URL.', 'ska-builder-core')}
                            </div>
                        )}
                    </div>
                </div>
            </>
        );
    },
    save: () => null,
});
