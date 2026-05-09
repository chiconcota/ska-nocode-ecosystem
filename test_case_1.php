<?php
// Tải môi trường WordPress để nạp đầy đủ hệ sinh thái Ska Builder
require_once dirname( __FILE__ ) . '/wp-load.php';

echo "=== BẮT ĐẦU TEST CASE 1: Static Link trên Image & Container ===\n\n";

/**
 * MÔ PHỎNG DỮ LIỆU BLOCK TỪ EDITOR
 * Đây là mảng attributes mà Gutenberg Editor sẽ lưu xuống cơ sở dữ liệu
 */

// 1. Dữ liệu khối Container
$container_attributes = [
    'tagName'         => 'div',
    'tailwindClasses' => 'flex flex-col p-4 bg-white rounded shadow',
    'link'            => [
        'source' => 'static', // Nguồn tĩnh
        'url'    => '/contact',
        'target' => '_self'
    ]
];
$container_content = '<p>Nội dung bên trong container</p>';

// 2. Dữ liệu khối Image
$image_attributes = [
    'imageUrl'        => 'https://via.placeholder.com/150',
    'aspectRatio'     => 'aspect-video',
    'objectFit'       => 'object-cover',
    'tailwindClasses' => 'rounded-lg hover:opacity-80',
    'alt'             => 'Test Image',
    'link'            => [
        'source' => 'static', // Nguồn tĩnh
        'url'    => 'https://wp.org',
        'target' => '_blank'
    ]
];

/**
 * THỰC THI RENDERING (SSR Backend)
 */

// ==========================================
// KẾT QUẢ 1: CONTAINER BLOCK
// ==========================================
ob_start();
// Khởi tạo biến để giả lập tham số truyền vào file render.php
$attributes = $container_attributes;
$content = $container_content;
require WP_PLUGIN_DIR . '/ska-no-code-design/src/ska-container/render.php';
$container_output = ob_get_clean();

echo "1. KẾT QUẢ RENDER CONTAINER (Mong đợi: thẻ div chuyển thành thẻ a, giữ nguyên class):\n";
echo "--------------------------------------------------\n";
echo $container_output . "\n\n";


// ==========================================
// KẾT QUẢ 2: IMAGE BLOCK
// ==========================================
ob_start();
$attributes = $image_attributes;
require WP_PLUGIN_DIR . '/ska-no-code-design/src/ska-image/render.php';
$image_output = ob_get_clean();

echo "2. KẾT QUẢ RENDER IMAGE (Mong đợi: thẻ img bọc trong thẻ a có target=_blank):\n";
echo "--------------------------------------------------\n";
echo $image_output . "\n\n";

echo "=== KẾT THÚC TEST ===\n";
