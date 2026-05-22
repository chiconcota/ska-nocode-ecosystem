<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chỉ load nhân core của WordPress, bỏ qua plugin và theme
define('SHORTINIT', true);

try {
    if (!file_exists('wp-load.php')) {
        die("wp-load.php not found!");
    }
    require 'wp-load.php';
    
    echo "<h2>WordPress Loaded via SHORTINIT</h2>";
    
    // PHP core functions like unserialize and database queries are available
    global $wpdb;
    
    $dictionary_raw = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'ska_data_dictionary'");
    if (!$dictionary_raw) {
        die("Option 'ska_data_dictionary' not found!");
    }
    
    $dictionary = maybe_unserialize($dictionary_raw);
    if (!is_array($dictionary)) {
        die("Dictionary is not an array or failed to unserialize!");
    }
    
    echo "<h3>Dictionary Keys (Tables):</h3>";
    echo "<pre>";
    print_r(array_keys($dictionary));
    echo "</pre>";

    $target_tables = [];
    foreach (array_keys($dictionary) as $table) {
        if (strpos($table, 'quan_ly_dat_phong') !== false || strpos($table, 'phong_khach_san') !== false) {
            echo "<h3>Table Schema in Dictionary: $table</h3>";
            echo "<pre>";
            print_r($dictionary[$table]);
            echo "</pre>";
            $target_tables[] = $table;
        }
    }
    
    foreach ($target_tables as $table) {
        $full_table = $table;
        if (strpos($full_table, $wpdb->prefix) !== 0) {
            $full_table = $wpdb->prefix . $full_table;
        }
        
        echo "<h3>Checking table in database: $full_table</h3>";
        
        $table_check = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_table));
        if ($table_check) {
            echo "Table exists physically: $full_table<br>";
            $rows = $wpdb->get_results("SELECT * FROM `$full_table` LIMIT 5", ARRAY_A);
            echo "Data Rows:<pre>";
            print_r($rows);
            echo "</pre>";
            
            $columns = $wpdb->get_results("DESCRIBE `$full_table`");
            echo "Columns meta:<pre>";
            print_r($columns);
            echo "</pre>";
        } else {
            echo "Table does NOT exist physically: $full_table<br>";
        }
    }

} catch (Throwable $e) {
    echo "<h3>Exception caught:</h3>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}
