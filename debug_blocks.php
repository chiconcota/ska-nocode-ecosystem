<?php
require_once('wp-load.php');
$post = get_post(447); // Wait, "post.php?post=447&action=edit"
$blocks = parse_blocks( $post->post_content );
foreach ($blocks as $b) {
    echo "Block: " . $b['blockName'] . "\n";
    if (isset($b['attrs']['skaDynamicBinding'])) {
        echo "Binding: " . print_r($b['attrs']['skaDynamicBinding'], true) . "\n";
    }
}
