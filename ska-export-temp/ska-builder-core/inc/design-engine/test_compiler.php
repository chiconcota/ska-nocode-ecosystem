<?php
define( 'ABSPATH', 1 );
function add_filter() {}
function apply_filters($tag, $value) { return $value; }
function get_option($name, $default = []) { return $default; }
require_once __DIR__ . '/class-tailwind-config.php';
require_once __DIR__ . '/class-tailwind-color-registry.php';
require_once __DIR__ . '/class-tailwind-compiler.php';

$compiler = new \Ska\Builder\Design\Tailwind_Compiler();
$classes = 'z-50 relative';
$result = $compiler->compile_classes($classes);

file_put_contents(__DIR__ . '/test_output.txt', $result['css'] . "\n\nUNRESOLVED:\n" . print_r($result['unresolved'], true));
echo "SAVED TO " . __DIR__ . '/test_output.txt';
