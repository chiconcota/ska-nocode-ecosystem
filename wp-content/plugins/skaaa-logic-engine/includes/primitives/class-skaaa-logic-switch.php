<?php
defined( 'ABSPATH' ) || exit;

class Skaaa_Logic_Switch implements Skaaa_Logic_Node {

    public function execute( $payload, $config ) {
        $routes = $config['routes'] ?? [];

        if ( empty( $routes ) || ! is_array( $routes ) ) {
            // Nếu không cấu hình route nào, đi nhánh mặc định
            return ['payload' => $payload, 'port' => 'default'];
        }

        foreach ( $routes as $route ) {
            $expression = $route['expression'] ?? '';
            $route_id   = $route['id'] ?? '';

            if ( empty( trim( $expression ) ) || empty( $route_id ) ) {
                continue;
            }

            try {
                $eval_result = \Skaaa\Logic\SkaaaFX\SkaaaFX_Engine::execute( $expression, $payload );
                $is_true = !empty( $eval_result['last_val'] );

                if ( $is_true ) {
                    return ['payload' => $payload, 'port' => $route_id];
                }
            } catch ( Exception $e ) {
                error_log( 'SkaaaFX Eval Error in Switch Router Node: ' . $e->getMessage() );
                // Lỗi đánh giá biểu thức -> Bỏ qua route này, tiếp tục kiểm tra các route sau
            }
        }

        // Không có route nào match
        return ['payload' => $payload, 'port' => 'default'];
    }
}
