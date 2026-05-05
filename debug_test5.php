<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'wp-load.php';

echo "<h2>SKA LOOP INTEGRATION TEST</h2>\n";
echo "<h3>Testing E2E: Ska Loop + Ska Dynamic Content + SkaFX + Data Pro</h3>\n";

// 1. Kiểm tra Data
global $wpdb;
$base_table_name = 'ska_data_doctors';
$table_name = $wpdb->prefix . $base_table_name;

$wpdb->query("CREATE TABLE IF NOT EXISTS {$table_name} (
    id int(11) NOT NULL AUTO_INCREMENT,
    ho_ten varchar(255) NOT NULL,
    experience int(11) NOT NULL,
    PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows($table_name, [], 5);
if (empty($rows)) {
    // Seed some data
    $wpdb->insert($table_name, ['ho_ten' => 'Dr. Nguyễn Văn A', 'experience' => 5]);
    if ($wpdb->last_error) { echo "DB Insert Error: " . $wpdb->last_error . "\n"; }
    $wpdb->insert($table_name, ['ho_ten' => 'Dr. Trần Thị B', 'experience' => 10]);
    $wpdb->insert($table_name, ['ho_ten' => 'Dr. Lê Văn C', 'experience' => 2]);
    $rows = \Ska\Data\Core\Data_Fetcher::get_table_rows($table_name, [], 5);
}

if (empty($rows)) {
    echo "<div style='color:red;'>Failed: Không thể tạo và lấy dữ liệu mẫu.</div>\n";
    if ($wpdb->last_error) { echo "Last DB Error: " . $wpdb->last_error . "\n"; }
    exit;
} else {
    echo "<div>Thành công: Lấy được " . count($rows) . " dòng từ bảng $table_name.</div>\n";
}

// Lấy tên cột đầu tiên (ví dụ 'name' hoặc 'ho_ten')
$first_row = $rows[0];
$dynamic_column = '';
foreach ($first_row as $key => $val) {
    if ($key !== 'id' && $key !== 'created_at' && $key !== 'updated_at') {
        $dynamic_column = $key;
        break;
    }
}

echo "<div>Dùng cột '$dynamic_column' để test Dynamic Content Binding.</div>\n";

// 2. Tạo một Organism giả (bỏ qua DB, mock hàm)
// Chúng ta sẽ hook vào \Ska\Design\Api\Organisms_API (nếu có hook) hoặc mock data nếu không thể.
// Thực ra get_bulk_html không có filter/action, nó gọi thẳng Data_Fetcher.
// Thay vì mock, chúng ta tạo một row trong ska_data_sys_organisms.
global $wpdb;
$organism_table = $wpdb->prefix . 'ska_data_sys_organisms';

// Print columns
$cols = $wpdb->get_results("DESCRIBE {$organism_table}");
echo "<pre>"; print_r($cols); echo "</pre>";

$fake_html = '<!-- wp:ska-builder/container {"className":"doctor-card"} -->
<div class="wp-block-ska-builder-container doctor-card">
    <!-- wp:core/heading {"skaDynamicBinding":{"script":"[' . $dynamic_column . ']"}} -->
    <h2>Tên Bác Sĩ Cũ</h2>
    <!-- /wp:core/heading -->
    
    <!-- wp:core/paragraph {"skaDynamicBinding":{"script":"[experience]"}} -->
    <p>Số năm kinh nghiệm</p>
    <!-- /wp:core/paragraph -->
</div>
<!-- /wp:ska-builder/container -->';

// Insert temporary organism
// Use only the columns that actually exist!
$insert_data = ['name' => 'Test Organism for Loop E2E'];
$has_html_content = false;
foreach ($cols as $col) {
    if ($col->Field === 'html_content') { $insert_data['html_content'] = $fake_html; $has_html_content = true; }
    if ($col->Field === 'raw_html') { $insert_data['raw_html'] = $fake_html; }
}

if (!$has_html_content) {
    echo "<div style='color:orange;'>Warning: Table doesn't have html_content column. Modifying table...</div>";
    $wpdb->query("ALTER TABLE {$organism_table} ADD html_content longtext");
    $insert_data['html_content'] = $fake_html;
}

$wpdb->insert($organism_table, $insert_data);
if ($wpdb->last_error) { echo "Organism DB Insert Error: " . $wpdb->last_error . "\n"; }
$org_id = $wpdb->insert_id;

echo "<div>Đã tạo Organism giả lập ID: $org_id</div>\n";

// 3. Render block Ska Loop
$loop_attributes = [
    'sourceTable' => $table_name,
    'limit' => 3,
    'tailwindClasses' => 'grid grid-cols-1 md:grid-cols-2 gap-4',
    'slots' => [
        [
            'condition' => 'default',
            'organismId' => (string) $org_id
        ]
    ]
];

$loop_block = [
    'blockName' => 'ska-builder/loop',
    'attrs' => $loop_attributes,
    'innerBlocks' => [],
    'innerHTML' => '',
    'innerContent' => []
];

// Thời gian bắt đầu Render
$start_time = microtime(true);

echo "<h4>Kết quả Bulk HTML (Từ Organism_API):</h4>\n";
$bulk = \Ska\Design\Api\Organisms_API::get_bulk_html([$org_id]);
echo "<pre>"; print_r($bulk); echo "</pre>";

echo "<h4>Kết quả Render Thực Tế:</h4>\n";
echo "<div style='border:2px solid green; padding: 20px;'>\n";
echo "Is ska-builder/loop registered? " . (WP_Block_Type_Registry::get_instance()->is_registered('ska-builder/loop') ? "Yes" : "No") . "<br>\n";

$block_type = WP_Block_Type_Registry::get_instance()->get_registered('ska-builder/loop');
echo "render_callback is: " . var_export(is_callable($block_type->render_callback), true) . "<br>\n";

$rendered_html = render_block($loop_block);
echo "rendered html is empty? " . var_export(empty($rendered_html), true) . "<br>\n";

echo $rendered_html;
echo "</div>\n";

$end_time = microtime(true);
$duration = round(($end_time - $start_time) * 1000, 2);

echo "<div><b>Tốc độ Render:</b> $duration ms</div>\n";

// 4. Cleanup
$wpdb->delete($organism_table, ['id' => $org_id]);
echo "<div>Đã dọn dẹp Organism giả lập ID: $org_id</div>\n";
