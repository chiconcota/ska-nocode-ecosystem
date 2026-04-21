<?php
require_once dirname(__FILE__) . '/wp-load.php';

file_put_contents( 'test_render1.php', '<?php return "HELLO WORLD";' );
file_put_contents( 'test_render2.php', '<?php echo "HELLO WORLD";' );

$closure1 = function() {
    ob_start();
    $out = include 'test_render1.php';
    $ob = ob_get_clean();
    return $out ? $out : $ob;
};

// Actually, let's just test how WordPress maps file closures:
$registry = WP_Block_Type_Registry::get_instance();
register_block_type('test/b1', array( 'render_callback' => function() { ob_start(); $res = include 'test_render1.php'; $ob = ob_get_clean(); return is_string($res) ? $res : $ob; } ));
echo "B1: " . render_block( array('blockName'=>'test/b1', 'attrs'=>array(), 'innerContent'=>array()) ) . "\n";
