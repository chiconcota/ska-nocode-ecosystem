<?php
defined('ABSPATH') || exit;

// Lấy danh sách bảng từ Skaaa Data Pro
$data_dictionary = get_option('skaaa_data_dictionary', []);
$skaaa_apps = get_option('skaaa_data_apps', []);
$available_tables = [];
foreach ($data_dictionary as $table_name => $meta) {
    if (isset($meta['__table_info'])) {
        $app_group = 'Skaaa Smart Object Table';
        $app_id = isset($meta['__table_info']['app_id']) ? $meta['__table_info']['app_id'] : '';

        if ($app_id && $app_id !== 'uncategorized') {
            if (isset($skaaa_apps[$app_id])) {
                $app_group = 'APP: ' . mb_strtoupper($skaaa_apps[$app_id]['name'], 'UTF-8');
            } else {
                $app_group = 'APP: ' . strtoupper(str_replace('app_', '', $app_id));
            }
        } elseif (preg_match('/skaaa_data_app_([^_]+)_/', $table_name, $m)) {
            $app_group = 'APP: ' . strtoupper($m[1]);
        }

        global $wpdb;
        $columns = [];
        
        $wpdb->suppress_errors(true);
        $physical_columns = $wpdb->get_results("SHOW COLUMNS FROM `{$table_name}`");
        $wpdb->suppress_errors(false);
        
        if (!empty($physical_columns)) {
            foreach ($physical_columns as $p_col) {
                $col_key = $p_col->Field;
                
                // Map friendly names from dictionary if available
                $options = null;
                if (isset($meta[$col_key]) && is_array($meta[$col_key])) {
                    $name = isset($meta[$col_key]['title']) ? $meta[$col_key]['title'] : $col_key;
                    $type = isset($meta[$col_key]['type']) ? $meta[$col_key]['type'] : 'text';
                    if (isset($meta[$col_key]['options'])) {
                        $options = $meta[$col_key]['options'];
                    } elseif (isset($meta[$col_key]['choices'])) {
                        $options = $meta[$col_key]['choices'];
                    }
                } else {
                    $name = $col_key;
                    if ($col_key === 'id') $name = 'ID';
                    if ($col_key === 'created_at') $name = __( 'Created date (created_at)', 'skaaa-logic-engine' );
                    
                    $type = 'text';
                    if (strpos(strtolower($p_col->Type), 'int') !== false) $type = 'number';
                    if (strpos(strtolower($p_col->Type), 'datetime') !== false || strpos(strtolower($p_col->Type), 'timestamp') !== false) $type = 'datetime';
                }
                
                $columns[] = [
                    'id' => $col_key,
                    'name' => $name,
                    'type' => $type,
                    'options' => $options
                ];
            }
        } else {
            // Fallback to dictionary if physical table fetch fails
            foreach ($meta as $col_key => $col_data) {
                if ($col_key !== '__table_info' && is_array($col_data)) {
                    $columns[] = [
                        'id' => $col_key,
                        'name' => isset($col_data['title']) ? $col_data['title'] : $col_key,
                        'type' => isset($col_data['type']) ? $col_data['type'] : 'text',
                        'options' => isset($col_data['options']) ? $col_data['options'] : (isset($col_data['choices']) ? $col_data['choices'] : null)
                    ];
                }
            }
        }

        $available_tables[] = [
            'id' => $table_name,
            'name' => $meta['__table_info']['name'],
            'app_group' => $app_group,
            'columns' => $columns
        ];
    }
}

// Lấy danh sách các Cục Node khả dụng từ Registry trung tâm
$available_nodes = Skaaa_Node_Registry::instance()->get_all_nodes();

global $wpdb;
$table_name = $wpdb->prefix . 'skaaa_data_sys_workflows';

// Đọc danh sách workflow_id dựa trên Dev Mode
$dev_mode = get_option( 'skaaa_system_dev_mode', '1' ) === '1';
$all_workflow_ids = [];
if ( $dev_mode ) {
    $wpdb->suppress_errors( true );
    $all_workflow_ids = $wpdb->get_col( "SELECT workflow_id FROM `{$table_name}`" );
    $wpdb->suppress_errors( false );
} else {
    $all_workflow_ids = get_option( 'skaaa_logic_workflow_ids', [] );
}
if (!is_array($all_workflow_ids)) {
    $all_workflow_ids = [];
}

// Nếu người dùng chọn từ dropdown, load đúng ID đó, nếu không load cái đầu tiên (hoặc default)
$current_wf_id = isset($_GET['workflow_id']) ? sanitize_text_field($_GET['workflow_id']) : (empty($all_workflow_ids) ? 'default_form_submit' : $all_workflow_ids[0]);

// Query riêng graph của workflow hiện tại từ MySQL
$graph_json = $wpdb->get_var($wpdb->prepare("SELECT graph FROM `{$table_name}` WHERE workflow_id = %s", $current_wf_id));
$saved_graph = empty($graph_json) ? '[]' : $graph_json;

?>

<style>
    /* Keep a clean layout for the app wrapper */
    .skaaa-admin-wrapper {
        margin: 20px 20px 20px 0;
    }
    .skaaa-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        padding: 12px 20px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
</style>

<div class="skaaa-admin-wrapper">
    <div class="skaaa-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="?page=skaaa-logic-engine&view=list" class="button"><span class="dashicons dashicons-arrow-left-alt2" style="margin-top:4px;"></span> Trở ra</a>
            <h2 style="margin:0; font-size: 18px; color: #0f172a; font-weight: 600;">
                <span class="dashicons dashicons-networking" style="color: #3b82f6;"></span>
                DAG Automation Builder <span style="color:#64748b; font-weight: 400; font-size: 14px;">(ID: <?php echo esc_html($current_wf_id); ?>)</span>
            </h2>
        </div>
        <form method="POST" id="skaaaWorkflowForm" style="margin: 0;">
            <?php wp_nonce_field('skaaa_logic_nonce'); ?>
            <input type="hidden" name="skaaa_logic_save" value="1">
            <input type="hidden" name="skaaa_form_id" value="<?php echo esc_attr($current_wf_id); ?>">
            <input type="hidden" name="skaaa_linear_graph" id="skaaaLinearGraphInput" value="">
            <button type="submit" class="button button-primary" style="background:#059669; border-color:#059669; font-size: 14px; padding: 0 16px; display:flex; align-items:center; gap:6px;">
                <span class="dashicons dashicons-saved" style="margin-top:0;"></span> Lưu Đồ Thị
            </button>
        </form>
    </div>

    <!-- React Flow Root -->
    <div id="skaaa-dag-root"></div>
</div>

<script>
    window.SKAAA_DAG_CONTEXT = {
        DATA_NONCE: "<?php echo esc_js(wp_create_nonce('skaaa_data_nonce')); ?>",
        AVAILABLE_TABLES: <?php echo wp_json_encode($available_tables); ?>,
        AVAILABLE_NODES: <?php echo wp_json_encode($available_nodes); ?>,
        CURRENT_GRAPH: <?php echo $saved_graph; ?>,
        CURRENT_WF_ID: "<?php echo esc_js($current_wf_id); ?>"
    };
</script>

<?php
// Tự động enqueue Vite bundles
$plugin_url = plugin_dir_url(dirname(__DIR__));
wp_enqueue_style('skaaa-dag-builder-style', $plugin_url . 'assets/js/admin-dag-builder.bundle.css', [], time());
wp_enqueue_script('skaaa-dag-builder-script', $plugin_url . 'assets/js/admin-dag-builder.bundle.js', ['wp-element', 'wp-i18n'], time(), true);
?>