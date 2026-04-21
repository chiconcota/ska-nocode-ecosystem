<?php
require_once dirname(__FILE__) . '/wp-load.php';

// Simulate block attributes
$attributes = array(
    'organismId' => '4'
);

$organism_id = sanitize_text_field( $attributes['organismId'] );

// Locate cache file
$upload_dir = wp_upload_dir();
$cache_file = trailingslashit( $upload_dir['basedir'] ) . 'ska-data/organisms.json';

if ( ! file_exists( $cache_file ) ) {
    echo "NO CACHE FILE";
    exit;
}

$file_contents = file_get_contents( $cache_file );
$organisms = json_decode( $file_contents, true );

$found_html = '';
foreach ( $organisms as $org ) {
    if ( isset( $org['id'] ) && (string) $org['id'] === (string) $organism_id ) {
        $found_html = isset( $org['html_content'] ) ? $org['html_content'] : '';
        break;
    }
}

echo "FOUND HTML CONTENT:\n";
echo "----------\n";
echo $found_html . "\n";
echo "----------\n";

echo "OUTPUTTING do_blocks():\n";
echo "----------\n";

$output = do_blocks( $found_html );
if ( empty( trim( $output ) ) ) {
     echo "WARNING: do_blocks() returned EMPTY string.";
} else {
     echo $output;
}
echo "\n----------\n";
