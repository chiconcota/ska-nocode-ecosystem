<?php 
require_once 'wp-load.php'; 
$html = '<select><option>test</option></select>'; 
$block = ['blockName' => 'ska-builder/select', 'attrs' => ['skaDynamicBinding' => '{{#foreach ska_data_doctors.qualifications}}']]; 
$dynamic = Ska_Dynamic_Content::instance(); 
echo $dynamic->parse_ska_blocks($html, $block); 
