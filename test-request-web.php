<?php
require_once __DIR__ . '/wp-load.php';

echo "<h1>SkaFX Engine - Test Environment</h1>";

if ( ! class_exists( '\Ska\Logic\SkaFX\SkaFX_Engine' ) ) {
    die("SkaFX Engine chưa được load.");
}

// 1. Giả lập một dòng dữ liệu (Context) từ Database
$context = [
    '$index' => 0,
    '$first' => true,
    '$last'  => false,
    '$even'  => true,
    '$odd'   => false,
    'id'     => 1,
    'name'   => 'Nguyen Van A',
    'age'    => 35,
    'role'   => 'doctor',
    'ska_data_app_test_loop_block_bang_bac_si' => [
        'name' => 'Nguyen Van A',
        'role' => 'doctor'
    ]
];

// 2. Định nghĩa các script SkaFX cần test
$tests = [
    '$index == 0',
    '$even == true',
    'age > 30',
    'role == "doctor"',
    'role == "nurse"',
    // Test với object property (khi gọi prefix)
    'ska_data_app_test_loop_block_bang_bac_si.name == "Nguyen Van A"'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; font-family: monospace;'>";
echo "<tr style='background: #f0f0f0;'><th>SkaFX Script</th><th>Kết quả (last_val)</th><th>Symbols (Biến trích xuất)</th></tr>";

foreach ( $tests as $script ) {
    try {
        $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $script, $context );
        
        $last_val = isset($result['last_val']) ? $result['last_val'] : 'null';
        if ( is_bool($last_val) ) {
            $last_val_str = $last_val ? '<span style="color: green;">true</span>' : '<span style="color: red;">false</span>';
        } else {
            $last_val_str = is_scalar($last_val) ? esc_html($last_val) : '<em>(complex type)</em>';
        }

        $symbols = json_encode($result['symbols'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        echo "<tr>";
        echo "<td><strong>" . esc_html($script) . "</strong></td>";
        echo "<td>" . $last_val_str . "</td>";
        echo "<td><pre style='margin: 0; font-size: 12px;'>" . esc_html($symbols) . "</pre></td>";
        echo "</tr>";
    } catch ( \Exception $e ) {
        echo "<tr>";
        echo "<td><strong>" . esc_html($script) . "</strong></td>";
        echo "<td colspan='2' style='color: red;'>Lỗi Syntax: " . esc_html($e->getMessage()) . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<h2>Context Dữ liệu Đầu vào:</h2>";
echo "<pre style='background: #333; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace;'>" . esc_html(print_r($context, true)) . "</pre>";

echo "<h2>Test Direct render_block() Simulation</h2>";
$block = [
    'blockName' => 'ska-builder/loop',
    'attrs' => [
        'sourceTable' => 'ska_data_app_test_loop_block_bang_bac_si',
        'limit' => 10,
        'slots' => [
            [
                'organismId' => '16',
                'condition' => ''
            ]
        ]
    ]
];
$html = render_block($block);
echo "<pre style='background: #333; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace;'>";
echo "HTML Output:\n";
echo esc_html(var_export($html, true));
echo "</pre>";
