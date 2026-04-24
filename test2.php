<?php
require 'wp-load.php';
$post = get_page_by_path('doctor-form', OBJECT, 'post');
if (!$post) {
    $post = get_page_by_path('doctor-form', OBJECT, 'page');
}
if ($post) {
    echo "POST CONTENT:\n" . $post->post_content . "\n";
} else {
    echo "Post not found.\n";
}
