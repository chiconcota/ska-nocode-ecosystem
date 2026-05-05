<?php
require 'wp-load.php';
$post = get_page_by_path('test-client-response');
if($post) {
    $post->post_content = str_replace('@click="$store.appState.openModal()"', '', $post->post_content);
    wp_update_post($post);
    echo 'Updated';
}
