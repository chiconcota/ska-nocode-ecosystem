<?php
defined( 'ABSPATH' ) || exit;

class Ska_Logic_Set_Data implements Ska_Logic_Node {

    public function execute( $payload, $config ) {
        $assignments = $config['assignments'] ?? [];
        if ( ! is_array( $assignments ) ) {
            return [ 'payload' => $payload, 'port' => 'main' ];
        }

        foreach ( $assignments as $assignment ) {
            $key   = $assignment['key'] ?? '';
            $value = $assignment['value'] ?? '';

            if ( empty( $key ) ) continue;

            $final_value = $value;

            // Smart Evaluation: Tự động nhận diện biểu thức hoặc Template
            $is_template = ( strpos( $value, '{{' ) !== false );
            $is_expression = ( strpos( $value, '[' ) !== false || preg_match('/[+\-*\/]/', $value) );

            if ( $is_template || $is_expression ) {
                try {
                    // Nếu là template {{ ... }}, trích xuất lõi
                    $expr = $value;
                    if ( $is_template ) {
                        $expr = trim(str_replace(['{{', '}}'], '', $value));
                    }
                    
                    $eval_result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $expr, $payload );
                    $final_value = $eval_result['last_val'] ?? $value;
                } catch ( \Exception $e ) {
                    error_log( 'Ska Logic Set Data: Lỗi evaluate - ' . $e->getMessage() );
                    // Fallback giữ nguyên value gốc
                }
            }

            $payload[ $key ] = $final_value;
        }

        return ['payload' => $payload, 'port' => 'main'];
    }
}
