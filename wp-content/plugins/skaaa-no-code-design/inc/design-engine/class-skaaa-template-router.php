<?php
/**
 * Skaaa Template Router
 * Theme Builder Phase 4: Intercepts WP template_include to serve
 * Header/Footer/Archive templates directly from Flat Tables.
 * 
 * Circuit Breaker: This class only runs if both Skaaa Data Pro and Skaaa Logic Engine are active.
 */

namespace Skaaa\Builder\Design;

defined( 'ABSPATH' ) || exit;

class Skaaa_Template_Router {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Fallback kiểm tra an toàn: Nếu không thỏa mãn dependency, ngắt mạch ngay.
        if ( ! \Skaaa_System_Framework\Dependency_Manager::is_theme_builder_supported() ) {
            return;
        }

        $this->init_hooks();
    }

    private function init_hooks() {
        // Intercept template resolution
        add_filter( 'template_include', array( $this, 'intercept_template' ), 99 );
    }

    /**
     * Intercept the standard WordPress template loading process.
     */
    public function intercept_template( $template ) {
        // Placeholder cho logic Theme Builder:
        // 1. Kiểm tra URL/Context (Condition Matcher).
        // 2. Kéo template ID từ Flat Table (skaaa_data_sys_organisms với type = template).
        // 3. Nếu tìm thấy, gán template = custom file (hoặc chặn trả về custom HTML).
        
        // Hiện tại: Bypass (trả về template gốc)
        return $template;
    }
}
