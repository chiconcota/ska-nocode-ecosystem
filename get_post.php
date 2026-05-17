<?php
require 'wp-load.php';
$post = get_page_by_path('test-content-padding');
echo $post->post_content;
