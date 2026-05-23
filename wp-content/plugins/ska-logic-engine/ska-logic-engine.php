<?php
/**
 * Plugin Name: Ska Logic Engine
 * Description: Bộ não Logic "Ska-xi măng" của Hệ sinh thái Ska Builder. Xử lý Workflow, định tuyến Data và các tính năng Automation Node-based.
 * Version: 1.0.0
 * Author: Ska Ecosystem
 * Text Domain: ska-logic-engine
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit; // Bảo mật chuẩn WordPress

// Khai báo Const
define( 'SKA_LOGIC_ENGINE_VERSION', '1.0.0' );
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

