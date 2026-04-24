<?php
require 'wp-load.php';
$post = get_page_by_path('doctor-form', OBJECT, 'post') ?: get_page_by_path('doctor-form', OBJECT, 'page');
if ($post) {
    $html = do_blocks($post->post_content);
    file_put_contents('doctor_form_output.html', $html);
    echo "Saved to doctor_form_output.html";
}
