<?php
/**
 * Plugin Name: Ska Logic Engine
 * Description: The "Ska-cement" Logic Brain of the Ska Builder Ecosystem. Handles workflows, data routing, and node-based automation features.
 * Version: 1.3.0
 * Author: Ska Ecosystem
 * Text Domain: ska-logic-engine
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit; // Bảo mật chuẩn WordPress

// Khai báo Const
define( 'SKA_LOGIC_ENGINE_VERSION', '1.3.0' );
define( 'SKA_LOGIC_ENGINE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKA_LOGIC_ENGINE_URL', plugin_dir_url( __FILE__ ) ); // Phục vụ gọi JS UI nếu cần

// Load Text Domain
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'ska-logic-engine', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Tuân thủ Load Order Bubble: Giống như Data Pro, 
// không đẻ Class loạn xạ khi mạng lưới Ecosystem (Ska Builder Core / Data Pro) chưa nạp xong.
add_action( 'plugins_loaded', 'ska_logic_engine_init', 15 ); // Trễ một nhịp (15) để chắc chắn Data Pro (10) đã sẵn sàng

function ska_logic_engine_init() {
    // Gọi tệp Quản Đốc Core
    require_once SKA_LOGIC_ENGINE_DIR . 'includes/class-ska-logic-core.php';
    
    // Khởi tạo
    Ska_Logic_Core::instance();
}

