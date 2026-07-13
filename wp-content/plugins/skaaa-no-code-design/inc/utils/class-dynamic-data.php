<?php
namespace Skaaa\Builder\Utils;

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
                // Trả về cú pháp Mustache để hệ thống Skaaa Loop tự động Hydrate
                return '{{' . $key . '}}';
            }
        }

        return $url;
    }

    /**
     * Legacy support for dynamic text content (used by skaaa-text)
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

    /**
     * Parse content and resolve inline dynamic links (e.g., from RichText).
     * 
     * @param string $content The block content.
     * @return string The content with resolved inline links.
     */
    public static function resolve_inline_links( $content ) {
        if ( strpos( $content, 'data-dynamic-source' ) === false ) {
            return $content;
        }

        return preg_replace_callback( '/<a\s+([^>]*?)>/i', function( $matches ) {
            $tag_attrs = $matches[1];
            
            if ( preg_match( '/data-dynamic-source="([^"]+)"/i', $tag_attrs, $source_match ) ) {
                $source = $source_match[1];
                $key = '';
                if ( preg_match( '/data-dynamic-key="([^"]*)"/i', $tag_attrs, $key_match ) ) {
                    $key = $key_match[1];
                }
                
                $url = '';
                if ( preg_match( '/href="([^"]*)"/i', $tag_attrs, $url_match ) ) {
                    $url = $url_match[1];
                }

                $link_attr = [
                    'url'     => $url,
                    'dynamic' => [
                        'source' => $source,
                        'key'    => $key
                    ]
                ];

                $resolved_url = self::resolve_dynamic_link( $link_attr );
                
                if ( ! empty( $resolved_url ) ) {
                    if ( preg_match( '/href="([^"]*)"/i', $tag_attrs ) ) {
                        $tag_attrs = preg_replace( '/href="([^"]*)"/i', 'href="' . esc_attr($resolved_url) . '"', $tag_attrs );
                    } else {
                        $tag_attrs .= ' href="' . esc_attr($resolved_url) . '"';
                    }
                }

                // Clean up data attributes
                $tag_attrs = preg_replace( '/\s*data-dynamic-source="[^"]*"/i', '', $tag_attrs );
                $tag_attrs = preg_replace( '/\s*data-dynamic-key="[^"]*"/i', '', $tag_attrs );
                
                return '<a ' . trim($tag_attrs) . '>';
            }
            
            return $matches[0];
        }, $content );
    }

    /**
     * Safe esc_url wrapper that preserves {{...}} mustache tags.
     * 
     * @param string $url The URL to escape.
     * @return string The escaped URL.
     */
    public static function safe_esc_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }
        if ( strpos( $url, '{{' ) !== false && strpos( $url, '}}' ) !== false ) {
            return esc_attr( $url );
        }
        return esc_url( $url );
    }
}
