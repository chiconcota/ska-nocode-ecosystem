<?php
/**
 * Plugin Name: Ska No-Code Design
 * Description: The UI design engine and blocks framework for Ska No-Code Builder.
 * Version: 1.1.0
 * Author: Ska Team
 * Text Domain: ska-no-code-design
 *
 * @package Ska_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

// Define Constants
define( 'SKA_DESIGN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKA_DESIGN_URL', plugin_dir_url( __FILE__ ) );

// Load Text Domain
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'ska-no-code-design', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// 4. Load System Framework (Drop-in, Must load early)
require_once SKA_DESIGN_PATH . 'inc/ska-system-framework/init.php';

/**
 * Load Modules
 */
function ska_no_code_design_init() {
	// 0. Utilities
	require_once SKA_DESIGN_PATH . 'inc/utils/class-assets.php';
	require_once SKA_DESIGN_PATH . 'inc/utils/class-dynamic-data.php';

	// 1. Data Engine
	require_once SKA_DESIGN_PATH . 'inc/data-engine/data-engine.php';

	// 2. Logic Engine
	require_once SKA_DESIGN_PATH . 'inc/logic-engine/logic-engine.php';

	// 3. Design Engine
	require_once SKA_DESIGN_PATH . 'inc/design-engine/design-engine.php';
    
    // Dashboard Integration (Module Hooking)
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-dashboard-integration.php';
    if ( class_exists( 'Ska_Design_Dashboard_Integration' ) ) {
        Ska_Design_Dashboard_Integration::init();
    }

    // Design Tokens (Brand & Theme Options)
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-design-tokens-api.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-design-tokens-compiler.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-design-tokens-ui.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-organisms-api.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-organism-editor.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-design-workspace-ui.php';
    
    // Theme Builder
    require_once SKA_DESIGN_PATH . 'inc/theme-builder/class-ska-theme-builder.php';

    if ( class_exists( '\Ska\Design\Api\Design_Tokens_API' ) ) {
        \Ska\Design\Api\Design_Tokens_API::get_instance();
    }
    if ( class_exists( '\Ska\Design\Api\Design_Tokens_Compiler' ) ) {
        \Ska\Design\Api\Design_Tokens_Compiler::get_instance();
    }
    if ( class_exists( '\Ska\Design\Api\Organisms_API' ) ) {
        \Ska\Design\Api\Organisms_API::get_instance();
    }
    if ( class_exists( '\Ska\Design\Admin\Design_Tokens_UI' ) ) {
        \Ska\Design\Admin\Design_Tokens_UI::get_instance();
    }
    if ( class_exists( '\Ska\Design\Admin\Design_Workspace_UI' ) ) {
        \Ska\Design\Admin\Design_Workspace_UI::get_instance();
    }
    if ( class_exists( '\Ska\Builder\Design\Organism_Editor' ) ) {
        \Ska\Builder\Design\Organism_Editor::instance();
    }
    if ( class_exists( '\Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder' ) ) {
        $theme_builder = new \Ska_No_Code_Design\Theme_Builder\Ska_Theme_Builder();
        $theme_builder->init();
    }

	// 4. Blocks System
	require_once SKA_DESIGN_PATH . 'blocks/init.php';

	// 5. Demo Content (Dev only)
	require_once SKA_DESIGN_PATH . 'inc/demo-content.php';
}

add_action( 'plugins_loaded', 'ska_no_code_design_init' );

// Tạm thời migrate Database Sys Presets
add_action('admin_init', function() {
    if ( ! get_option('ska_temp_fix_sys_presets_db_3', false) ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ska_data_sys_presets';
        
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN type VARCHAR(255) DEFAULT ''");
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN value LONGTEXT");
        
        $dict = get_option('ska_data_dictionary', []);
        if (isset($dict[$table])) {
            unset($dict[$table]['json_content']);
            $dict[$table]['type'] = array(
                'label' => 'Type', 
                'type' => 'enum', 
                'options' => 'token_color, token_font, token_spacing, token_radius, token_shadow, preset_typography, preset_component'
            );
            $dict[$table]['value'] = array(
                'label' => 'Value', 
                'type' => 'long_text', 
                'options' => ''
            );
            update_option('ska_data_dictionary', $dict);
        }
        
        $wpdb->query("ALTER TABLE {$table} DROP COLUMN json_content");
        
        update_option('ska_temp_fix_sys_presets_db_3', true);
    }
});
