<?php
require_once 'wp-load.php';
$post = get_post(919);
echo "Post Type: " . $post->post_type . "\n";
echo "Post Name: " . $post->post_name . "\n";
