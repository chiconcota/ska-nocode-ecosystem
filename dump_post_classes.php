<?php
require_once 'wp-load.php';

$core = \Ska\Builder\Design\Core::instance();
$classes = $core->style_manager->scan_post_classes(919);
echo "Classes for post 919: " . substr($classes, 0, 500) . "\n";
