import { createHigherOrderComponent } from '@wordpress/compose';
import { BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Popover, TextControl, Button } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { select } from '@wordpress/data';

const withOrganismSaveButton = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        // Chỉ áp dụng cho các block của Ska Builder và khi Ska Data Pro đang active
        const isDataProActive = window.skaEditorConfig?.isDataProActive ?? true;
        if (!props.name || !props.name.startsWith('ska-builder/') || !isDataProActive) {
            return <BlockEdit {...props} />;
        }

        const [isPopoverVisible, setIsPopoverVisible] = useState(false);
        const [organismName, setOrganismName] = useState('');
        const [isSaving, setIsSaving] = useState(false);
        const { clientId } = props;

        const togglePopover = () => {
            setIsPopoverVisible((state) => !state);
            if (!isPopoverVisible) {
                // Tự động gán tên mặc định khi mở popover
                // Loại bỏ tiền tố 'ska-builder/'
                const shortName = props.name.replace('ska-builder/', '');
                setOrganismName(`My ${shortName.charAt(0).toUpperCase() + shortName.slice(1)}`);
            }
        };

        const handleSaveOrganism = async () => {
            if (!organismName.trim()) {
                alert(__( 'Please enter the name Organism', 'ska-no-code-design' ));
                return;
            }

            setIsSaving(true);
            try {
                // 1. Lấy JSON structure của block hiện tại
                const blockContent = select('core/block-editor').getBlock(clientId);
                
                // 2. Lấy rendered HTML (Serialization)
                // Tuy nhiên, việc lấy HTML sau có thể thực hiện thông qua wp.blocks.serialize
                const { serialize } = window.wp.blocks || {};
                let htmlContent = '';
                if (serialize && blockContent) {
                    htmlContent = serialize(blockContent);
                }

                // 3. POST data về server
                const response = await apiFetch({
                    path: '/ska-design/v1/organisms',
                    method: 'POST',
                    data: {
                        name: organismName,
                        block_json: blockContent,
                        html_content: htmlContent
                    }
                });

                if (response && response.success) {
                    // console.log("Organism saved:", response);
                    setIsPopoverVisible(false);
                    // Có thể dispatch một store của wp/notices để báo thành công
                    const { createSuccessNotice } = wp.data.dispatch('core/notices');
                    if (createSuccessNotice) {
                        createSuccessNotice(`Đã lưu Organism: ${organismName}`, { type: 'snackbar' });
                    } else {
                        alert(`Đã lưu Organism: ${organismName}`); // Fallback
                    }

                    // Biến hình (Transform) block hiện tại thành Ghost Block (Reference)
                    const returnedId = response.id || (response.data && response.data.id);
                    if (returnedId) {
                        // Bơm dữ liệu trực tiếp vào System Cache để Frontend sử dụng liền mà không cần tải lại trang
                        window.skaOrganismsCache = window.skaOrganismsCache || {};
                        window.skaOrganismsCache[returnedId] = {
                            id: String(returnedId),
                            name: organismName,
                            block_json: typeof blockContent === 'string' ? blockContent : JSON.stringify(blockContent),
                            html_content: htmlContent
                        };

                        wp.data.dispatch('core/block-editor').replaceBlock(
                            clientId,
                            window.wp.blocks.createBlock('ska-builder/organism-ref', { organismId: String(returnedId) })
                        );
                    }
                } else {
                    throw new Error(response?.data?.message || __( 'Unknown error', 'ska-no-code-design' ));
                }
            } catch (error) {
                console.error(error);
                alert(`Lỗi khi lưu Organism: ${error.message || __( 'Server error', 'ska-no-code-design' )}`);
            } finally {
                setIsSaving(false);
            }
        };

        return (
            <>
                <BlockEdit {...props} />
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={<span className="material-symbols-outlined" style={{fontFamily: 'Material Symbols Outlined'}}>magic_button</span>}
                            label={__(__( 'Save Organism (Ska Symbol)', 'ska-no-code-design' ), 'ska-no-code-design')}
                            onClick={togglePopover}
                            isActive={isPopoverVisible}
                        />
                    </ToolbarGroup>
                </BlockControls>

                {isPopoverVisible && (
                    <Popover
                        position="bottom center"
                        onClose={() => setIsPopoverVisible(false)}
                        focusOnMount="firstElement"
                    >
                        <div style={{ padding: '16px', width: '280px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
                            <div style={{ fontSize: '14px', fontWeight: 'bold' }}>
                                Lưu thành Ska Organism
                            </div>
                            <TextControl
                                label={__( 'Name Organism', 'ska-no-code-design' )}
                                value={organismName}
                                onChange={(value) => setOrganismName(value)}
                                placeholder={__( 'Enter name...', 'ska-no-code-design' )}
                            />
                            {(() => {
                                const data = window.skaOrganismsCache || {};
                                const isDuplicate = organismName.trim() && Object.values(data).some(org => 
                                    org.name && org.name.trim().toLowerCase() === organismName.trim().toLowerCase()
                                );
                                if (isDuplicate) {
                                    return <div style={{ color: '#ef4444', fontSize: '12px', marginTop: '-8px' }}>{__( 'This name already exists!', 'ska-no-code-design' )}</div>;
                                }
                                return null;
                            })()}
                            <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end' }}>
                                <Button isTertiary onClick={() => setIsPopoverVisible(false)}>
                                    Hủy
                                </Button>
                                <Button 
                                    isPrimary 
                                    isBusy={isSaving} 
                                    disabled={
                                        !organismName.trim() || 
                                        isSaving || 
                                        Object.values(window.skaOrganismsCache || {}).some(org => org.name && org.name.trim().toLowerCase() === organismName.trim().toLowerCase())
                                    }
                                    onClick={handleSaveOrganism}
                                    style={{
                                        background: '#10b981', // Màu Emerald của Ska
                                        borderColor: '#059669',
                                        color: '#fff'
                                    }}
                                >
                                    {isSaving ? __( 'Saving...', 'ska-no-code-design' ) : __( 'Save Block', 'ska-no-code-design' )}
                                </Button>
                            </div>
                            <p style={{ margin: '0', fontSize: '11px', color: '#64748b' }}>
                                Khối này sẽ được lưu ngầm vào Database và sẵn sàng cho việc Tái sử dụng (Reusable).
                            </p>
                        </div>
                    </Popover>
                )}
            </>
        );
    };
}, 'withOrganismSaveButton');

addFilter('editor.BlockEdit', 'ska-builder/extensions/save-organism', withOrganismSaveButton);
