<?php
$config_content = file_get_contents('wp-config.php');
preg_match("/define\(\s*'DB_NAME',\s*'([^']+)'\s*\);/", $config_content, $m1);
preg_match("/define\(\s*'DB_USER',\s*'([^']+)'\s*\);/", $config_content, $m2);
preg_match("/define\(\s*'DB_PASSWORD',\s*'([^']*)'\s*\);/", $config_content, $m3);
preg_match("/define\(\s*'DB_HOST',\s*'([^']+)'\s*\);/", $config_content, $m4);

$db = new PDO("mysql:host={$m4[1]};dbname={$m1[1]};charset=utf8", $m2[1], $m3[1]);
$stmt = $db->query("SELECT option_value FROM wp_options WHERE option_name = 'ska_logic_simple_workflows'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    file_put_contents('output.json', $row['option_value']);
    echo "Saved to output.json\n";
} else {
    echo "Option not found\n";
}
