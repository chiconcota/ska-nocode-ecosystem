<?php
defined( 'ABSPATH' ) || exit;

class Skaaa_Condition_Node implements Skaaa_Logic_Node {

    public function execute( $payload, $config ) {
        $expression = $config['expression'] ?? '';
        
        if ( empty( trim( $expression ) ) ) {
            // Nếu không có điều kiện, mặc định đi nhánh true
            return ['payload' => $payload, 'port' => 'true'];
        }

        try {
            $eval_result = \Skaaa\Logic\SkaaaFX\SkaaaFX_Engine::execute( $expression, $payload );
            // Kiểm tra giá trị boolean của kết quả biểu thức
            $is_true = !empty( $eval_result['last_val'] );
        } catch ( Exception $e ) {
            error_log( 'SkaaaFX Eval Error in Condition Node: ' . $e->getMessage() );
            $is_true = false; // Lỗi thì đi nhánh false cho an toàn
        }

        $port = $is_true ? 'true' : 'false';

        return ['payload' => $payload, 'port' => $port];
    }
}
