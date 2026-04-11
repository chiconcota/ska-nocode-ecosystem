<?php
require_once('wp-load.php');
ini_set('display_errors', 1);
$script = 'visible = [app_Do5P1Uxz.test_sync.kieu_so]<6;';

try {
    $evaluator = new \Ska\Logic\SkaFX\SkaFX_Evaluator( [ 'GLOBAL_ID' => 1 ] );
    $evaluator->evaluate_script( (new \Ska\Logic\SkaFX\SkaFX_Parser( (new \Ska\Logic\SkaFX\SkaFX_Lexer($script))->tokenize() ))->parse() );
    echo "\n--- Symbols ---\n";
    print_r($evaluator->get_symbols());
} catch (Exception $e) {
    echo "\nFATAL: " . $e->getMessage();
}
