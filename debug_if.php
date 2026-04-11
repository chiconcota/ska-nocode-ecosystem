<?php
require_once('wp-load.php');
ini_set('display_errors', 1);
$script = 'var data = IF([app_Do5P1Uxz.test_sync.kieu_slect]== "đỏ", "✔️ đúng với id=1", "❌ sai với id=1");';

try {
    $lexer = new \Ska\Logic\SkaFX\SkaFX_Lexer( $script );
    $tokens = $lexer->tokenize();
    echo "--- Tokens ---\n";
    print_r($tokens);

    $parser = new \Ska\Logic\SkaFX\SkaFX_Parser( $tokens );
    $statements = $parser->parse();
    echo "\n--- AST ---\n";
    print_r($statements);
    
    $evaluator = new \Ska\Logic\SkaFX\SkaFX_Evaluator( [ 'GLOBAL_ID' => 1 ] );
    $last_val = $evaluator->evaluate_script( $statements );
    echo "\n--- Evaluator ---\n";
    echo $last_val;

} catch (Exception $e) {
    echo "\nFATAL: " . $e->getMessage();
}
