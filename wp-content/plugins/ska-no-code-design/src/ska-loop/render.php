<?php
/**
 * Render Callback cho Ska Loop Block
 * 
 * Kiến trúc: Siêu tốc (Zero N+1)
 * 1. Thu thập tất cả các ID của Organism từ các Slots
 * 2. Tải toàn bộ HTML của chúng lên RAM bằng 1 Query (Bulk Load)
 * 3. Biên dịch HTML thô thành Khuôn Mustache (Template Compilation)
 * 4. Truy vấn bảng phẳng lấy dữ liệu.
 * 5. Duyệt qua từng dòng dữ liệu, tính toán SkaFX để chọn Khuôn.
 * 6. Hydrate (bơm) dữ liệu vào Khuôn Mustache siêu nhanh bằng chuỗi tĩnh.
 */

defined( 'ABSPATH' ) || exit;

// Lấy thuộc tính cấu hình từ người dùng
$source_table = isset( $attributes['sourceTable'] ) ? sanitize_text_field( $attributes['sourceTable'] ) : '';
$limit        = isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 10;
$slots        = isset( $attributes['slots'] ) && is_array( $attributes['slots'] ) ? $attributes['slots'] : [];

if ( empty( $source_table ) ) {
    if ( is_admin() ) {
        return '<div class="components-placeholder"><div class="components-placeholder__label">Ska Query Loop</div><div class="components-placeholder__fieldset">Vui lòng chọn bảng nguồn (Source Table) trong Inspector.</div></div>';
    }
    return '';
}

// 1. Thu thập ID và Bulk Load
$organism_ids = [];
foreach ( $slots as $slot ) {
    if ( ! empty( $slot['organismId'] ) ) {
        $organism_ids[] = absint( $slot['organismId'] );
    }
}

if ( empty( $organism_ids ) ) {
    if ( is_admin() ) {
        return '<div class="components-placeholder"><div class="components-placeholder__label">Ska Query Loop</div><div class="components-placeholder__fieldset">Vui lòng thiết lập ít nhất 1 Slot (Organism ID).</div></div>';
    }
    return '';
}

// Lấy mảng [id => raw_html]
$bulk_html = \Ska\Design\Api\Organisms_API::get_bulk_html( $organism_ids );

// 2. Biên dịch (Compile) mã HTML tĩnh thành Khuôn Mustache {{key}}
$compiled_templates = [];

// Tắt bộ lọc chặn Frontend cũ để không chạy truy vấn dữ liệu thừa
if ( class_exists( '\Ska_Dynamic_Content' ) ) {
    remove_filter( 'render_block', [ \Ska_Dynamic_Content::instance(), 'parse_ska_blocks' ], 10 );
}

// Định nghĩa bộ lọc Compile Cục bộ
$compile_filter = function( $block_content, $block ) {
    if ( isset( $block['attrs']['skaDynamicBinding']['script'] ) && ! empty( $block['attrs']['skaDynamicBinding']['script'] ) ) {
        $script = $block['attrs']['skaDynamicBinding']['script'];
        
        // Trích xuất biến [table.column]
        preg_match_all( '/\[([a-zA-Z0-9_\.]+)\]/', $script, $matches );
        if ( ! empty( $matches[1] ) ) {
            $var_name = $matches[1][0]; 
            
            // Đào lỗ vào Text Block
            if ( $block['blockName'] === 'ska-builder/text' ) {
                 $block_content = preg_replace( '/(<[a-zA-Z0-9]+[^>]*>)(.*?)(<\/[a-zA-Z0-9]+>\s*)$/s', '${1}{{' . $var_name . '}}${3}', $block_content );
            }
            // Mở rộng sau: Đào lỗ cho Image (src), Link (href)...
        }
    }
    return $block_content;
};

// Bật bộ lọc Compile
add_filter( 'render_block', $compile_filter, 10, 2 );

