<?php
namespace Ska\Design\Admin;

defined( 'ABSPATH' ) || exit;

class Design_Tokens_UI {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        // Đăng ký như 1 trang con của hệ thống nhưng không hiển thị trên menu chính (parent_slug = null/false)
        // Thay vì null, add_submenu_page vào một slug không tồn tại sẽ ẩn menu (cách 1)
        // Hoặc add vào ska-system-dashboard nhưng dùng CSS ẩn đi. Cách xịn nhất WordPress là parent_slug = null.
        add_submenu_page(
            'options.php', // Hidden from main menu but valid parent (fixes strip_tags null warning)
            __( 'Brand, Font & Theme Options', 'ska-no-code-design' ),
            __( 'Design Tokens', 'ska-no-code-design' ),
            'manage_options',
            'ska-design-tokens',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        // Enqueue media (nếu cần chọn ảnh brand)
        wp_enqueue_media();
        
        // Gọi file view
        require_once SKA_DESIGN_PATH . 'inc/design-engine/views/design-tokens-app.php';
    }
}
