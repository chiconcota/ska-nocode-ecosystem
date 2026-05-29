import { useBlockProps, InspectorControls, BlockControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button, Modal, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { parse } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, clientId }) {
    const { organismId } = attributes;
    const [organisms, setOrganisms] = useState([]);
    const [isEditingGlobal, setIsEditingGlobal] = useState(false);
    const [iframeUrl, setIframeUrl] = useState('');
    
    // Hack to force ServerSideRender to reload
    const [renderKey, setRenderKey] = useState(1);
    
    const { replaceBlocks } = useDispatch('core/block-editor');

    const handleDetach = () => {
        if (!organismId) return;
        const data = window.skaOrganismsCache || {};
        const org = Object.values(data).find(o => String(o.id) === String(organismId));
        
        if (org && org.html_content) {
            // Parse the HTML content back into Gutenberg blocks
            const parsedBlocks = parse(org.html_content);
            if (parsedBlocks && parsedBlocks.length > 0) {
                replaceBlocks(clientId, parsedBlocks);
            } else {
                alert(__(__( 'The HTML content of this Symbol cannot be parsed. ', 'ska-no-code-design' ), 'ska-no-code-design'));
            }
        } else {
            alert(__(__( 'No data for this Symbol was found in System Cache. ', 'ska-no-code-design' ), 'ska-no-code-design'));
        }
    };
    
    const handleGlobalEdit = () => {
        if (!organismId) return;
        setIsEditingGlobal(true);
        const dummyPostId = window.skaEditorConfig?.dummyPostId || '';
        const adminUrl = window.skaEditorConfig?.adminUrl || '/wp-admin/';
        // Mở Iframe trỏ đến trang edit của dummy post, kèm tham số ska_iframe và edit_organism
        const url = `${adminUrl}post.php?post=${dummyPostId}&action=edit&ska_iframe=1&edit_organism=${organismId}`;
        setIframeUrl(url);
    };

    useEffect(() => {
        const handleMessage = (event) => {
            // Optional: verify origin
            if (event.data && event.data.type === 'SKA_ORGANISM_SAVED') {
                const { id, name, html } = event.data.data;
                
                // Cập nhật Cache tĩnh ngay lập tức
                if (window.skaOrganismsCache && window.skaOrganismsCache[id]) {
                    window.skaOrganismsCache[id].html_content = html;
                }

                // Hiển thị thông báo để giải thích cho người dùng tại sao nút Save không sáng
                const { dispatch } = wp.data;
                if (dispatch && dispatch('core/notices')) {
                    dispatch('core/notices').createSuccessNotice(
                        __(__( 'Symbol has been updated systemwide! ', 'ska-no-code-design' ), 'ska-no-code-design'),
                        { type: 'snackbar', id: 'ska-symbol-saved-notice' }
                    );
                }

                // Đóng Modal và Reload SSR
                setIsEditingGlobal(false);
                setRenderKey(prev => prev + 1);
            }
        };

        window.addEventListener('message', handleMessage);
        return () => window.removeEventListener('message', handleMessage);
    }, []);
    
    // Dùng data đã được load sẵn thay vì fetch mạng
    useEffect(() => {
        const data = window.skaOrganismsCache || {};
        const options = Object.values(data).map(org => ({
            label: org.name || org.id,
            value: org.id
        }));
        setOrganisms([
            { label: __( 'Select Organism...', 'ska-no-code-design' ), value: '' },
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
            {organismId && (
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={<span className="material-symbols-outlined" style={{fontSize: '20px'}}>edit_square</span>}
                            label={__(__( 'Edit the original (Global Edit)', 'ska-no-code-design' ), 'ska-no-code-design')}
                            onClick={handleGlobalEdit}
                        />
                    </ToolbarGroup>
                </BlockControls>
            )}

            <InspectorControls>
                <PanelBody title={__('Ska Symbol Settings', 'ska-no-code-design')} initialOpen={true}>
                    <SelectControl
                        label={__(__( 'Select Ska Symbol', 'ska-no-code-design' ), 'ska-no-code-design')}
                        value={organismId}
                        options={organisms.length > 0 ? organisms : [{label: organismId ? organismId : __( 'Select Organism...', 'ska-no-code-design' ), value: organismId || ''}]}
                        onChange={(val) => setAttributes({ organismId: String(val) })}
                        help={__(__( 'Select the saved Symbol template to render the content.', 'ska-no-code-design' ), 'ska-no-code-design')}
                    />
                    <TextControl
                        label={__('Manual ID Override', 'ska-no-code-design')}
                        value={organismId}
                        onChange={(val) => setAttributes({ organismId: val })}
                        help={__( 'Manually overwrite Reference IDs if the above list has not been updated', 'ska-no-code-design' )}
                    />

                    {organismId && (
                        <>
                            <div style={{ marginTop: '24px', padding: '16px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '6px' }}>
                                <h4 style={{ margin: '0 0 8px 0', fontSize: '13px', color: '#1d4ed8' }}>{__( '{__(\'Global Edit\', \'ska-no-code-design\')}', 'ska-no-code-design' )}</h4>
                                <p style={{ margin: '0 0 12px 0', fontSize: '12px', color: '#64748b' }}>
                                    {__(__( 'Open an independent draft to edit the original template. ', 'ska-no-code-design' ), 'ska-no-code-design')}
                                    <span style={{ display: 'block', color: '#ef4444', fontWeight: 'bold', marginTop: '4px' }}>{__( 'Laying out the page takes a little while, BUT PEACE OF MIND <3S', 'ska-no-code-design' )}</span>
                                </p>
                                <Button
                                    variant="primary"
                                    onClick={handleGlobalEdit}
                                    style={{ width: '100%', justifyContent: 'center' }}
                                >
                                    {__(__( 'Edit Original', 'ska-no-code-design' ), 'ska-no-code-design')}
                                </Button>
                            </div>

                            <div style={{ marginTop: '16px', padding: '16px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '6px' }}>
                                <h4 style={{ margin: '0 0 8px 0', fontSize: '13px' }}>{__( '{__(\'Local Edit\', \'ska-no-code-design\')}', 'ska-no-code-design' )}</h4>
                                <p style={{ margin: '0 0 12px 0', fontSize: '12px', color: '#64748b' }}>
                                    {__(__( 'Decompose this Symbol into local blocks. ', 'ska-no-code-design' ), 'ska-no-code-design')}
                                </p>
                                <Button
                                    variant="secondary"
                                    onClick={handleDetach}
                                    style={{ width: '100%', justifyContent: 'center' }}
                                >
                                    {__(__( 'Symbol Decay (Detach)', 'ska-no-code-design' ), 'ska-no-code-design')}
                                </Button>
                            </div>
                        </>
                    )}
                </PanelBody>
            </InspectorControls>

            {organismId ? (
                <div style={{ pointerEvents: 'none' }}>
                    <ServerSideRender
                        key={`ssr-${renderKey}`}
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
                        ID Tham Chiếu: <code>{__( 'No ID yet', 'ska-no-code-design' )}</code>
                    </div>
                    <p style={{ marginTop: '12px', fontSize: '12px', fontStyle: 'italic', color: '#64748b' }}>
                        Vui lòng chọn Ska Symbol bên cột Settings để render nội dung.
                    </p>
                </div>
            )}

            {isEditingGlobal && (
                <Modal
                    title={__('Ska Global Editor - ' + organismId, 'ska-no-code-design')}
                    onRequestClose={() => setIsEditingGlobal(false)}
                    isFullScreen={true}
                    className="ska-global-edit-modal"
                >
                    <div style={{ width: '100%', height: 'calc(100vh - 80px)', backgroundColor: '#f8fafc', position: 'relative' }}>
                        <iframe 
                            src={iframeUrl} 
                            style={{ width: '100%', height: '100%', border: 'none' }}
                            title="Global Edit"
                        />
                    </div>
                </Modal>
            )}
        </div>
    );
}
