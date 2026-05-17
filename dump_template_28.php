<?php
require_once 'wp-config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT html_content FROM {$table_prefix}ska_data_sys_templates WHERE id = 28");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "HTML:\n" . $row["html_content"] . "\n";
    }
} else {
    echo "0 results";
}
$conn->close();
