/**
 * Ska Icon Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import metadata from './block.json';
import { TailwindPanel } from '../components/TailwindPanel.js';
import { splitTailwindClasses } from '../utils/tailwind-utils.js';
import { POPULAR_ICONS, ALL_ICONS } from '../utils/material-icons.js';

registerBlockType(metadata.name, {
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { iconName, tailwindClasses = '', className = '', logic, customStyle } = attributes;

        const { useEffect } = wp.element;
        useEffect(() => {
            if (className && !tailwindClasses) {
                setAttributes({ tailwindClasses: className, className: '' });
            } else if (className) {
                const merged = `${tailwindClasses} ${className}`.trim();
                setAttributes({ tailwindClasses: merged, className: '' });
            }
        }, []);

        const [isModalOpen, setIsModalOpen] = useState(false);
        const [modalSearch, setModalSearch] = useState('');

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
            className: `ska-icon-block wp-block-ska-builder-icon material-symbols-outlined ${tailwindClasses}`.trim(),
            style: { ...parseStyle(customStyle), fontSize: 'inherit' }
        });

        // Modal: filter ALL_ICONS, gioi han 200 ket qua de tranh lag
        const modalFilteredIcons = modalSearch
            ? ALL_ICONS.filter(icon => icon.includes(modalSearch.toLowerCase())).slice(0, 200)
            : POPULAR_ICONS;

        const updateLogic = (key, val) => {
            setAttributes({ logic: { ...logic, [key]: val } });
        };

        const selectIcon = (icon) => {
            setAttributes({ iconName: icon });
            setIsModalOpen(false);
            setModalSearch('');
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Icon Selection', 'ska-builder-core')} initialOpen={true}>
                        {/* Preview icon hien tai */}
                        <div style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '10px',
                            padding: '12px',
                            marginBottom: '12px',
                            background: '#f0f0f0',
                            borderRadius: '6px',
                        }}>
                            <span className="material-symbols-outlined" style={{ fontSize: '36px' }} aria-hidden="true">{iconName || 'star'}</span>
                            <div style={{ flex: 1 }}>
                                <div style={{ fontWeight: 'bold', fontSize: '13px' }}>{iconName || 'star'}</div>
                                <div style={{ fontSize: '11px', color: '#757575' }}>Current Icon</div>
                            </div>
                        </div>

                        {/* Custom icon name input */}
                        <TextControl
                            label={__('Icon Name', 'ska-builder-core')}
                            help="Material Symbols name (VD: payments, rocket_launch)"
                            value={iconName}
                            onChange={(val) => setAttributes({ iconName: val })}
                        />

                        {/* Nut mo popup chon icon */}
                        <Button
                            variant="secondary"
                            onClick={() => setIsModalOpen(true)}
                            style={{ width: '100%', justifyContent: 'center', marginBottom: '8px' }}
                        >
                            <span className="material-symbols-outlined" style={{ fontSize: '18px', marginRight: '6px' }} aria-hidden="true">apps</span>
                            {__('Browse Icons', 'ska-builder-core')} ({ALL_ICONS.length})
                        </Button>
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

                {/* Icon Picker Modal */}
                {isModalOpen && (
                    <Modal
                        title={__('Material Icons Library', 'ska-builder-core')}
                        onRequestClose={() => { setIsModalOpen(false); setModalSearch(''); }}
                        style={{ width: '680px', maxWidth: '90vw' }}
                    >
                        {/* Search bar */}
                        <div style={{ marginBottom: '16px' }}>
                            <TextControl
                                placeholder={__('Search 4207 icons... (VD: payment, star, home)', 'ska-builder-core')}
                                value={modalSearch}
                                onChange={(val) => setModalSearch(val)}
                                __nextHasNoMarginBottom
                            />
                            <div style={{ fontSize: '12px', color: '#757575', marginTop: '4px' }}>
                                {modalSearch
                                    ? `${modalFilteredIcons.length}${modalFilteredIcons.length >= 200 ? '+' : ''} results`
                                    : `Showing ${POPULAR_ICONS.length} popular icons — type to search all`
                                }
                            </div>
                        </div>

                        {/* Icon grid */}
                        <div style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(auto-fill, minmax(75px, 1fr))',
                            gap: '6px',
                            padding: '4px',
                        }}>
                            {modalFilteredIcons.map(icon => (
                                <Button
                                    key={icon}
                                    onClick={() => selectIcon(icon)}
                                    style={{
                                        padding: '8px 4px',
                                        height: 'auto',
                                        display: 'flex',
                                        flexDirection: 'column',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        minWidth: 'auto',
                                        border: iconName === icon ? '2px solid #007cba' : '1px solid #ddd',
                                        borderRadius: '6px',
                                        background: iconName === icon ? '#e6f2ff' : 'transparent',
                                        gap: '4px',
                                        cursor: 'pointer',
                                    }}
                                    title={icon}
                                >
                                    <span className="material-symbols-outlined" style={{ fontSize: '24px' }} aria-hidden="true">{icon}</span>
                                    <span style={{
                                        fontSize: '9px',
                                        color: '#666',
                                        maxWidth: '100%',
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        whiteSpace: 'nowrap',
                                        lineHeight: '1.2',
                                    }}>{icon}</span>
                                </Button>
                            ))}
                        </div>
                    </Modal>
                )}

                <span {...blockProps}>
                    {iconName || 'star'}
                </span>
            </>
        );
    },
    save: () => {
        return null;
    },
});
