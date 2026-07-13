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
    const [selectedCategory, setSelectedCategory] = useState('');
    
    // Hack to force ServerSideRender to reload
    const [renderKey, setRenderKey] = useState(1);
    
    const { replaceBlocks } = useDispatch('core/block-editor');

    const handleDetach = () => {
        if (!organismId) return;
        const data = window.skaaaOrganismsCache || {};
        const org = Object.values(data).find(o => String(o.id) === String(organismId));
        
        if (org && org.html_content) {
            // Parse the HTML content back into Gutenberg blocks
            const parsedBlocks = parse(org.html_content);
            if (parsedBlocks && parsedBlocks.length > 0) {
                replaceBlocks(clientId, parsedBlocks);
            } else {
                alert(__( 'The HTML content of this Symbol cannot be parsed.', 'skaaa-no-code-design' ));
            }
        } else {
            alert(__( 'No data for this Symbol was found in System Cache.', 'skaaa-no-code-design' ));
        }
    };
    
    const handleGlobalEdit = () => {
        if (!organismId) return;
        setIsEditingGlobal(true);
        const dummyPostId = window.skaaaEditorConfig?.dummyPostId || '';
        const adminUrl = window.skaaaEditorConfig?.adminUrl || '/wp-admin/';
        // Mở Iframe trỏ đến trang edit của dummy post, kèm tham số skaaa_iframe và edit_organism
        const url = `${adminUrl}post.php?post=${dummyPostId}&action=edit&skaaa_iframe=1&edit_organism=${organismId}`;
        setIframeUrl(url);
    };

    useEffect(() => {
        const handleMessage = (event) => {
            // Optional: verify origin
            if (event.data && event.data.type === 'SKAAA_ORGANISM_SAVED') {
                const { id, name, html } = event.data.data;
                
                // Cập nhật Cache tĩnh ngay lập tức
                if (window.skaaaOrganismsCache && window.skaaaOrganismsCache[id]) {
                    window.skaaaOrganismsCache[id].html_content = html;
                }

                // Hiển thị thông báo để giải thích cho người dùng tại sao nút Save không sáng
                const { dispatch } = wp.data;
                if (dispatch && dispatch('core/notices')) {
                    dispatch('core/notices').createSuccessNotice(
                        __( 'Symbol has been updated systemwide!', 'skaaa-no-code-design' ),
                        { type: 'snackbar', id: 'skaaa-symbol-saved-notice' }
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
        const data = window.skaaaOrganismsCache || {};
        
        // Nhóm các organisms theo category
        const groups = {};
        const uncategorizedKey = __( 'Uncategorized', 'skaaa-no-code-design' );
        
        Object.values(data).forEach(org => {
            const cat = org.category || uncategorizedKey;
            if (!groups[cat]) {
                groups[cat] = [];
            }
            groups[cat].push({
                label: org.name || String(org.id),
                value: String(org.id)
            });
        });

        // Xây dựng options có phân nhóm (optgroup)
        const groupedOptions = [];
        
        // Đưa Uncategorized lên đầu hoặc xử lý riêng
        if (groups[uncategorizedKey] && groups[uncategorizedKey].length > 0) {
            groupedOptions.push({
                label: uncategorizedKey,
                options: groups[uncategorizedKey]
            });
            delete groups[uncategorizedKey];
        }

        // Đưa các category khác vào
        Object.keys(groups).sort().forEach(cat => {
            groupedOptions.push({
                label: cat,
                options: groups[cat]
            });
        });

        // Extract Category Options
        const catOptions = [
            { label: __( 'All Categories', 'skaaa-no-code-design' ), value: '' }
        ];
        
        if (groups[uncategorizedKey]) {
            catOptions.push({ label: uncategorizedKey, value: uncategorizedKey });
        }
        
        Object.keys(groups).sort().forEach(cat => {
            if (cat !== uncategorizedKey) {
                catOptions.push({ label: cat, value: cat });
            }
        });

        // Filter symbols based on selectedCategory
        let filteredGroupedOptions = [];
        if (selectedCategory) {
            // Show flat list of symbols in this category without optgroup
            if (groups[selectedCategory]) {
                filteredGroupedOptions = groups[selectedCategory];
            }
        } else {
            // Show all grouped
            filteredGroupedOptions = groupedOptions;
        }

        // Đảm bảo Option luôn chứa giá trị hiện tại (Manual ID) nếu nó không nằm trong danh sách cache
        let currentIdExists = false;
        if (attributes.organismId) {
            Object.values(groups).forEach(g => {
                if (g.some(opt => opt.value === String(attributes.organismId))) currentIdExists = true;
            });
        }

        const finalOptions = [
            { label: __( '--- Select Symbol ---', 'skaaa-no-code-design' ), value: '' },
            ...filteredGroupedOptions
        ];

        if (attributes.organismId && !currentIdExists) {
            finalOptions.push({
                label: `[Custom ID] ${attributes.organismId}`,
                value: String(attributes.organismId)
            });
        }

        setOrganisms({
            categories: catOptions,
            symbols: finalOptions
        });
    }, [attributes.organismId, selectedCategory]);

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
                            label={__( 'Edit the original (Global Edit)', 'skaaa-no-code-design' )}
                            onClick={handleGlobalEdit}
                        />
                    </ToolbarGroup>
                </BlockControls>
            )}

            <InspectorControls>
                <PanelBody title={__('Skaaa Symbol Settings', 'skaaa-no-code-design')} initialOpen={true}>
                    <div style={{ marginBottom: '16px' }}>
                        <SelectControl
                            label={__( 'Filter by Category', 'skaaa-no-code-design' )}
                            value={selectedCategory}
                            options={organisms.categories || []}
                            onChange={(val) => setSelectedCategory(val)}
                            help={__( 'Optional: Narrow down the symbols list below by category.', 'skaaa-no-code-design' )}
                        />
                    </div>

                    <div style={{ marginBottom: '16px', borderTop: '1px solid #e2e8f0', paddingTop: '16px' }}>
                        <SelectControl
                            label={__( 'Select Skaaa Symbol', 'skaaa-no-code-design' )}
                            value={organismId}
                            options={organisms.symbols && organisms.symbols.length > 0 ? organisms.symbols : [{label: organismId ? `[ID] ${organismId}` : __( '--- Select Symbol ---', 'skaaa-no-code-design' ), value: organismId || ''}]}
                            onChange={(val) => setAttributes({ organismId: String(val) })}
                            help={__( 'Select the saved Symbol template to render the content.', 'skaaa-no-code-design' )}
                        />
                    </div>
                    
                    <div style={{ padding: '12px', backgroundColor: '#f1f5f9', borderRadius: '6px', border: '1px dashed #cbd5e1' }}>
                        <TextControl
                            label={__('Manual ID Override', 'skaaa-no-code-design')}
                            value={organismId}
                            onChange={(val) => setAttributes({ organismId: val })}
                            help={__( 'Manually enter an ID if it does not appear in the dropdown.', 'skaaa-no-code-design' )}
                        />
                    </div>

                    {organismId && (
                        <>
                            <div style={{ marginTop: '24px', padding: '16px', backgroundColor: '#eff6ff', border: '1px solid #bfdbfe', borderRadius: '8px' }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '8px' }}>
                                    <span className="material-symbols-outlined" style={{ fontSize: '18px', color: '#1d4ed8' }}>edit_document</span>
                                    <h4 style={{ margin: 0, fontSize: '14px', color: '#1d4ed8', fontWeight: 'bold' }}>{__( 'Global Edit', 'skaaa-no-code-design' )}</h4>
                                </div>
                                <p style={{ margin: '0 0 12px 0', fontSize: '12px', color: '#3b82f6' }}>
                                    {__( 'Open an independent draft to edit the original template.', 'skaaa-no-code-design' )}
                                    <span style={{ display: 'block', color: '#ef4444', fontWeight: 'bold', marginTop: '6px' }}>{__( '⚠️ Laying out the page takes a little while, BUT PEACE OF MIND <3S', 'skaaa-no-code-design' )}</span>
                                </p>
                                <Button
                                    variant="primary"
                                    onClick={handleGlobalEdit}
                                    style={{ width: '100%', justifyContent: 'center', backgroundColor: '#2563eb', border: 'none' }}
                                >
                                    {__( 'Edit Original Symbol', 'skaaa-no-code-design' )}
                                </Button>
                            </div>

                            <div style={{ marginTop: '16px', padding: '16px', backgroundColor: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: '8px' }}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '8px' }}>
                                    <span className="material-symbols-outlined" style={{ fontSize: '18px', color: '#64748b' }}>account_tree</span>
                                    <h4 style={{ margin: 0, fontSize: '14px', color: '#475569', fontWeight: 'bold' }}>{__( 'Local Edit (Detach)', 'skaaa-no-code-design' )}</h4>
                                </div>
                                <p style={{ margin: '0 0 12px 0', fontSize: '12px', color: '#64748b' }}>
                                    {__( 'Decompose this Symbol into local blocks. Changes will not affect the original template.', 'skaaa-no-code-design' )}
                                </p>
                                <Button
                                    variant="secondary"
                                    onClick={handleDetach}
                                    style={{ width: '100%', justifyContent: 'center', color: '#475569', borderColor: '#cbd5e1' }}
                                >
                                    {__( 'Detach from Symbol', 'skaaa-no-code-design' )}
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
                        block="skaaaaa-builder/organism-ref"
                        attributes={{ organismId: String(organismId) }}
                    />
                </div>
            ) : (
                <div style={placeholderStyle}>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', marginBottom: '8px' }}>
                        <span className="material-symbols-outlined" style={{ fontSize: '24px', color: '#10b981' }}>category</span>
                        <strong style={{ fontSize: '16px', color: '#1e293b' }}>Skaaa Symbol Placeholder</strong>
                    </div>
                    <div style={{ fontSize: '13px' }}>
                        ID Tham Chiếu: <code>{__( 'No ID yet', 'skaaa-no-code-design' )}</code>
                    </div>
                    <p style={{ marginTop: '12px', fontSize: '12px', fontStyle: 'italic', color: '#64748b' }}>
                        Vui lòng chọn Skaaa Symbol bên cột Settings để render nội dung.
                    </p>
                </div>
            )}

            {isEditingGlobal && (
                <Modal
                    title={__('Skaaa Global Editor - ' + organismId, 'skaaa-no-code-design')}
                    onRequestClose={() => setIsEditingGlobal(false)}
                    isFullScreen={true}
                    className="skaaa-global-edit-modal"
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
