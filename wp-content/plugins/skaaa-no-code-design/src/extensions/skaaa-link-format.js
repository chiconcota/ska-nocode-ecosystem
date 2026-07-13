import { registerFormatType, toggleFormat, applyFormat } from '@wordpress/rich-text';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover, Button } from '@wordpress/components';
import { useState, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SkaaaLinkControl } from '../components/SkaaaLinkControl';

const FORMAT_NAME = 'skaaa/dynamic-link';

registerFormatType(FORMAT_NAME, {
    title: __('Skaaa Dynamic Link', 'skaaaaa-builder-core'),
    tagName: 'a',
    className: 'skaaa-dynamic-link',
    attributes: {
        url: 'href',
        target: 'target',
        dynamicSource: 'data-dynamic-source',
        dynamicKey: 'data-dynamic-key',
    },
    edit: ({ isActive, value, onChange, activeAttributes }) => {
        const [isPopoverVisible, setIsPopoverVisible] = useState(false);
        const anchorRef = useRef(null);

        const togglePopover = () => {
            setIsPopoverVisible(!isPopoverVisible);
        };

        const onLinkChange = (newLink) => {
            // Apply the new format
            const newFormat = {
                type: FORMAT_NAME,
                attributes: {
                    url: newLink.url || '#',
                    target: newLink.target || '_self',
                }
            };
            
            if (newLink.dynamic && newLink.dynamic.source !== 'static') {
                newFormat.attributes.dynamicSource = newLink.dynamic.source;
                newFormat.attributes.dynamicKey = newLink.dynamic.key;
            }

            onChange(applyFormat(value, newFormat));
        };

        const removeLink = () => {
            onChange(toggleFormat(value, { type: FORMAT_NAME }));
            setIsPopoverVisible(false);
        };

        const currentLink = {
            url: activeAttributes?.url || '',
            target: activeAttributes?.target || '_self',
            dynamic: {
                source: activeAttributes?.dynamicSource || 'static',
                key: activeAttributes?.dynamicKey || ''
            }
        };

        return (
            <>
                <RichTextToolbarButton
                    icon="admin-links"
                    title={__('Dynamic Link', 'skaaaaa-builder-core')}
                    onClick={togglePopover}
                    isActive={isActive}
                    ref={anchorRef}
                />
                {isPopoverVisible && (
                    <Popover
                        position="bottom center"
                        anchorRef={anchorRef.current}
                        onClose={() => setIsPopoverVisible(false)}
                        focusOnMount={false}
                    >
                        <div style={{ padding: '16px', minWidth: '300px' }}>
                            <SkaaaLinkControl
                                link={currentLink}
                                onChange={onLinkChange}
                            />
                            {isActive && (
                                <div style={{ marginTop: '10px', textAlign: 'right' }}>
                                    <Button isDestructive isSmall onClick={removeLink}>
                                        {__('Remove Link', 'skaaaaa-builder-core')}
                                    </Button>
                                </div>
                            )}
                        </div>
                    </Popover>
                )}
            </>
        );
    }
});
