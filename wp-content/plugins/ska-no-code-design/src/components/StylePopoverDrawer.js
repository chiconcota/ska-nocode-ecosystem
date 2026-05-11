import { useState, useMemo } from '@wordpress/element';
import { Button, Popover, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const StylePopoverDrawer = ({ onSelectPreset }) => {
    const [isVisible, setIsVisible] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    const components = window.skaDesignTokens?.components || [];

    const filteredComponents = useMemo(() => {
        let presetArray = [];
        if (Array.isArray(components)) {
            presetArray = components;
        } else if (typeof components === 'object' && components !== null) {
            // Handle legacy object format: map { "button_primary": "bg-blue-500" } to [{ name: "button_primary", value: "bg-blue-500" }]
            presetArray = Object.entries(components).map(([key, val]) => ({
                name: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()), // Attempt to reconstruct display name
                value: val
            }));
        }

        if (!searchQuery) return presetArray;
        const query = searchQuery.toLowerCase();
        return presetArray.filter(c => 
            (c.name && c.name.toLowerCase().includes(query)) || 
            (c.value && c.value.toLowerCase().includes(query))
        );
    }, [searchQuery, components]);

    return (
        <>
            <Button
                isSecondary
                onClick={toggleVisible}
                style={{
                    padding: '0 8px',
                    marginLeft: '4px',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '4px',
                    borderColor: '#e2e8f0',
                    color: '#475569'
                }}
                title={__('Browse UI Presets', 'ska-builder-core')}
            >
                <span className="material-symbols-outlined" style={{ fontSize: '18px' }}>
                    style
                </span>
            </Button>

            {isVisible && (
                <Popover
                    position="bottom right"
                    onClose={toggleVisible}
                    noArrow={false}
                    className="ska-style-popover-drawer"
                    style={{ width: '300px' }}
                >
                    <div style={{ padding: '12px', borderBottom: '1px solid #f1f5f9' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                            <h3 style={{ margin: 0, fontSize: '14px', color: '#1e293b', fontWeight: '600' }}>
                                {__('UI Presets', 'ska-builder-core')}
                            </h3>
                            <Button
                                isSmall
                                isTertiary
                                onClick={toggleVisible}
                                style={{ minWidth: 'auto', padding: '4px' }}
                            >
                                <span className="material-symbols-outlined" style={{ fontSize: '16px' }}>close</span>
                            </Button>
                        </div>
                        <TextControl
                            value={searchQuery}
                            onChange={(val) => setSearchQuery(val)}
                            placeholder={__('Search presets...', 'ska-builder-core')}
                            style={{ margin: 0 }}
                            autocomplete="off"
                        />
                    </div>

                    <div style={{ maxHeight: '300px', overflowY: 'auto', padding: '8px' }}>
                        {filteredComponents.length > 0 ? (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                                {filteredComponents.map((preset, index) => (
                                    <button
                                        key={index}
                                        onClick={() => {
                                            // The user clicks on the preset to apply it
                                            onSelectPreset(preset.value);
                                            toggleVisible();
                                        }}
                                        style={{
                                            display: 'block',
                                            width: '100%',
                                            textAlign: 'left',
                                            padding: '8px',
                                            background: 'none',
                                            border: 'none',
                                            borderRadius: '4px',
                                            cursor: 'pointer',
                                            transition: 'background-color 0.15s ease'
                                        }}
                                        onMouseEnter={(e) => e.currentTarget.style.backgroundColor = '#f1f5f9'}
                                        onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}
                                    >
                                        <div style={{ fontSize: '13px', fontWeight: '600', color: '#334155' }}>
                                            {preset.name}
                                        </div>
                                        <div style={{ fontSize: '11px', color: '#94a3b8', marginTop: '2px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                            {preset.value}
                                        </div>
                                    </button>
                                ))}
                            </div>
                        ) : (
                            <div style={{ padding: '24px 12px', textAlign: 'center', color: '#94a3b8', fontSize: '12px' }}>
                                {__('No presets found.', 'ska-builder-core')}
                            </div>
                        )}
                    </div>
                </Popover>
            )}
        </>
    );
};
