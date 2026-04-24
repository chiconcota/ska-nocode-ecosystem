<?php
require 'wp-load.php';
remove_filter('render_block', array( Ska_Dynamic_Content::instance(), 'filter_render_block' ), 90);
$post = get_page_by_path('doctor-form', OBJECT, 'post') ?: get_page_by_path('doctor-form', OBJECT, 'page');
if ($post) {
    $html = do_blocks($post->post_content);
    file_put_contents(__DIR__ . '/doctor_form_raw.html', $html);
    echo "Done";
}
