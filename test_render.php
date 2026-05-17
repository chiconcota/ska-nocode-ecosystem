<?php
require_once 'wp-load.php';

// Mimic WP environment for this test
global $wp_query;
$wp_query->set('ska_id', '2');
$wp_query->set('ska_portal', 'giao-vien');

$table_name = 'app_courses_courses'; // The user usually selects the short name in the editor
$actual_table_name = 'wp_ska_data_' . $table_name;

$filters = [
    [
        'column' => 'teacher_id',
        'operator' => 'json_contains',
        'value' => '{url:id}'
    ]
];

$args = [];
if ( ! empty( $filters ) && is_array( $filters ) ) {
    $first_filter = $filters[0];
    if ( ! empty( $first_filter['column'] ) ) {
        $filter_val = $first_filter['value'] ?? '';

        // Dynamic Resolution từ URL Parameter (vd: {url:id})
        if ( is_string( $filter_val ) && preg_match( '/^\{url:(.+)\}$/', $filter_val, $url_matches ) ) {
            $url_param = $url_matches[1];
            // Get from Router Context or $_GET
            if ( $url_param === 'id' && (get_query_var('ska_id') || get_query_var('app_record_id')) ) {
                $filter_val = get_query_var('ska_id') ? get_query_var('ska_id') : get_query_var('app_record_id');
            } else {
                $filter_val = isset($_GET[$url_param]) ? sanitize_text_field($_GET[$url_param]) : '';
            }
        }
        
        $args['filter_field'] = $first_filter['column'];
        $args['filter_op']    = $first_filter['operator'] ?? 'eq';
        $args['filter_val']   = $filter_val;
    }
}

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $actual_table_name, $args, 10 );
echo json_encode(['args' => $args, 'rows' => $rows]);
