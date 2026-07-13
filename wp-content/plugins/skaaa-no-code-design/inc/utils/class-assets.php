<?php
namespace Skaaa\Builder\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Utility Class
 */
class Assets {
    /**
     * Split Tailwind classes into layout and styling groups.
     * 
     * @param string $classes The full tailwind class string.
     * @return array Array with 'layout' and 'styling' keys.
     */
    public static function split_tailwind_classes( $classes ) {
        if ( empty( $classes ) ) {
            return array( 'layout' => '', 'styling' => '' );
        }

        $layout_regex = '/^(w-|h-|flex|grid|block|inline|hidden|justify|items|content|self|place|columns|aspect|min-|max-|absolute|relative|fixed|sticky|m-|gap-|space-|top-|left-|right-|bottom-|z-)/';
        
        $class_array = array_filter( explode( ' ', $classes ) );
        $layout = array();
        $styling = array();

        foreach ( $class_array as $cls ) {
            if ( preg_match( $layout_regex, $cls ) ) {
                $layout[] = $cls;
            } else {
                $styling[] = $cls;
            }
        }

        return array(
            'layout' => implode( ' ', $layout ),
            'styling' => implode( ' ', $styling )
        );
    }
}
