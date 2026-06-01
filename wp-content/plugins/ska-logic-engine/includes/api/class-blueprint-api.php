<?php
defined('ABSPATH') || exit;

class Ska_Blueprint_API {

    public static function register_routes() {
        register_rest_route('ska-logic/v1', '/import-blueprint', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'handle_import'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);

        register_rest_route('ska-logic/v1', '/export-blueprint', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'handle_export'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);
    }

    public static function handle_import(WP_REST_Request $request) {
        $workflow_id = sanitize_title($request->get_param('workflow_id'));
        $name = sanitize_text_field($request->get_param('name'));
        if (empty($name)) {
            $name = $workflow_id;
        }

        // Đọc nội dung file đính kèm
        $file_params = $request->get_file_params();
        $graph_data = [];

        if (isset($file_params['blueprint_file']) && $file_params['blueprint_file']['error'] === UPLOAD_ERR_OK) {
            $file_content = file_get_contents($file_params['blueprint_file']['tmp_name']);
            $graph_data = json_decode($file_content, true);
        } else {
            // Hỗ trợ dự phòng nếu gửi thẳng body JSON qua REST API (Dành cho bot)
            $graph_data = $request->get_param('graph');
            if (is_string($graph_data)) {
                $graph_data = json_decode($graph_data, true);
            }
        }

        if (!is_array($graph_data) || !isset($graph_data['nodes']) || !isset($graph_data['edges'])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Invalid Blueprint format. Must contain nodes and edges.', 'ska-logic-engine')
            ]);
        }

        global $wpdb;
        $table_workflows = $wpdb->prefix . 'ska_data_sys_workflows';
        $node_count = is_array($graph_data['nodes']) ? count($graph_data['nodes']) : 0;
        $graph_json = wp_json_encode($graph_data);

        // Kiểm tra xem workflow_id đã tồn tại chưa
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_workflows}` WHERE workflow_id = %s", $workflow_id));

        if ($exists) {
            // Overwrite
            $wpdb->update(
                $table_workflows,
                [
                    'name'       => $name,
                    'graph'      => $graph_json,
                    'node_count' => $node_count
                ],
                ['workflow_id' => $workflow_id]
            );
        } else {
            // Insert mới
            $wpdb->insert(
                $table_workflows,
                [
                    'workflow_id' => $workflow_id,
                    'name'        => $name,
                    'app_id'      => 'ska_system',
                    'graph'       => $graph_json,
                    'node_count'  => $node_count,
                    'status'      => 'active'
                ]
            );
        }

        // Cập nhật Cache
        if (class_exists('Ska_Logic_Core')) {
            Ska_Logic_Core::sync_workflow_ids_cache();
        }

        return rest_ensure_response([
            'success' => true,
            'message' => __('Blueprint imported successfully!', 'ska-logic-engine')
        ]);
    }

    public static function handle_export(WP_REST_Request $request) {
        $workflow_id = sanitize_title($request->get_param('workflow_id'));
        if (empty($workflow_id)) {
            wp_die(__('Missing workflow_id.', 'ska-logic-engine'), __('Error', 'ska-logic-engine'), ['response' => 400]);
        }

        global $wpdb;
        $table_workflows = $wpdb->prefix . 'ska_data_sys_workflows';
        $graph_json = $wpdb->get_var($wpdb->prepare("SELECT graph FROM `{$table_workflows}` WHERE workflow_id = %s", $workflow_id));

        if ($graph_json === null) {
            wp_die(__('Workflow not found.', 'ska-logic-engine'), __('Error', 'ska-logic-engine'), ['response' => 404]);
        }

        // Format lại JSON cho đẹp
        $graph_data = json_decode($graph_json, true);
        if (!is_array($graph_data)) {
            $graph_data = ['nodes' => [], 'edges' => []];
        }

        $pretty_json = json_encode($graph_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Kích hoạt Download File
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="workflow-' . $workflow_id . '.json"');
        header('Content-Length: ' . strlen($pretty_json));
        
        echo $pretty_json;
        exit;
    }
}
