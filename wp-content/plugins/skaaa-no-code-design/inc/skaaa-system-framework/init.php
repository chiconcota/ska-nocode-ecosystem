<?php
/**
 * Skaaa System Framework - Load Balancer Bootstrapper
 * Version: 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Chống require lại nếu hệ thống đã nạp version cao hơn.
if ( defined( 'SKAAA_SYSTEM_FRAMEWORK' ) && version_compare( SKAAA_SYSTEM_FRAMEWORK, '1.0.0', '>=' ) ) {
    return;
}
define( 'SKAAA_SYSTEM_FRAMEWORK', '1.0.0' );
define( 'SKAAA_SYSTEM_FRAMEWORK_PATH', dirname( __FILE__ ) );
define( 'SKAAA_SYSTEM_FRAMEWORK_URL', plugin_dir_url( __FILE__ ) );

require_once SKAAA_SYSTEM_FRAMEWORK_PATH . '/includes/class-framework-ui.php';
require_once SKAAA_SYSTEM_FRAMEWORK_PATH . '/includes/class-ai-proxy.php';
require_once SKAAA_SYSTEM_FRAMEWORK_PATH . '/includes/class-system-cache.php';
require_once SKAAA_SYSTEM_FRAMEWORK_PATH . '/includes/class-dependency-manager.php';

add_action( 'plugins_loaded', function() {
    \Skaaa_System_Framework\Framework_UI::get_instance();
    \Skaaa_System_Framework\AI_Proxy::get_instance();
    \Skaaa_System_Framework\System_Cache::get_instance();
    \Skaaa_System_Framework\Dependency_Manager::get_instance();
}, 99 );
