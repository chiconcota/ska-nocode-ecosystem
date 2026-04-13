<?php
/**
 * Ska System Framework - Load Balancer Bootstrapper
 * Version: 1.0.0
 */
defined( 'ABSPATH' ) || exit;

// Chống require lại nếu hệ thống đã nạp version cao hơn.
if ( defined( 'SKA_SYSTEM_FRAMEWORK' ) && version_compare( SKA_SYSTEM_FRAMEWORK, '1.0.0', '>=' ) ) {
    return;
}
define( 'SKA_SYSTEM_FRAMEWORK', '1.0.0' );
define( 'SKA_SYSTEM_FRAMEWORK_PATH', dirname( __FILE__ ) );
define( 'SKA_SYSTEM_FRAMEWORK_URL', plugin_dir_url( __FILE__ ) );

require_once SKA_SYSTEM_FRAMEWORK_PATH . '/includes/class-framework-ui.php';
require_once SKA_SYSTEM_FRAMEWORK_PATH . '/includes/class-ai-proxy.php';

add_action( 'plugins_loaded', function() {
    \Ska_System_Framework\Framework_UI::get_instance();
    \Ska_System_Framework\AI_Proxy::get_instance();
}, 99 );
