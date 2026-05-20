<?php
require_once __DIR__ . '/../../../wp-load.php';
if (class_exists('\Ska\Design\Api\Organisms_API')) {
    \Ska\Design\Api\Organisms_API::get_instance()->export_physical_cache();
    echo "Cache flushed!";
} else {
    echo "Class not found!";
}
