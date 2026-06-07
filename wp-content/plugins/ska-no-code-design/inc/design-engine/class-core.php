<?php
/**
 * Design Engine Core Class
 *
 * @package Ska_Builder_Core
 */

namespace Ska\Builder\Design;

defined('ABSPATH') || exit;

/**
 * Class Core
 */
class Core
{

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
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Core constructor.
     */
    private function __construct()
    {
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Initialize components.
     */
    private function init_components()
    {
        $this->style_manager = new Style_Manager();
        $this->compiler = new Tailwind_Compiler();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks()
    {
        // Enqueue Tailwind CDN for Frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_tailwind_frontend'));

        // Enqueue Editor Helper
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));

        // Enqueue Material Icons
        add_action('wp_enqueue_scripts', array($this, 'enqueue_design_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_design_assets'));

        // Force injection with high priority to ensure it comes after theme styles
        add_action('wp_head', array($this, 'inject_tailwind_cdn'), 999);
        
        // Anti-FOUC script for Dark Mode (Very high priority)
        add_action('wp_head', array($this, 'inject_anti_fouc_script'), 0);

        // Architecture Scope: Add .ska-builder to body to enable JIT Scoped CSS
        add_filter('body_class', array($this, 'add_ska_builder_class'));
        add_filter('admin_body_class', array($this, 'add_ska_builder_admin_class'));

        // Note: HTML attribute injection (inject_html_attributes) was moved to blocks/init.php to avoid duplicates.
    }



    /**
     * Add .ska-builder class to body.
     */
    public function add_ska_builder_class($classes)
    {
        $classes[] = 'ska-builder';
        return $classes;
    }

    /**
     * Add .ska-builder class to admin body.
     */
    public function add_ska_builder_admin_class($classes)
    {
        return $classes . ' ska-builder';
    }

