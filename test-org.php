<?php
require 'wp-load.php';
global $wpdb;
$post = $wpdb->get_var('SELECT post_content FROM wp_posts WHERE ID=980');
$blocks = parse_blocks($post);
$classes = [];
$manager = \Ska\Design\Engine\Core::get_instance()->style_manager;

$reflection = new ReflectionClass($manager);
$method = $reflection->getMethod('extract_block_classes');
$method->setAccessible(true);
$method->invokeArgs($manager, [$blocks, &$classes]);
print_r($classes);
