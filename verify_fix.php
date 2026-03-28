<?php
/**
 * Verification Script for Tailwind Compiler and Style Manager Fixes
 */

// Load WordPress environment if possible, or mock it
define('ABSPATH', dirname(__FILE__) . '/');
require_once dirname(__FILE__) . '/wp-content/plugins/ska-builder-core/inc/design-engine/class-tailwind-compiler.php';
require_once dirname(__FILE__) . '/wp-content/plugins/ska-builder-core/inc/design-engine/class-style-manager.php';

use Ska\Builder\Design\Tailwind_Compiler;
use Ska\Builder\Design\Style_Manager;

// Mock dependencies
function add_filter($tag, $callback) {}
function add_action($tag, $callback) {}
function apply_filters($tag, $value) { return $value; }
function get_option($option, $default = false) { return $default; }

$compiler = new Tailwind_Compiler();
$manager = new Style_Manager();

echo "--- Testing Tailwind_Compiler ---\n";

// 1. Test slate-100
$result_slate = $compiler->compile_classes('bg-slate-100');
if (strpos($result_slate['css'], '#f1f5f9') !== false) {
    echo "✅ [PASS] bg-slate-100 resolved correctly to #f1f5f9\n";
} else {
    echo "❌ [FAIL] bg-slate-100 did not resolve correctly.\n";
    print_r($result_slate);
}

// 2. Test opacity support for standard colors
$result_opacity = $compiler->compile_classes('bg-slate-900/50');
if (strpos($result_opacity['css'], 'rgba(15, 23, 42, 0.5)') !== false) {
    echo "✅ [PASS] bg-slate-900/50 resolved correctly with opacity\n";
} else {
    echo "❌ [FAIL] bg-slate-900/50 did not resolve correctly.\n";
    print_r($result_opacity);
}

// 3. Test multi-palette
$result_emerald = $compiler->compile_classes('text-emerald-500');
if (strpos($result_emerald['css'], '#10b981') !== false) {
    echo "✅ [PASS] text-emerald-500 resolved correctly to #10b981\n";
} else {
    echo "❌ [FAIL] text-emerald-500 did not resolve correctly.\n";
}

echo "\n--- Testing Style_Manager ---\n";

// 4. Test tailwindClasses scanning
$mock_blocks = [
    [
        'blockName' => 'ska-builder/container',
        'attrs' => [
            'tailwindClasses' => 'bg-slate-100 p-8'
        ]
    ],
    [
        'blockName' => 'ska-builder/text',
        'attrs' => [
            'content' => '<div class="text-blue-500">Hello</div>',
            'className' => 'mt-4'
        ]
    ]
];

// We need to use reflection or access if it's private, but extract_block_classes is private.
// However, scan_post_classes uses it.
// Let's mock get_post and parse_blocks... wait, too complex for a quick script.
// I'll just check if I can access the method via reflection.

$reflection = new ReflectionClass($manager);
$method = $reflection->getMethod('extract_block_classes');
$method->setAccessible(true);

$classes = [];
$method->invokeArgs($manager, [$mock_blocks, &$classes]);

$classes_str = implode(' ', $classes);
echo "Extracted classes: $classes_str\n";

if (strpos($classes_str, 'bg-slate-100') !== false && strpos($classes_str, 'text-blue-500') !== false) {
    echo "✅ [PASS] Style_Manager successfully extracted tailwindClasses and content classes\n";
} else {
    echo "❌ [FAIL] Style_Manager missed some classes.\n";
}

echo "\n--- Verification Finished ---\n";
