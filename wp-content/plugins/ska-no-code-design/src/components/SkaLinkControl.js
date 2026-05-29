import { __ } from '@wordpress/i18n';
import { TextControl, SelectControl, ToggleControl, BaseControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * SkaLinkControl Component
 * 
 * Provides a UI for inputting static URLs or selecting dynamic URL sources
 * (e.g., from the System or Loop Data) for block-level elements.
 */
export const SkaLinkControl = ({ link, onChange }) => {
    // Fallback defaults if link object is undefined
    const { 
        url = '', 
        target = '_self', 
        dynamic = { source: 'static', key: '' } 
    } = link || {};

    const isDynamicMode = dynamic.source !== 'static';

    const updateLink = (updates) => {
        onChange({ ...link, ...updates });
    };

    const updateDynamic = (updates) => {
        onChange({ ...link, dynamic: { ...dynamic, ...updates } });
    };

    const toggleDynamicMode = () => {
        if (isDynamicMode) {
            // Turn off dynamic mode -> revert to static
            updateDynamic({ source: 'static', key: '' });
        } else {
            // Turn on dynamic mode -> default to system home_url
            updateDynamic({ source: 'system', key: 'home_url' });
        }
    };

    return (
        <BaseControl label={__('Link / URL', 'ska-builder-core')}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px', marginBottom: '10px' }}>
                <div style={{ flex: 1 }}>
                    {!isDynamicMode ? (
                        <TextControl
                            type="text"
                            value={url}
                            onChange={(val) => updateLink({ url: val })}
                            onBlur={(e) => {
                                let val = e.target.value.trim();
                                // Auto-prefix https:// if it looks like a domain but lacks protocol/relative slashes
                                if (val && !/^(https?:\/\/|ftp:\/\/|mailto:|tel:|\/|#|\?)/i.test(val) && val.includes('.')) {
                                    updateLink({ url: 'https://' + val });
                                }
                            }}
                            placeholder={__( 'https://... or /path', 'ska-no-code-design' )}
                            __nextHasNoMarginBottom
                        />
                    ) : (
                        <div style={{ background: '#f0f0f0', padding: '10px', borderRadius: '4px', border: '1px solid #ddd' }}>
                            <SelectControl
                                label={__('Data Source', 'ska-builder-core')}
                                value={dynamic.source}
                                options={[
                                    { label: __('System Variables', 'ska-builder-core'), value: 'system' },
                                    { label: __('Loop Data', 'ska-builder-core'), value: 'loop' }
                                ]}
                                onChange={(val) => updateDynamic({ source: val, key: val === 'system' ? 'home_url' : '' })}
                                __nextHasNoMarginBottom
                                style={{ marginBottom: '10px' }}
                            />
                            
                            {dynamic.source === 'system' && (
                                <SelectControl
                                    label={__('System Value', 'ska-builder-core')}
                                    value={dynamic.key}
                                    options={[
                                        { label: __('Site Home URL', 'ska-builder-core'), value: 'home_url' },
                                        { label: __('Admin URL', 'ska-builder-core'), value: 'admin_url' },
                                        { label: __('Current Post Permalink', 'ska-builder-core'), value: 'current_post_url' }
                                    ]}
                                    onChange={(val) => updateDynamic({ key: val })}
                                    __nextHasNoMarginBottom
                                />
                            )}
                            
                            {dynamic.source === 'loop' && (
                                <TextControl
                                    label={__('Loop Field Name', 'ska-builder-core')}
                                    value={dynamic.key}
                                    onChange={(val) => updateDynamic({ key: val })}
                                    help={__('Ex: permalink, url, or relation_column.url (for Detail pages)', 'ska-builder-core')}
                                    __nextHasNoMarginBottom
                                />
                            )}
                        </div>
                    )}
                </div>
                <Button 
                    isSmall 
                    variant={isDynamicMode ? 'primary' : 'secondary'}
                    icon="database" 
                    onClick={toggleDynamicMode}
                    title={__('Toggle Dynamic Link', 'ska-builder-core')}
                    style={{ marginTop: isDynamicMode ? '0' : '2px' }}
                />
            </div>
            <ToggleControl
                label={__('Open in new tab', 'ska-builder-core')}
                checked={target === '_blank'}
                onChange={(val) => updateLink({ target: val ? '_blank' : '_self' })}
            />
        </BaseControl>
    );
};
