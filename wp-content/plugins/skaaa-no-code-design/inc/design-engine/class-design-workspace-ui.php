<?php
namespace Skaaa\Design\Admin;

defined( 'ABSPATH' ) || exit;

class Design_Workspace_UI {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ], 15 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets( $hook ) {
        // Chỉ nạp ở trang Workspace, Theme Builder, và các trang Editor
        if ( strpos( $hook, 'skaaa-design-workspace' ) === false && 
             strpos( $hook, 'skaaa-theme-builder' ) === false && 
             strpos( $hook, 'skaaa-organism-editor' ) === false && 
             strpos( $hook, 'skaaa-theme-builder-editor' ) === false ) {
            return;
        }

        wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', [], null, false );
        wp_add_inline_script( 'tailwindcss', '
            window.tailwind = window.tailwind || {};
            window.tailwind.config = {
                corePlugins: {
                    preflight: false,
                }
            };
        ', 'before' );
        wp_enqueue_script( 'alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], null, true );
        wp_enqueue_style( 'material-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200', [], null );
    }

    public function register_menu() {
        add_submenu_page(
            'skaaa-system-dashboard', 
            'Design Workspace',     
            __( 'Skaaa Organisms Manager', 'skaaa-no-code-design' ),     
            'manage_options',       
            'skaaa-design-workspace', 
            [ $this, 'render_page' ], 
            2 
        );
    }

    public function render_page() {
        $view_path = plugin_dir_path( __FILE__ ) . 'views/workspace-panel.php';
        if ( file_exists( $view_path ) ) {
            require_once $view_path;
        } else {
            echo __( '<div class=\"wrap\"><h2>Error: Workspace interface not found.</h2></div>', 'skaaa-no-code-design' );
        }
    }
}
