import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    const { organismId } = attributes;
    const [organisms, setOrganisms] = useState([]);
    
    // Dùng data đã được load sẵn thay vì fetch mạng
    useEffect(() => {
        const data = window.skaOrganismsCache || {};
        const options = Object.values(data).map(org => ({
            label: org.name || org.id,
            value: org.id
        }));
        setOrganisms([
            { label: 'Chọn Organism...', value: '' },
            ...options
        ]);
    }, []);

    // Tạo một khung cho khối
    const blockProps = useBlockProps();

    const placeholderStyle = {
        background: '#f8fafc',
        border: '2px dashed #94a3b8',
        borderRadius: '8px',
        padding: '24px',
        textAlign: 'center',
        color: '#475569',
        fontFamily: 'system-ui, sans-serif'
    };

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody title={__('Ska Symbol Settings', 'ska-no-code-design')} initialOpen={true}>
                    <SelectControl
                        label={__('Chọn Ska Symbol', 'ska-no-code-design')}
                        value={organismId}
                        options={organisms.length > 0 ? organisms : [{label: organismId ? organismId : 'Chọn Organism...', value: organismId || ''}]}
                        onChange={(val) => setAttributes({ organismId: String(val) })}
                        help={__('Chọn mẫu Symbol đã lưu để render nội dung.', 'ska-no-code-design')}
                    />
                    <TextControl
                        label={__('Manual ID Override', 'ska-no-code-design')}
                        value={organismId}
                        onChange={(val) => setAttributes({ organismId: val })}
                        help="Ghi đè thủ công ID Tham Chiếu nếu list trên chưa kịp cập nhật"
                    />
                </PanelBody>
            </InspectorControls>

            {organismId ? (
                <div style={{ pointerEvents: 'none' }}>
                    <ServerSideRender
                        block="ska-builder/organism-ref"
                        attributes={{ organismId: String(organismId) }}
                    />
                </div>
            ) : (
                <div style={placeholderStyle}>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', marginBottom: '8px' }}>
                        <span className="material-symbols-outlined" style={{ fontSize: '24px', color: '#10b981' }}>category</span>
                        <strong style={{ fontSize: '16px', color: '#1e293b' }}>Ska Symbol Placeholder</strong>
                    </div>
                    <div style={{ fontSize: '13px' }}>
                        ID Tham Chiếu: <code>Chưa có ID</code>
                    </div>
                    <p style={{ marginTop: '12px', fontSize: '12px', fontStyle: 'italic', color: '#64748b' }}>
                        Vui lòng chọn Ska Symbol bên cột Settings để render nội dung.
                    </p>
                </div>
            )}
        </div>
    );
}
