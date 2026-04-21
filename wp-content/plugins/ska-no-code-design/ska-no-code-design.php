<?php
/**
 * Plugin Name: Ska No-Code Design
 * Description: The UI design engine and blocks framework for Ska No-Code Builder.
 * Version: 1.0.0
 * Author: Ska Team
 * Text Domain: ska-no-code-design
 *
 * @package Ska_No_Code_Design
 */

defined( 'ABSPATH' ) || exit;

// Define Constants
define( 'SKA_DESIGN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKA_DESIGN_URL', plugin_dir_url( __FILE__ ) );

// 4. Load System Framework (Drop-in, Must load early)
require_once SKA_DESIGN_PATH . 'inc/ska-system-framework/init.php';

/**
 * Load Modules
 */
function ska_no_code_design_init() {
	// 0. Utilities
	require_once SKA_DESIGN_PATH . 'inc/utils/class-assets.php';

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
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-design-tokens-ui.php';
    require_once SKA_DESIGN_PATH . 'inc/design-engine/class-organisms-api.php';
    if ( class_exists( '\Ska\Design\Api\Design_Tokens_API' ) ) {
        \Ska\Design\Api\Design_Tokens_API::get_instance();
    }
    if ( class_exists( '\Ska\Design\Api\Organisms_API' ) ) {
        \Ska\Design\Api\Organisms_API::get_instance();
    }
    if ( class_exists( '\Ska\Design\Admin\Design_Tokens_UI' ) ) {
        \Ska\Design\Admin\Design_Tokens_UI::get_instance();
    }

	// 4. Blocks System
	require_once SKA_DESIGN_PATH . 'blocks/init.php';

	// 5. Demo Content (Dev only)
	require_once SKA_DESIGN_PATH . 'inc/demo-content.php';
}

add_action( 'plugins_loaded', 'ska_no_code_design_init' );
