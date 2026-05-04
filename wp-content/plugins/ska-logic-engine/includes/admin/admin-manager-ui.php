<?php
defined( 'ABSPATH' ) || exit;

$workflows = get_option('ska_logic_simple_workflows', []);
?>

<div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 900px;">
    
    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="margin:0; font-size: 18px; color: #111827;"><span class="dashicons dashicons-networking" style="color:#64748b; margin-top:3px;"></span> Danh sách Băng Chuyền Logic</h3>
        
        <!-- Form Tạo Mới Ở Header -->
        <form method="POST" style="display:flex; gap: 8px;">
            <?php wp_nonce_field('ska_logic_nonce'); ?>
            <input type="hidden" name="ska_logic_action" value="create">
            <input type="text" name="new_workflow_id" required placeholder="Nhập tên ID mới (vd: lead_form)" pattern="[a-zA-Z0-9_-]+" title="Chỉ chứa chữ không dấu, số, gạch ngang và gạch dưới" style="border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 12px; font-size:13px; line-height:28px;">
            <button type="submit" class="button button-primary" style="background:#10b981; border-color:#10b981; border-radius: 6px; height:38px;">+ Khởi tạo Luồng vắng</button>
        </form>
    </div>

    <?php if (empty($workflows)): ?>
        <div style="text-align:center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <span class="dashicons dashicons-warning" style="font-size:32px; width:32px; height:32px; color:#94a3b8; display:block; margin: 0 auto 10px;"></span>
            <p style="margin:0; color:#64748b; font-size:14px;">Chưa có Băng Chuyền nào được lắp đặt. Hãy tạo cái mới ở phía trên!</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped" style="border-radius: 8px; overflow:hidden; border: 1px solid #e2e8f0; border-collapse: separate;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:12px 16px; font-weight:600; width:35%;">ID Luồng (Form Action)</th>
                    <th style="padding:12px 16px; font-weight:600;">Số lượng Bước (Nodes)</th>
                    <th style="padding:12px 16px; font-weight:600; width:35%; text-align:right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($workflows as $wf_id => $wf_data): 
                    $node_count = isset($wf_data['graph']['nodes']) && is_array($wf_data['graph']['nodes']) ? count($wf_data['graph']['nodes']) : 0;
                ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 16px; vertical-align: middle;">
                        <div id="display_box_<?php echo esc_attr($wf_id); ?>" style="display:flex; align-items:center; gap:8px;">
                            <strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html($wf_id); ?></strong>
                            <button type="button" onclick="document.getElementById('display_box_<?php echo esc_attr($wf_id); ?>').style.display='none'; document.getElementById('edit_box_<?php echo esc_attr($wf_id); ?>').style.display='flex';" class="button button-small" style="font-size:11px; padding:0 6px; height:22px; line-height:20px; color:#64748b; border:1px solid #cbd5e1; background:transparent;">✏️ Đổi ID</button>
                        </div>
                        <div id="edit_box_<?php echo esc_attr($wf_id); ?>" style="display:none; align-items:center; gap:8px;">
                            <form method="POST" style="display:flex; gap: 4px; margin:0;">
                                <?php wp_nonce_field('ska_logic_nonce'); ?>
                                <input type="hidden" name="ska_logic_action" value="rename">
                                <input type="hidden" name="old_id" value="<?php echo esc_attr($wf_id); ?>">
                                <input type="text" name="new_id" required value="<?php echo esc_attr($wf_id); ?>" pattern="[a-zA-Z0-9_-]+" style="width:180px; font-size:13px; padding:2px 8px; height:28px;">
                                <button type="submit" class="button button-primary button-small" style="height:28px;">Lưu</button>
                                <button type="button" class="button button-small" style="height:28px;" onclick="document.getElementById('edit_box_<?php echo esc_attr($wf_id); ?>').style.display='none'; document.getElementById('display_box_<?php echo esc_attr($wf_id); ?>').style.display='flex';">Hủy</button>
                            </form>
                        </div>
                    </td>
                    <td style="padding: 16px; vertical-align: middle; color: #64748b;">
                        <?php if($node_count > 0): ?>
                            <span style="background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600;"><?php echo $node_count; ?> Trạm</span>
                        <?php else: ?>
                            <span style="background:#f1f5f9; color:#94a3b8; padding:2px 8px; border-radius:12px; font-size:12px;">Trống Rỗng</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 16px; vertical-align: middle; text-align:right;">
                        <a href="?page=ska-logic-engine&view=builder&workflow_id=<?php echo esc_attr($wf_id); ?>" class="button" style="color: #4f46e5; border-color: #4f46e5; font-weight: 500; margin-right:8px;"><span class="dashicons dashicons-edit" style="margin-top:3px; font-size:16px;"></span> Thiết kế Luồng</a>
                        
                        <form method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('CẢNH BÁO: Hành động này sẽ xoá mất Json Graph của luồng. Nếu Frontend vẫn đang submit thì sẽ đứt cáp. Bạn chắc chứ?')">
                            <?php wp_nonce_field('ska_logic_nonce'); ?>
                            <input type="hidden" name="ska_logic_action" value="delete">
                            <input type="hidden" name="workflow_id" value="<?php echo esc_attr($wf_id); ?>">
                            <button type="submit" class="button" style="color: #ef4444; border-color: #fca5a5; background: #fef2f2;">Xóa bỏ</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
