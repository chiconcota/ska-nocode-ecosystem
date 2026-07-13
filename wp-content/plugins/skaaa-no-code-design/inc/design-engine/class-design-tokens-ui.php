<?php
namespace Skaaa\Design\Admin;

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
        add_action( 'admin_menu', [ $this, 'register_menu' ], 20 );
    }

    public function register_menu() {
        $hook = add_submenu_page(
            'skaaa-system-dashboard',
            __( 'Brand, Font & Theme Options', 'skaaa-no-code-design' ),
            __( 'Theme Options', 'skaaa-no-code-design' ),
            'manage_options',
            'skaaa-design-tokens',
            [ $this, 'render_page' ],
            1
        );

        add_action( "load-{$hook}", function() {
            wp_enqueue_media();
        } );
    }

    public function render_page() {
        // Gọi file view
        require_once SKAAA_DESIGN_PATH . 'inc/design-engine/views/design-tokens-app.php';
    }
}
