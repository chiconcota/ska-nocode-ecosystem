<?php
require_once 'wp-load.php';

$post = get_post(8);
if (!$post) {
    echo "Post 8 not found";
    exit;
}

$blocks = parse_blocks($post->post_content);
echo json_encode($blocks, JSON_PRETTY_PRINT);
