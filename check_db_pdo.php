<?php
$dsn = "mysql:host=localhost;dbname=local;charset=utf8mb4";
$user = 'root';
$pass = 'root';
try {
    $pdo = new PDO($dsn, $user, $pass);
    $stmt = $pdo->query("SELECT * FROM wp_ska_data_app_app_viet_de_test_node_logic_test_api_log WHERE id = 6");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($row);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
