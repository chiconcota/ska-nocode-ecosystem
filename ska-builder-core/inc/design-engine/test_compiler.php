<?php
define( 'ABSPATH', 1 );
function add_filter() {}
function apply_filters($tag, $value) { return $value; }
function get_option($name, $default = []) { return $default; }
require_once __DIR__ . '/class-tailwind-compiler.php';

$compiler = new \Ska\Builder\Design\Tailwind_Compiler();
$classes = 'bg-white text-primary px-10 py-5 rounded-xl font-bold text-lg hover:bg-slate-50 transition-colors shadow-xl';
$result = $compiler->compile_classes($classes);

file_put_contents(__DIR__ . '/test_output.txt', $result['css'] . "\n\nUNRESOLVED:\n" . print_r($result['unresolved'], true));
echo "SAVED TO " . __DIR__ . '/test_output.txt';
