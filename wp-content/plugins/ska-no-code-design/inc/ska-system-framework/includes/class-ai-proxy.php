<?php
namespace Ska_System_Framework;

defined( 'ABSPATH' ) || exit;

class AI_Proxy {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_ska_generate_blueprint', array( $this, 'handle_generate_blueprint' ) );
        add_action( 'ska_system_dashboard_extensions', array( $this, 'render_ai_extension_card' ) );
    }

    public function render_ai_extension_card() {
        ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all flex flex-col items-start h-full">
            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-4 border border-purple-100">
                <span class="material-symbols-outlined text-[22px]">auto_awesome</span>
            </div>
            <h3 class="m-0 border-0 pt-0 pb-0 text-base font-bold text-slate-800 mb-2">Ska AI Architect</h3>
            <p class="text-sm text-slate-500 mb-5 flex-1">Cấu hình Gemini API Key và hiệu chỉnh System Prompts để giúp AI thấu hiểu kiến trúc dữ liệu riêng của bạn tốt hơn.</p>
            <button onclick="alert('Module cài đặt AI đang được phát triển cho phase tiếp theo.')" class="border-0 bg-transparent cursor-pointer p-0 text-purple-600 font-medium text-sm hover:text-purple-800 transition-colors mt-auto flex items-center gap-1">
                Tùy chỉnh AI <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </button>
        </div>
        <?php
    }

    public function handle_generate_blueprint() {
        check_ajax_referer( 'ska_ai_blueprint_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
        }

        $prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
        if ( empty( $prompt ) ) {
            wp_send_json_error( array( 'message' => 'Thiếu thông tin prompt.' ) );
        }

        $api_key = get_option( 'ska_gemini_api_key', '' );
        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => 'Thiếu Gemini API Key. Vui lòng cấu hình trường ska_gemini_api_key trong wp_options.' ) );
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=' . $api_key;

        $system_prompt = "Bạn là Ska AI Overseer, kiến trúc sư trưởng của nền tảng Ska App Builder. 
        Người dùng sẽ cung cấp một ý tưởng hệ thống. 
        Nhiệm vụ của bạn là thiết kế kiến trúc cơ sở dữ liệu dựa trên mô hình Flat Tables (không dùng wp_postmeta).
        Mỗi bảng (table) cần có tiền tố ska_data_.
        Mỗi trường (field) phải thuộc các loại dữ liệu: text, longtext, number, boolean, date, image, relation_id.";

        $payload = array(
            'contents' => array(
                array( 'parts' => array( array( 'text' => $prompt ) ) )
            ),
            'systemInstruction' => array(
                'parts' => array( array( 'text' => $system_prompt ) )
            ),
            'generationConfig' => array(
                'responseMimeType' => 'application/json',
                'responseSchema' => array(
                    'type' => 'OBJECT',
                    'properties' => array(
                        'appName' => array( 'type' => 'STRING', 'description' => 'Tên ứng dụng chuyên nghiệp' ),
                        'appDescription' => array( 'type' => 'STRING', 'description' => 'Mô tả mục đích của ứng dụng' ),
                        'tables' => array(
                            'type' => 'ARRAY',
                            'description' => 'Danh sách các bảng Flat Tables',
                            'items' => array(
                                'type' => 'OBJECT',
                                'properties' => array(
                                    'tableName' => array( 'type' => 'STRING', 'description' => 'Semantic slug của bảng, ví dụ: ska_data_spa_customers' ),
                                    'label' => array( 'type' => 'STRING', 'description' => 'Tên hiển thị thân thiện, ví dụ: Khách hàng Spa' ),
                                    'fields' => array(
                                        'type' => 'ARRAY',
                                        'items' => array(
                                            'type' => 'OBJECT',
                                            'properties' => array(
                                                'key' => array( 'type' => 'STRING', 'description' => 'Khóa của trường' ),
                                                'label' => array( 'type' => 'STRING', 'description' => 'Tên hiển thị' ),
                                                'type' => array( 'type' => 'STRING', 'description' => 'Loại dữ liệu (text, number, boolean, date, relation_id)' ),
                                                'required' => array( 'type' => 'BOOLEAN' )
                                            ),
                                            'required' => array( 'key', 'label', 'type' )
                                        )
                                    )
                                ),
                                'required' => array( 'tableName', 'label', 'fields' )
                            )
                        )
                    ),
                    'required' => array( 'appName', 'tables' )
                )
            )
        );

        $response = wp_remote_post( $url, array(
            'body'    => wp_json_encode( $payload ),
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
            $err_msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Lỗi không xác định từ API';
            wp_send_json_error( array( 'message' => $err_msg ) );
        }

        $text = isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ? $data['candidates'][0]['content']['parts'][0]['text'] : '';
        if ( empty( $text ) ) {
            wp_send_json_error( array( 'message' => 'AI không trả về kết quả hợp lệ.' ) );
        }

        wp_send_json_success( array( 'blueprint' => json_decode( $text, true ) ) );
    }
}
