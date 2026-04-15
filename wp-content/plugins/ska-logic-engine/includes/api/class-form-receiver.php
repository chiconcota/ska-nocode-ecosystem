<?php
defined('ABSPATH') || exit;

class Ska_Form_Receiver
{

    public static function register_routes()
    {
        register_rest_route('ska-logic/v1', '/submit', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'handle_submit'],
            // @todo: Tạm mở cửa cho Khách tham quan Form nhập liệu. Cần Nonce-auth ở Phase sau!
            'permission_callback' => '__return_true',
        ]);

        // Cung cấp Lược đồ Database cho hệ sinh thái Editor (TributeJS)
        register_rest_route('ska-logic/v1', '/schema', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'handle_schema'],
            'permission_callback' => function () {
                return current_user_can('edit_posts'); // Phân quyền Editor
            }
        ]);
    }

    public static function handle_submit(WP_REST_Request $request)
    {
        $data = $request->get_params(); // Payload gốc Frontend đẩy sang
        $form_id = $data['ska_form_id'] ?? 'unknown_workflow';
        if (is_array($form_id)) {
            $form_id = trim(sanitize_text_field(reset($form_id)));
        }

        // 1. Phễu Khử Độc: Chặn XSS Injection cơ bản nhất 
        // Lớp Kho còn 1 bộ vệ sinh SQL nữa
        $clean_data = [];
        foreach ($data as $k => $v) {
            // Loại bỏ cái tham số route rác của REST API
            if ($k === 'rest_route')
                continue;
            $clean_data[sanitize_text_field($k)] = is_string($v) ? sanitize_text_field($v) : $v;
        }

        // 2. Chuyển Lệnh Sang Băng Chuyền (The Runner)
        // Áp dụng Nguyên Tắc Vàng = Cấm gọi Class Runner trực tiếp!
        // Truyền Hook vào ko trung, ai hứng thì tính (Ở đây Logic Core đã đăng ký add_filter đón lõng r)
        $completed_payload = apply_filters('ska_logic_run_pipeline', $clean_data, $form_id);

        // 2.1 CẢNH BÁO BẢO MẬT/DEV MODE: Check xem Workflow có tồn tại thật không? Tránh fake success
        $workflows = get_option('ska_logic_simple_workflows', []);
        if (!isset($workflows[$form_id])) {
            return rest_ensure_response([
                'success' => false,
                'message' => "Sai Mật Mã: Không tìm thấy Logic id '{$form_id}'. Vui lòng check thuộc tính data-ska-action= trên chữ HTML."
            ]);
        }

        // 2.2 CHECK DỮ LIỆU ĐỔ VỠ TỪ NODE DATA ACTION (Bên Ska Data chối từ nhận Data)
        if (isset($completed_payload['_latest_insert']) && $completed_payload['_latest_insert']['result'] === false) {
            return rest_ensure_response([
                'success' => false,
                'message' => "Lỗi Chặn Ở Kho: Mapping Cột sai hoặc Dữ liệu rỗng, Bảng {$completed_payload['_latest_insert']['table_name']} từ chối tiếp nhận!"
            ]);
        }

        // 3. Phun Phản Hồi Về Cho Thẻ <Form> Ở Lớp Vỏ Giao Diện
        return rest_ensure_response([
            'success' => true,
            'message' => 'Nhiệm màu! Data đã lướt an toàn qua các bộ Node vào trúng Lớp Kho!',
            'data' => $completed_payload
        ]);
    }

    public static function handle_schema(WP_REST_Request $request)
    {
        $dictionary = get_option('ska_data_dictionary', []);
        $tribute_data = [];
        global $wpdb;
        $global_prefix = $wpdb->prefix . 'ska_data_';

        foreach ($dictionary as $table_id => $table_config) {
            // Lấy App Slug từ __table_info
            $table_info = isset($table_config['__table_info']) ? $table_config['__table_info'] : [];
            $app_slug = isset($table_info['app_id']) && !empty($table_info['app_id']) ? $table_info['app_id'] : 'uncategorized';
            $table_label = isset($table_info['name']) ? $table_info['name'] : $table_id;

            // Xử lý tên bảng vật lý: bóc bỏ `wp_ska_data_` và bóc bỏ luôn `app_id` để được model_slug thuần túy
            $table_slug = str_replace($global_prefix, '', $table_id);

            // Lọc luôn cả app_id prefix ra khỏi tên bảng (nếu app_id chèn vào theo chuẩn mới)
            if ($app_slug !== 'uncategorized') {
                $app_slug_clean = sanitize_key($app_slug) . '_';
                if (strpos($table_slug, $app_slug_clean) === 0) {
                    $table_slug = substr($table_slug, strlen($app_slug_clean));
                }
            }

            // Duyệt từng Cột (Mọi key không phải __table_info đều là cột)
            foreach ($table_config as $col_slug => $col_data) {
                if ($col_slug === '__table_info' || $col_slug === 'id' || $col_slug === 'created_at') {
                    continue;
                }

                $full_notation = "{$app_slug}.{$table_slug}.{$col_slug}";
                $label = isset($col_data['label']) ? $col_data['label'] : $col_slug;

                $tribute_data[] = [
                    'key' => "{$full_notation} - {$label} ({$table_label})", // Cho user tìm bằng cả tiếng Việt
                    'value' => "[{$full_notation}]" // Giá trị thực dán vào ô Text
                ];
            }
        }

        return rest_ensure_response($tribute_data);
    }
}
