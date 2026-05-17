<?php
require 'wp-load.php';
global $wp_filter;
$filters = array();
if ( isset( $wp_filter['template_include'] ) ) {
    foreach ( $wp_filter['template_include']->callbacks as $priority => $callbacks ) {
        foreach ( $callbacks as $cb ) {
            $name = '';
            if ( is_array($cb['function']) ) {
                $name = get_class($cb['function'][0]) . '::' . $cb['function'][1];
            } elseif ( is_string($cb['function']) ) {
                $name = $cb['function'];
            } else {
                $name = 'Closure';
            }
            $filters[] = "Priority $priority: $name";
        }
    }
}
echo implode("\n", $filters);
