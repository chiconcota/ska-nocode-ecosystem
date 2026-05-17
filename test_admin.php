<?php
require 'wp-load.php';
$u = get_user_by('login', 'admin');
if ($u) {
    wp_set_current_user($u->ID);
}
ob_start();
require 'index.php';
$out = ob_get_clean();
echo $out;
