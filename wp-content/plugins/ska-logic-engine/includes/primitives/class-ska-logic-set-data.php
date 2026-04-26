<?php
defined( 'ABSPATH' ) || exit;

class Ska_Logic_Set_Data implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        if ( ! isset( $config['assignments'] ) || ! is_array( $config['assignments'] ) ) {
            return $payload; // Hoặc `return ['payload' => $payload, 'port' => 'main'];` nếu theo chuẩn mới, nhưng DAG tự bắt reference payload rồi.
        }

        foreach ( $config['assignments'] as $assignment ) {
            if ( empty( $assignment['key'] ) ) {
                continue;
            }

            $key   = sanitize_text_field( $assignment['key'] );
            $value = $assignment['value'] ?? '';

            // Nếu giá trị có chứa biểu thức SkaFX
            if ( strpos( $value, '{{' ) !== false ) {
                try {
                    // Extract expression from {{ ... }}
                    $expression = trim(str_replace(['{{', '}}'], '', $value));
                    $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expression, $payload );
                    $final_value = $eval_result['last_val'] ?? null;
                } catch ( Exception $e ) {
                    // Nếu lỗi parse, lưu giá trị nguyên thuỷ hoặc bỏ qua
                    $final_value = null;
                    error_log( 'SkaFX Eval Error in Set Data Node: ' . $e->getMessage() );
                }
            } else {
                $final_value = $value;
            }

            // Gán/Ghi đè giá trị vào mảng payload
            $payload[ $key ] = $final_value;
        }

        return ['payload' => $payload, 'port' => 'main'];
    }
}
