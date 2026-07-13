<?php
defined( 'ABSPATH' ) || exit;

global $wpdb;
$table_name = $wpdb->prefix . 'skaaa_data_sys_workflows';
$workflows_raw = [];

// Suppress error in case table is not created yet (though it should be)
$wpdb->suppress_errors(true);
$workflows_raw = $wpdb->get_results("SELECT workflow_id, name, app_id, status, node_count FROM `{$table_name}` ORDER BY app_id ASC, workflow_id ASC", ARRAY_A);
$wpdb->suppress_errors(false);

$skaaa_apps = get_option('skaaa_data_apps', []);

$workflows_by_app = [];
if (is_array($workflows_raw)) {
    foreach ($workflows_raw as $row) {
        $app_id = $row['app_id'] ?: 'uncategorized';
        $workflows_by_app[$app_id][] = $row;
    }
}
?>

<div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 1200px;">
    
    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="margin:0; font-size: 18px; color: #111827;"><span class="dashicons dashicons-networking" style="color:#64748b; margin-top:3px;"></span> <?php esc_html_e( 'Logic Streams List', 'skaaa-logic-engine' ); ?></h3>
        
        <!-- Form Tạo Mới Ở Header -->
        <div style="display:flex; gap: 8px;">
            <button type="button" onclick="document.getElementById('import_blueprint_modal').style.display='flex';" class="button button-secondary" style="border-radius: 6px; height:38px; display:flex; align-items:center; gap:4px;">
                <span class="dashicons dashicons-upload" style="margin-top:2px;"></span> <?php esc_html_e( 'Import Blueprint', 'skaaa-logic-engine' ); ?>
            </button>
            <form method="POST" style="display:flex; gap: 8px; margin:0;">
                <?php wp_nonce_field('skaaa_logic_nonce'); ?>
                <input type="hidden" name="skaaa_logic_action" value="create">
                <input type="text" name="new_workflow_id" required placeholder="<?php esc_attr_e( 'Enter the new ID name (eg: lead_form)', 'skaaa-logic-engine' ); ?>" pattern="[a-zA-Z0-9_-]+" title="<?php esc_attr_e( 'Contains only unaccented letters, numbers, dashes and underlines', 'skaaa-logic-engine' ); ?>" style="border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 12px; font-size:13px; line-height:28px;">
                <button type="submit" class="button button-primary" style="background:#10b981; border-color:#10b981; border-radius: 6px; height:38px;"><?php esc_html_e( '+ Initialize empty Stream', 'skaaa-logic-engine' ); ?></button>
            </form>
        </div>
    </div>

    <!-- Import Blueprint Modal -->
    <div id="import_blueprint_modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
        <div style="background:white; padding:24px; border-radius:12px; width:400px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0; font-size:18px;"><?php esc_html_e( 'Import JSON Blueprint', 'skaaa-logic-engine' ); ?></h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:16px;">
                <?php wp_nonce_field('skaaa_logic_nonce'); ?>
                <input type="hidden" name="skaaa_logic_action" value="import_blueprint">
                
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Workflow ID', 'skaaa-logic-engine' ); ?></label>
                    <input type="text" name="import_workflow_id" required pattern="[a-zA-Z0-9_-]+" style="width:100%; border-radius:6px; border:1px solid #d1d5db; padding:6px 12px;">
                </div>
                
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'JSON Blueprint File', 'skaaa-logic-engine' ); ?></label>
                    <input type="file" name="blueprint_file" accept=".json" required style="width:100%;">
                </div>
                
                <div>
                    <label style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" name="overwrite" value="1" checked>
                        <?php esc_html_e( 'Overwrite if exists', 'skaaa-logic-engine' ); ?>
                    </label>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:8px;">
                    <button type="button" class="button" onclick="document.getElementById('import_blueprint_modal').style.display='none';"><?php esc_html_e( 'Cancel', 'skaaa-logic-engine' ); ?></button>
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'skaaa-logic-engine' ); ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($workflows_by_app)): ?>
        <div style="text-align:center; padding: 40px 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <span class="dashicons dashicons-warning" style="font-size:32px; width:32px; height:32px; color:#94a3b8; display:block; margin: 0 auto 10px;"></span>
            <p style="margin:0; color:#64748b; font-size:14px;"><?php esc_html_e( 'No Conveyor Belt has been installed yet. ', 'skaaa-logic-engine' ); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped" style="border-radius: 8px; overflow:hidden; border: 1px solid #e2e8f0; border-collapse: separate;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:12px 16px; font-weight:600; width:45%;"><?php esc_html_e( 'Stream ID (Form Action)', 'skaaa-logic-engine' ); ?></th>
                    <th style="padding:12px 16px; font-weight:600; width:15%;"><?php esc_html_e( 'Number of Steps (Nodes)', 'skaaa-logic-engine' ); ?></th>
                    <th style="padding:12px 16px; font-weight:600; width:40%; text-align:right;"><?php esc_html_e( 'Operation', 'skaaa-logic-engine' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($workflows_by_app as $app_id => $flows): 
                    $app_name = 'Default Workspace';
                    if ($app_id === 'skaaa_system') {
                        $app_name = 'Site Management';
                    } elseif (isset($skaaa_apps[$app_id])) {
                        $app_name = $skaaa_apps[$app_id]['name'];
                    }
                ?>
                <tr style="background:#f1f5f9; font-weight:600;">
                    <td colspan="3" style="padding: 10px 16px; color: #475569; font-size:13px;">
                        <span class="dashicons dashicons-portfolio" style="font-size:16px; width:16px; height:16px; margin-right:5px; margin-top:2px; color:#4f46e5;"></span>
                        <?php echo esc_html($app_name); ?>
                    </td>
                </tr>
                <?php foreach($flows as $flow): 
                    $wf_id = $flow['workflow_id'];
                    $node_count = intval($flow['node_count']);
                ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 16px; vertical-align: middle;">
                        <div id="display_box_<?php echo esc_attr($wf_id); ?>" style="display:flex; align-items:center; gap:8px;">
                            <strong style="color: #0f172a; font-size: 14px;"><?php echo esc_html($wf_id); ?></strong>
                            <button type="button" onclick="document.getElementById('display_box_<?php echo esc_attr($wf_id); ?>').style.display='none'; document.getElementById('edit_box_<?php echo esc_attr($wf_id); ?>').style.display='flex';" class="button button-small" style="font-size:11px; padding:0 6px; height:22px; line-height:20px; color:#64748b; border:1px solid #cbd5e1; background:transparent;"><?php esc_html_e( '✏️ Change ID', 'skaaa-logic-engine' ); ?></button>
                        </div>
                        <div id="edit_box_<?php echo esc_attr($wf_id); ?>" style="display:none; align-items:center; gap:8px;">
                            <form method="POST" style="display:flex; gap: 4px; margin:0;">
                                <?php wp_nonce_field('skaaa_logic_nonce'); ?>
                                <input type="hidden" name="skaaa_logic_action" value="rename">
                                <input type="hidden" name="old_id" value="<?php echo esc_attr($wf_id); ?>">
                                <input type="text" name="new_id" required value="<?php echo esc_attr($wf_id); ?>" pattern="[a-zA-Z0-9_-]+" style="width:180px; font-size:13px; padding:2px 8px; height:28px;">
                                <button type="submit" class="button button-primary button-small" style="height:28px;"><?php esc_html_e( 'Save', 'skaaa-logic-engine' ); ?></button>
                                <button type="button" class="button button-small" style="height:28px;" onclick="document.getElementById('edit_box_<?php echo esc_attr($wf_id); ?>').style.display='none'; document.getElementById('display_box_<?php echo esc_attr($wf_id); ?>').style.display='flex';"><?php esc_html_e( 'Cancel', 'skaaa-logic-engine' ); ?></button>
                            </form>
                        </div>
                    </td>
                    <td style="padding: 16px; vertical-align: middle; color: #64748b;">
                        <?php if($node_count > 0): ?>
                            <span style="background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600;"><?php echo $node_count; ?> Trạm</span>
                        <?php else: ?>
                            <span style="background:#f1f5f9; color:#94a3b8; padding:2px 8px; border-radius:12px; font-size:12px;"><?php esc_html_e( 'Empty', 'skaaa-logic-engine' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 16px; vertical-align: middle;">
                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px; white-space:nowrap;">
                            <a href="?page=skaaa-logic-engine&view=builder&workflow_id=<?php echo esc_attr($wf_id); ?>" class="button" style="color: #4f46e5; border-color: #4f46e5; font-weight: 500; margin:0; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-edit" style="font-size:16px; width:16px; height:16px;"></span> <?php esc_html_e( 'Design Flow', 'skaaa-logic-engine' ); ?></a>
                            
                            <a href="<?php echo esc_url(rest_url('skaaa-logic/v1/export-blueprint?workflow_id=' . $wf_id . '&_wpnonce=' . wp_create_nonce('wp_rest'))); ?>" target="_blank" class="button" style="color: #0f172a; border-color: #cbd5e1; margin:0; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-download" style="font-size:16px; width:16px; height:16px;"></span> <?php esc_html_e( 'Export', 'skaaa-logic-engine' ); ?></a>

                            <form method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('<?php esc_attr_e( 'WARNING: This action will delete the stream\'s Json Graph. If the Frontend is still submitting, the cable will break. Are you sure?', 'skaaa-logic-engine' ); ?>')">
                                <?php wp_nonce_field('skaaa_logic_nonce'); ?>
                                <input type="hidden" name="skaaa_logic_action" value="delete">
                                <input type="hidden" name="workflow_id" value="<?php echo esc_attr($wf_id); ?>">
                                <button type="submit" class="button" style="color: #ef4444; border-color: #fca5a5; background: #fef2f2; margin:0; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-trash" style="font-size:16px; width:16px; height:16px;"></span> <?php esc_html_e( 'Delete', 'skaaa-logic-engine' ); ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
