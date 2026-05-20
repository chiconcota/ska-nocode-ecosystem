<?php
define('ABSPATH', 1);
function add_filter(){}
require 'wp-content/plugins/ska-no-code-design/inc/design-engine/class-tailwind-color-registry.php';
require 'wp-content/plugins/ska-no-code-design/inc/design-engine/class-tailwind-config.php';
require 'wp-content/plugins/ska-no-code-design/inc/design-engine/class-tailwind-compiler.php';
$compiler = new \Ska\Builder\Design\Tailwind_Compiler();
$res = $compiler->compile_classes('hidden md:grid grid-cols-[2fr_repeat(3,minmax(0,1fr))_40px] gap-4 items-center p-4 border-b border-slate-200 bg-slate-50/50');
print_r($res);
