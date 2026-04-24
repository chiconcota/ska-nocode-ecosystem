<?php
require 'wp-load.php';
$post = get_page_by_path('doctor-form', OBJECT, 'post') ?: get_page_by_path('doctor-form', OBJECT, 'page');
if ($post) {
    echo "POST CONTENT:\n";
    echo $post->post_content;
}
