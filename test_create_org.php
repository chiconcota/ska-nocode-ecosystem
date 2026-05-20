<?php
require 'wp-load.php';

$org_data = array(
    'type' => 'single',
    'name' => 'Test Form Rich Text',
    'json_content' => wp_json_encode(array()),
    'html_content' => '<!-- wp:ska-builder/form-rich-text {"field":"noi_dung","label":"Nội dung test"} /-->'
);

$org_id = apply_filters('ska_data_insert_record', false, $org_data, 'ska_data_sys_organisms');

echo "Created Organism: $org_id";
