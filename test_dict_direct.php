<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Starting Direct DB Test</h2>";

// Thử bằng mysqli trước
echo "<h3>Testing with mysqli:</h3>";
$conn = @new mysqli('127.0.0.1', 'root', 'root', 'local');
if ($conn->connect_error) {
    echo "mysqli (127.0.0.1) failed: " . $conn->connect_error . "<br>";
    
    // Thử socket/pipe hoặc localhost
    $conn = @new mysqli('localhost', 'root', 'root', 'local');
    if ($conn->connect_error) {
        echo "mysqli (localhost) failed: " . $conn->connect_error . "<br>";
    } else {
        echo "mysqli (localhost) connected!<br>";
    }
} else {
    echo "mysqli (127.0.0.1) connected!<br>";
}

// Thử bằng PDO
echo "<h3>Testing with PDO (127.0.0.1):</h3>";
try {
    $dsn = "mysql:host=127.0.0.1;dbname=local;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "PDO (127.0.0.1) connected!<br>";
} catch (Exception $e) {
    echo "PDO (127.0.0.1) failed: " . $e->getMessage() . "<br>";
}

echo "<h3>Testing with PDO (localhost):</h3>";
try {
    $dsn = "mysql:host=localhost;dbname=local;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "PDO (localhost) connected!<br>";
} catch (Exception $e) {
    echo "PDO (localhost) failed: " . $e->getMessage() . "<br>";
}

// Nếu kết nối thành công, lấy dữ liệu
if (isset($pdo) || (isset($conn) && !$conn->connect_error)) {
    $db = isset($pdo) ? $pdo : null;
    
    // Đọc dictionary từ wp_options
    if ($db) {
        $stmt = $db->query("SELECT option_value FROM wp_options WHERE option_name = 'ska_data_dictionary'");
        $opt = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $res = $conn->query("SELECT option_value FROM wp_options WHERE option_name = 'ska_data_dictionary'");
        $opt = $res->fetch_assoc();
    }
    
    if ($opt) {
        $dictionary = unserialize($opt['option_value']);
        echo "<h3>Tables in Dictionary:</h3><pre>";
        print_r(array_keys($dictionary));
        echo "</pre>";
        
        foreach (array_keys($dictionary) as $t) {
            if (strpos($t, 'quan_ly_dat_phong') !== false || strpos($t, 'phong_khach_san') !== false) {
                echo "<h4>Table metadata: $t</h4><pre>";
                print_r($dictionary[$t]);
                echo "</pre>";
                
                // Get rows
                $full_table = (strpos($t, 'wp_') === 0) ? $t : 'wp_' . $t;
                if ($db) {
                    $rows = $db->query("SELECT * FROM `$full_table` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $rows = [];
                    $res = $conn->query("SELECT * FROM `$full_table` LIMIT 5");
                    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
                }
                echo "Rows in $full_table:<pre>";
                print_r($rows);
                echo "</pre>";
            }
        }
    }
}