foreach ( $bulk_html as $org_id => $raw_html ) {
    $blocks = parse_blocks( $raw_html );
    $html_output = '';
    foreach ( $blocks as $b ) {
        $html_output .= render_block( $b );
    }
    $compiled_templates[ $org_id ] = $html_output;
}

// Tắt bộ lọc Compile, bật lại bộ lọc cũ
remove_filter( 'render_block', $compile_filter, 10 );
if ( class_exists( '\Ska_Dynamic_Content' ) ) {
    add_filter( 'render_block', [ \Ska_Dynamic_Content::instance(), 'parse_ska_blocks' ], 10, 2 );
}


// 3. Lấy dữ liệu thực từ Data Pro (Sử dụng bảng phẳng)
if ( ! class_exists( '\Ska\Data\Core\Data_Fetcher' ) ) {
    return 'Lỗi: Ska Data Pro chưa được kích hoạt.';
}

$rows = \Ska\Data\Core\Data_Fetcher::get_table_rows( $source_table, [], $limit );
if ( empty( $rows ) ) {
    return '<!-- Ska Loop: No data found -->';
}

// 4. Vòng Lặp Phân Giải Siêu Tốc
$final_html = '';
$total_rows = count( $rows );

foreach ( $rows as $index => $row ) {
    
    // Bơm các biến Hệ thống vào Context
    $context = array_merge( $row, [
        '$index' => $index,
        '$first' => $index === 0,
        '$last'  => $index === ( $total_rows - 1 ),
        '$even'  => ( $index % 2 ) === 0,
        '$odd'   => ( $index % 2 ) !== 0,
        // Hỗ trợ cả prefix của bảng: [doctors.name]
        $source_table => $row
    ] );

    // Duyệt qua các slot để Match điều kiện
    $matched_template_html = '';
    
    foreach ( $slots as $slot ) {
        if ( empty( $slot['organismId'] ) ) continue;
        
        $org_id = absint( $slot['organismId'] );
        if ( ! isset( $compiled_templates[ $org_id ] ) ) continue;

        $condition = trim( $slot['condition'] ?? '' );
        
        // Nếu không có condition, coi như Default Slot (luôn khớp)
        if ( empty( $condition ) || $condition === 'default' ) {
            $matched_template_html = $compiled_templates[ $org_id ];
            break;
        }

        // Tính toán bằng SkaFX
        if ( class_exists( '\Ska\Logic\SkaFX\SkaFX_Engine' ) ) {
            try {
                $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute( $condition, $context );
                // Nếu phép tính trả về TRUE, khớp Slot này
                if ( isset( $result['last_val'] ) && $result['last_val'] === true ) {
                    $matched_template_html = $compiled_templates[ $org_id ];
                    break;
                }
            } catch ( \Exception $e ) {
                // Bỏ qua lỗi cú pháp để loop chạy mượt
            }
        }
    }

    // Cắm dữ liệu (Hydration) vào Khuôn
    if ( ! empty( $matched_template_html ) ) {
        $hydrated_html = preg_replace_callback( '/\{\{\s*([a-zA-Z0-9_\.\$]+)\s*\}\}/', function( $matches ) use ( $context, $source_table ) {
            $key_path = $matches[1]; // Vd: `name` hoặc `doctors.name`
            
            // Xử lý tách bảng (nếu có)
            if ( strpos( $key_path, $source_table . '.' ) === 0 ) {
                $key_path = str_replace( $source_table . '.', '', $key_path );
            }
            
            if ( isset( $context[ $key_path ] ) ) {
                return esc_html( (string) $context[ $key_path ] );
            }
            return '';
        }, $matched_template_html );

        $final_html .= $hydrated_html;
    }
}

// Bọc wrapper
$wrapper_classes = 'ska-loop-wrapper';
if ( ! empty( $attributes['className'] ) ) {
    $wrapper_classes .= ' ' . esc_attr( $attributes['className'] );
}

echo '<div class="' . $wrapper_classes . '">';
echo $final_html;
echo '</div>';
