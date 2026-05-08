<?php
namespace Ska\Builder\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Dynamic Data Utility Class
 */
class Dynamic_Data {
    
    /**
     * Resolve the dynamic link attribute to a final URL string.
     * 
     * @param array $link_attr The block link attribute.
     * @return string The resolved URL.
     */
    public static function resolve_dynamic_link( $link_attr ) {
        if ( empty( $link_attr ) || ! is_array( $link_attr ) ) {
            return '';
        }

        $url     = $link_attr['url'] ?? '';
        $dynamic = $link_attr['dynamic'] ?? [];
        $source  = $dynamic['source'] ?? 'static';
        $key     = $dynamic['key'] ?? '';

        if ( $source === 'static' ) {
            return $url;
        }

        if ( $source === 'system' ) {
            switch ( $key ) {
                case 'home_url':
                    return home_url( '/' );
                case 'admin_url':
                    return admin_url();
                case 'current_post_url':
                    return get_permalink();
                // We can add more system URLs here in the future
                default:
                    return home_url( '/' );
            }
        }

        if ( $source === 'loop' ) {
            if ( ! empty( $key ) ) {
                // Trả về cú pháp Mustache để hệ thống Ska Loop tự động Hydrate
                return '{{' . $key . '}}';
            }
        }

        return $url;
    }

    /**
     * Legacy support for dynamic text content (used by ska-text)
     * 
     * @param array $dynamic_attr The block dynamic attribute.
     * @param string $type The expected type of content.
     * @return string The resolved text.
     */
    public static function get_dynamic_content( $dynamic_attr, $type = 'text' ) {
        if ( empty( $dynamic_attr ) || ! is_array( $dynamic_attr ) ) {
            return '';
        }

        $source = $dynamic_attr['source'] ?? 'static';
        $key    = $dynamic_attr['key'] ?? '';

        if ( $source === 'static' ) {
            return ''; 
        }

        if ( $source === 'system' ) {
            switch ( $key ) {
                case 'site_title': return get_bloginfo( 'name' );
                case 'site_description': return get_bloginfo( 'description' );
                case 'post_title': return get_the_title();
                case 'post_excerpt': return get_the_excerpt();
            }
        }

        if ( $source === 'loop' ) {
            if ( ! empty( $key ) ) {
                return '{{' . $key . '}}';
            }
        }

        return '[' . esc_html($source) . ':' . esc_html($key) . ']';
    }
}
