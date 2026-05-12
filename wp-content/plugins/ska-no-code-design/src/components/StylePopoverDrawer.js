import { useState, useMemo } from '@wordpress/element';
import { Button, Popover, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const StylePopoverDrawer = ({ onSelectPreset }) => {
    const [isVisible, setIsVisible] = useState(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    return (
        <>
            <span
                role="button"
                onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleVisible();
                }}
                onPointerDown={(e) => e.stopPropagation()}
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: '28px',
                    height: '28px',
                    border: '1px solid #e2e8f0',
                    borderRadius: '2px',
                    cursor: 'pointer',
                    backgroundColor: '#fff',
                    color: '#475569'
                }}
                title={__('Visual Tailwind Browser', 'ska-builder-core')}
            >
                <span className="material-symbols-outlined" style={{ fontSize: '16px' }}>
                    palette
                </span>
            </span>

            {isVisible && (
                <Popover
                    position="bottom right"
                    onClose={toggleVisible}
                    noArrow={false}
                    className="ska-style-popover-drawer"
                    style={{ width: '300px' }}
                >
                    <div style={{ padding: '16px', textAlign: 'center', color: '#64748b' }}>
                        {__('Giao diện Visual Tailwind Browser sẽ được thiết kế tại đây...', 'ska-builder-core')}
                    </div>
                </Popover>
            )}
        </>
    );
};
