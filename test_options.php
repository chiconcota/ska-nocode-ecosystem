<?php
require_once "wp-load.php";
echo "GLOBAL_CSS_CACHE:\n";
echo get_option("ska_builder_global_css_cache", "EMPTY");
echo "\n=====================\n";
// Let's also check a sample post's meta to see the button CSS
$args = array('post_type' => 'post', 'posts_per_page' => 1);
$posts = get_posts($args);
if (!empty($posts)) {
    $post_id = $posts[0]->ID;
    echo "POST $post_id CSS:\n";
    echo get_post_meta($post_id, '_ska_page_css', true);
}
