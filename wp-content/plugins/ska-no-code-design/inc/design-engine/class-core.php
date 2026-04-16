<?php
/**
 * Design Engine Core Class
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined( 'ABSPATH' ) || exit;

/**
 * Class Core
 */
class Core {

	/**
	 * Instance of this class.
	 *
	 * @var Core
	 */
	protected static $instance = null;

	/**
	 * Style Manager instance.
	 *
	 * @var Style_Manager
	 */
	public $style_manager;

	/**
	 * Tailwind Compiler instance.
	 *
	 * @var Tailwind_Compiler
	 */
	public $compiler;

	/**
	 * Return an instance of this class.
	 *
	 * @return Core A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Core constructor.
	 */
	private function __construct() {
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Initialize components.
	 */
	private function init_components() {
		$this->style_manager = new Style_Manager();
		$this->compiler      = new Tailwind_Compiler();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Enqueue Tailwind CDN for Frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tailwind_frontend' ) );
        
        // Enqueue Editor Helper
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
        
        // Enqueue Material Icons
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_design_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_design_assets' ) );
        
        // Force injection with high priority to ensure it comes after theme styles
        add_action( 'wp_head', array( $this, 'inject_tailwind_cdn' ), 999 );
        
        // Architecture Scope: Add .ska-builder to body to enable JIT Scoped CSS
        add_filter( 'body_class', array( $this, 'add_ska_builder_class' ) );
        
        // Note: HTML attribute injection (inject_html_attributes) was moved to blocks/init.php to avoid duplicates.
	}



    /**
     * Add .ska-builder class to body.
     */
    public function add_ska_builder_class( $classes ) {
        $classes[] = 'ska-builder';
        return $classes;
    }

    /**
     * Enqueue global design assets.
     */
    public function enqueue_design_assets() {
        wp_enqueue_style( 'material-symbols-outlined', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap', array(), '1.0.0' );
    }

	/**
	 * Enqueue Alpine Store (Alpine.js is enqueued globally on-demand in blocks/init.php)
	 */
	public function enqueue_tailwind_frontend() {
        // Enqueue local Alpine.js (so we can attach inline scripts)
        wp_enqueue_script( 'ska-alpine' );
        
        // Alpine Store for UI Ecosystem Shared State
        $alpine_store_script = "
        document.addEventListener('alpine:init', () => {
            Alpine.store('skaBuilder', {
                data: {},
                get(key) { return this.data[key]; },
                set(key, value) { this.data[key] = value; }
            });
        });
        ";
        wp_add_inline_script( 'ska-alpine', $alpine_store_script, 'before' );
	}

    public function enqueue_editor_assets() {
        wp_enqueue_style( 'material-symbols-outlined', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap', array(), '1.0.0' );
        wp_enqueue_script(
            'ska-editor-helper',
            SKA_DESIGN_URL . 'assets/js/ska-editor-helper.js',
            array(),
            time(),
            true
        );

        // Pass theme config to JS
        wp_localize_script( 'ska-editor-helper', 'skaEditorConfig', array(
            'editorResetCss'  => \Ska\Builder\Design\Tailwind_Config::get_core_reset_css(),
            'brandColorsJson' => \Ska\Builder\Design\Tailwind_Color_Registry::get_custom_colors(),
            // Vẫn giữ brandColorsCss fallback nếu file JS chưa được cập nhật cache.
            'brandColorsCss'  => \Ska\Builder\Design\Tailwind_Config::get_core_reset_css() . "\n" . \Ska\Builder\Design\Tailwind_Color_Registry::get_brand_colors_css(),
        ) );

        // Enqueue Bridge Parser if enabled
        if ( get_option( 'ska_bridge_enabled', 'yes' ) === 'yes' ) {
            wp_enqueue_script(
                'ska-bridge-parser',
                SKA_DESIGN_URL . 'assets/js/html-to-blocks.js',
                array( 'wp-blocks', 'wp-data', 'wp-util' ),
                time(),
                true
            );
        }
    }

    /**
     * Inject Tailwind CDN or Local JIT CSS.
     */
    public function inject_tailwind_cdn() {
        if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

        // 2. Frontend Context -> Local JIT Compilation (Production)
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return;
        }

        // Scan blocks for classes
        $classes = $this->style_manager->scan_post_classes( $post_id );
        
        if ( $classes ) {
            // Compile classes to CSS
            $result = $this->compiler->compile_classes( $classes );
            $css    = $result['css'];
            $unresolved = $result['unresolved'];
            
            // Output Compiled CSS
            if ( $css ) {
                $scanned_debug = '/* Scanned Classes: ' . esc_html( implode( ', ', array_unique( array_filter( explode( ' ', $classes ) ) ) ) ) . ' */';
                echo "<style id='ska-jit-styles'>\n{$scanned_debug}\n{$css}\n</style>" . "\n";
            }

            // HYBRID FALLBACK: If there are unresolved classes, inject CDN
            if ( ! empty( $unresolved ) ) {
                if ( ! has_action( 'wp_footer', '__return_true' ) ) {
                    echo "<!-- Ska Hybrid Fallback: Unresolved classes found (" . implode(', ', $unresolved) . ") -->\n";
                    echo '<script src="https://cdn.tailwindcss.com"></script>' . "\n";
                    echo '<script>window.tailwind = { config: { important: false, corePlugins: { preflight: false } } }</script>' . "\n";
                    add_action( 'wp_footer', '__return_true' );
                }
            }
        }
    }
}
