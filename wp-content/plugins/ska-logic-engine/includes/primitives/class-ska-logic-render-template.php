<?php
defined( 'ABSPATH' ) || exit;

/**
 * Node: Render Template
 * Extract HTML from ska_data_sys_organisms and inject data via SkaFX.
 */
class Ska_Logic_Render_Template implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        // Lấy template_html từ cấu hình, fallback về raw_template hoặc organism_id cũ để giữ tính tương thích ngược tối đa
        $template_html = isset( $config['template_html'] ) ? $config['template_html'] : '';
        if ( empty( $template_html ) ) {
            if ( isset( $config['raw_template'] ) && !empty( $config['raw_template'] ) ) {
                $template_html = $config['raw_template'];
            } elseif ( isset( $config['organism_id'] ) && !empty( $config['organism_id'] ) ) {
                $template_html = $config['organism_id'];
            }
        }

        // Bước 1: Nội suy cấp 1 (giải quyết biến chứa HTML thô từ payload, ví dụ: {{payload.db_result.html_content}})
        $html_pass1 = $this->evaluate_template( $template_html, $payload );

        // Bước 2: Nội suy cấp 2 (giải quyết các biến dữ liệu con nằm bên trong HTML đó, ví dụ: {{name}})
        $rendered_html = $this->evaluate_template( $html_pass1, $payload );

        // Biên dịch block WordPress Gutenberg (nếu có) thành mã HTML thật
        if ( function_exists( 'do_blocks' ) ) {
            $rendered_html = do_blocks( $rendered_html );
        }

        // Lưu kết quả vào biến chỉ định (mặc định là payload.rendered_template)
        $result_var = isset( $config['result_var'] ) && !empty( $config['result_var'] ) ? trim( $config['result_var'] ) : 'payload.rendered_template';
        
        // Ghi dữ liệu vào đường dẫn của $result_var trong $payload
        $this->set_nested_value( $payload, $result_var, $rendered_html );

        return [
            'payload' => $payload,
            'port'    => 'main'
        ];
    }

    /**
     * Nội suy template string có chứa {{ ... }} thông qua SkaFX_Engine
     */
    private function evaluate_template( $template_string, $payload ) {
        if ( empty( $template_string ) ) {
            return '';
        }

        // Nếu chuỗi không chứa {{, trả về nguyên bản
        if ( strpos( $template_string, '{{' ) === false ) {
            return $template_string;
        }

        return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($matches) use ($payload) {
            $expression = trim($matches[1]);
            try {
                $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expression, $payload );
                if ( isset($eval_result['last_val']) ) {
                    $val = $eval_result['last_val'];
                    if ( is_array( $val ) || is_object( $val ) ) {
                        return wp_json_encode( $val );
                    }
                    return (string) $val;
                }
            } catch ( Exception $e ) {
                error_log('SkaFX Eval Error in Render Template Node: ' . $e->getMessage());
                return '';
            }
            return '';
        }, $template_string);
    }

    /**
     * Hàm set value vào payload dựa trên dot notation (vd: payload.html.content)
     */
    private function set_nested_value( &$array, $path, $value ) {
        $keys = explode('.', $path);
        
        // Nếu bắt đầu bằng 'payload', loại bỏ nó vì ta đang xét bên trong $payload
        if ( $keys[0] === 'payload' ) {
            array_shift($keys);
        }

        $current = &$array;
        foreach ( $keys as $i => $key ) {
            if ( $i === count($keys) - 1 ) {
                $current[$key] = $value;
            } else {
                if ( !isset($current[$key]) || !is_array($current[$key]) ) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }
}
