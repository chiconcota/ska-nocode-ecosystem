<?php
require_once 'wp-config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$res = $conn->query('SELECT teacher_id FROM ' . $table_prefix . 'ska_data_app_courses_courses LIMIT 5');
while($row = $res->fetch_assoc()) { echo $row['teacher_id'] . "\n"; }
