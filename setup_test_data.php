<?php
require_once 'wp-load.php';

$app_manager = \Ska\Data\Core\App_Manager::class;
$app_id = $app_manager::create_app('Online Hospital', 'dashicons-heart');

// Error handling in case it already exists
if (is_wp_error($app_id)) {
    if ($app_id->get_error_code() === 'app_exists') {
        $app_id = 'app_online_hospital'; // fallback
    }
}

$db_engine = \Ska\Data\Core\Database_Engine::get_instance();
$table_name = $db_engine->create_custom_table('Doctors', 'dashicons-businessman', $app_id);

if (!is_wp_error($table_name)) {
    $db_engine->add_column($table_name, 'Doctor Name', 'short_text');
    $db_engine->add_column($table_name, 'Avatar', 'media');
    $db_engine->add_column($table_name, 'Specialty', 'short_text');
    $db_engine->add_column($table_name, 'Experience', 'short_text');
    $db_engine->add_column($table_name, 'Qualifications', 'short_text');
    $db_engine->add_column($table_name, 'City', 'short_text');
    $db_engine->add_column($table_name, 'Clinic Name', 'short_text');
    $db_engine->add_column($table_name, 'Rating', 'short_text');
    $db_engine->add_column($table_name, 'Patient Count', 'short_text');
    $db_engine->add_column($table_name, 'Consultation Fee', 'number');
    $db_engine->add_column($table_name, 'Availability Text', 'short_text');
    $db_engine->add_column($table_name, 'Has Guarantee', 'boolean');
} else {
    echo "Table may already exist: " . $table_name->get_error_message() . "\n";
    // Construct expected table name to insert data anyway
    global $wpdb;
    $table_name = $wpdb->prefix . 'ska_data_app_online_hospital_doctors';
}

// Insert dummy data
global $wpdb;

// Verify if the record exists to prevent duplicates
$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE doctor_name = %s", 'Dr. Shesham Srinidhi'));

if (!$exists) {
    $wpdb->insert($table_name, [
        'doctor_name' => 'Dr. Shesham Srinidhi',
        'avatar' => 'https://via.placeholder.com/150', // placeholder
        'specialty' => 'General Practitioner',
        'experience' => '5 YEARS',
        'qualifications' => 'MD(PHYSICIAN)',
        'city' => 'Hyderabad',
        'clinic_name' => 'Apollo 24|7 Clinic, Hyderabad',
        'rating' => '86%',
        'patient_count' => '175+ Patients',
        'consultation_fee' => 660,
        'availability_text' => 'Available in 1 minutes',
        'has_guarantee' => 1,
    ]);
    echo "Created and inserted dummy data in " . $table_name . "\n";
} else {
    echo "Dummy data already exists in " . $table_name . "\n";
}
