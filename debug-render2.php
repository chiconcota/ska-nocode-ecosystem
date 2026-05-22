<?php
/**
 * Test render logic of ska-loop
 */
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

header('Content-Type: text/plain; charset=utf-8');

$actual_table_name = 'wp_ska_data_app_quan_ly_dat_phong_khach_san_phong_khach_san';
$context = [
    'phong_dat' => '[{"id":1,"label":"vung tu","url":"http://ska-core-builder.local/lich-dat-phong/1/"}]'
];

$matched_template_html = '<div class="cell">Tên phòng: {{phong_dat}}</div>';

$hydrated_html = preg_replace_callback( '/\{\{\s*([a-zA-Z0-9_\.\$\-]+)\s*\}\}|\[\s*([a-zA-Z0-9_\.\$\-]+)\s*\]|%7B%7B\s*([a-zA-Z0-9_\.\$\-]+)\s*%7D%7D|%5B\s*([a-zA-Z0-9_\.\$\-]+)\s*%5D/', function( $matches ) use ( $context, $actual_table_name ) {
    $raw_key = '';
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($matches[$i])) {
            $raw_key = trim($matches[$i]);
            break;
        }
    }
    
    $parts = explode( '.', $raw_key );
    $field_name = end( $parts );
    
    echo "Matching raw_key: $raw_key, field_name: $field_name\n";
    
    if ( isset( $context[ $field_name ] ) ) {
        $val = $context[ $field_name ];
        echo "Value in context: " . var_export($val, true) . "\n";
        if ( is_string( $val ) ) {
            $val_clean = trim( stripslashes( $val ) );
            echo "val_clean: " . var_export($val_clean, true) . "\n";
            echo "Starts with [{: " . (str_starts_with( $val_clean, '[{' ) ? 'yes' : 'no') . "\n";
            if ( str_starts_with( $val_clean, '[{' ) ) {
                $decoded = json_decode( $val_clean, true );
                echo "Decoded: " . var_export($decoded, true) . "\n";
                if ( is_array( $decoded ) ) {
                    $labels = array_column( $decoded, 'label' );
                    echo "Labels: " . var_export($labels, true) . "\n";
                    if ( ! empty( $labels ) ) {
                        $res = esc_html( implode( ', ', $labels ) );
                        echo "Result: $res\n";
                        return $res;
                    }
                }
            }
        }
        return esc_html( (string) $val );
    }
    return $matches[0];
}, $matched_template_html );

echo "Hydrated HTML: $hydrated_html\n";