    /**
     * Enqueue global design assets.
     */
    public function enqueue_design_assets()
    {
        wp_enqueue_style('material-symbols-outlined', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap', array(), '1.0.0');

        // Enqueue Vanilla CSS Variables (Physical Cache)
        $upload_dir = wp_upload_dir();
        $tokens_css_path = trailingslashit($upload_dir['basedir']) . 'ska-data/tokens.css';
        $tokens_css_url = trailingslashit($upload_dir['baseurl']) . 'ska-data/tokens.css';
        
        if (file_exists($tokens_css_path)) {
            wp_enqueue_style('ska-design-tokens-vars', $tokens_css_url, array(), filemtime($tokens_css_path));
        }
    }

    /**
     * Enqueue Alpine Store (Alpine.js is enqueued globally on-demand in blocks/init.php)
     */
    public function enqueue_tailwind_frontend()
    {
        // Enqueue local Alpine.js (so we can attach inline scripts)
        wp_enqueue_script('ska-alpine');

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
        wp_add_inline_script('ska-alpine', $alpine_store_script, 'before');
    }

    public function enqueue_editor_assets()
    {
        wp_enqueue_style('material-symbols-outlined', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap', array(), '1.0.0');
        wp_enqueue_script(
            'ska-editor-helper',
            SKA_DESIGN_URL . 'assets/js/ska-editor-helper.js',
            array(),
            time(),
            true
        );

        // Pass theme config to JS
        wp_localize_script('ska-editor-helper', 'skaEditorConfig', array(
            'editorResetCss' => \Ska\Builder\Design\Tailwind_Config::get_core_reset_css(),
            'brandColorsJson' => \Ska\Builder\Design\Tailwind_Color_Registry::get_custom_colors(),
            // Vẫn giữ brandColorsCss fallback nếu file JS chưa được cập nhật cache.
            'brandColorsCss' => \Ska\Builder\Design\Tailwind_Config::get_core_reset_css() . "\n" . \Ska\Builder\Design\Tailwind_Color_Registry::get_brand_colors_css(),
            'dummyPostId' => get_option('ska_organism_dummy_post_id'),
            'adminUrl' => admin_url(),
            'isDataProActive' => class_exists('\Ska_System_Framework\Dependency_Manager') && \Ska_System_Framework\Dependency_Manager::is_data_pro_active(),
        ));

        // Inject Organisms Cache to JS (Zero-Query) - Conditionally via Graceful Fallback
        $organisms_data = array();

        // Only load if Ska Data Pro is active
        if (class_exists('\Ska_System_Framework\Dependency_Manager') && \Ska_System_Framework\Dependency_Manager::is_data_pro_active()) {
            $upload_dir = wp_upload_dir();
            $cache_file = trailingslashit($upload_dir['basedir']) . 'ska-data/organisms.json';

            if (file_exists($cache_file)) {
                $file_contents = file_get_contents($cache_file);
                if (!empty($file_contents)) {
                    $decoded = json_decode($file_contents, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        foreach ($decoded as $org) {
                            if (isset($org['id'])) {
                                $organisms_data[$org['id']] = array(
                                    'id'       => $org['id'],
                                    'name'     => !empty($org['name']) ? $org['name'] : $org['id'],
                                    'category' => !empty($org['category']) ? $org['category'] : '',
                                );
                            }
                        }
                    }
                }
            }
        }

        wp_add_inline_script(
            'ska-editor-helper',
            'window.skaOrganismsCache = ' . wp_json_encode($organisms_data) . ';',
            'before'
        );

        // Enqueue Bridge Parser if enabled
        if (get_option('ska_bridge_enabled', 'yes') === 'yes') {
            wp_enqueue_script(
                'ska-bridge-parser',
                SKA_DESIGN_URL . 'assets/js/html-to-blocks.js',
                array('wp-blocks', 'wp-data', 'wp-util'),
                time(),
                true
            );
        }
    }

    /**
     * Inject Anti-FOUC script for Dark Mode.
     */
    public function inject_anti_fouc_script()
    {
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        echo "<script id='ska-anti-fouc'>
            (function() {
                try {
                    var skaTheme = localStorage.getItem('ska_dark_mode');
                    if (skaTheme === 'true') {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>\n";
    }

    /**
     * Inject Tailwind CDN or Local JIT CSS.
     */
    public function inject_tailwind_cdn()
    {
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $classes_to_scan = '';

        // 1. Frontend Context -> Local JIT Compilation (Production)
        if ( is_home() || is_archive() || is_front_page() || is_search() ) {
            global $wp_query;
            if ( ! empty( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
                foreach ( $wp_query->posts as $p ) {
                    $classes_to_scan .= $this->style_manager->scan_post_classes( $p->ID ) . ' ';
                }
            }
        } else {
            $post_id = get_the_ID();
            if ($post_id) {
                $classes_to_scan .= $this->style_manager->scan_post_classes($post_id) . ' ';
            }
        }

        // 2. Scan Theme Builder Organisms if Active
        global $ska_active_theme_organisms;
        if (!empty($ska_active_theme_organisms) && is_array($ska_active_theme_organisms)) {
            foreach ($ska_active_theme_organisms as $org_id) {
                $classes_to_scan .= $this->style_manager->scan_organism_classes($org_id) . ' ';
            }
        }

        // Cho phép các plugin khác đóng góp các class cần scan qua filter
        $classes_to_scan = apply_filters( 'ska_design_classes_to_scan', $classes_to_scan );

        if (!empty(trim($classes_to_scan))) {
            // Compile classes to CSS
            $result = $this->compiler->compile_classes($classes_to_scan);
            $css = $result['css'];
            $unresolved = $result['unresolved'];

            // Output Compiled CSS
            if ($css) {
                $scanned_debug = '/* Scanned Classes: ' . esc_html(implode(', ', array_unique(array_filter(explode(' ', $classes_to_scan))))) . ' */';
                echo "<style id='ska-jit-styles'>\n{$scanned_debug}\n{$css}\n</style>" . "\n";
            }

            // HYBRID FALLBACK: Removed per Zero CDN Policy.
            if (!empty($unresolved)) {
                echo "<!-- Unresolved classes (Zero CDN Policy Active): " . esc_html(implode(', ', $unresolved)) . " -->\n";
            }
        }
    }
}
