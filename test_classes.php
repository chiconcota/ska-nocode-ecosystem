<?php
require 'wp-load.php';
$page = get_page_by_path('test-tinh-nang-alpinejs');
if ($page) {
    echo "Found page ID: " . $page->ID . "\n";
    $blocks = parse_blocks($page->post_content);
    // var_dump($blocks);
    $sm = new \Ska\Builder\Design\Style_Manager();
    $classes = $sm->scan_post_classes($page->ID);
    echo "Classes extracted:\n";
    echo $classes;
} else {
    echo "Page not found";
}
