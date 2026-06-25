<?php
/**
 * Plugin Name: Ska Data Pro
 * Plugin URI: https://ska.net
 * Description: High-performance database system (Flat Tables) and schema automation via Template Gallery.
 * Version: 1.3.2
 * Author: Ly Tat Thanh + antigravity AI
 * Author URI: https://lytatthanh.com
 * Text Domain: ska-data-pro
 * Domain Path: /languages
 */

namespace Ska\Data;

defined('ABSPATH') || exit;

// Định nghĩa Path & URL
define('SKA_DATA_PRO_VERSION', '1.3.2');
define('SKA_DATA_PRO_PATH', plugin_dir_path(__FILE__));
define('SKA_DATA_PRO_URL', plugin_dir_url(__FILE__));

// Load Text Domain
add_action('plugins_loaded', function() {
    load_plugin_textdomain('ska-data-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Load Core Classes
require_once SKA_DATA_PRO_PATH . 'inc/admin/class-admin-menu.php';
require_once SKA_DATA_PRO_PATH . 'inc/admin/class-admin-ajax.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-app-manager.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-template-registry.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-data-fetcher.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-database-engine.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-query-builder.php';
require_once SKA_DATA_PRO_PATH . 'inc/core/class-scripts-loader.php';
require_once SKA_DATA_PRO_PATH . 'inc/api/class-rest-api.php';
// Khởi tạo hệ thống
function init()
{
    Admin\Admin_Menu::get_instance();
    $ajax = new Admin\Admin_Ajax(); // Khởi tạo xử lý Request (AJAX)
    Core\Query_Builder::get_instance(); // Đánh thức cỗ máy xử lý Truy Xuất Dữ Liệu SQL
    Core\App_Manager::maybe_run_migration(); // Đồng bộ App Blueprint Migration
    Api\Rest_Api::get_instance(); // Đăng ký REST API cho App Portal

    // Khởi tạo Scripts Loader & đúc bảng phẳng MySQL
    Core\Scripts_Loader::get_instance();
    Core\Scripts_Loader::maybe_create_table();

    // Require file chứa Provider lúc hệ thống Hook Plugins_loaded (tránh bị return early)
    require_once SKA_DATA_PRO_PATH . 'inc/core/class-ska-provider.php';

    // Cắm Provider Dữ liệu của Ska Flat Tables vào hệ sinh thái của App Builder (Nếu có)
    if (class_exists('\Ska\Builder\Data\Core') && class_exists('\Ska\Data\Core\Ska_Provider')) {
        \Ska\Builder\Data\Core::instance()->registry->register(new Core\Ska_Provider());

        // Đăng ký Provider Thử nghiệm Extensibility
        require_once SKA_DATA_PRO_PATH . 'inc/core/class-test-provider.php';
        \Ska\Builder\Data\Core::instance()->registry->register(new Core\Test_Provider());
    }
}
add_action('plugins_loaded', __NAMESPACE__ . '\init');

// --- HỌNG ĐÓN DỮ LIỆU TỪ LỚP NHÂN (SKA-XI MĂNG) ---
// Action Node từ Ska Logic Engine sẽ xả vào đây, Ska Data Pro sẽ đúc bê tông xuống MySQL
add_filter('ska_data_insert_record', function ($result, $payload, $table_name) {
    global $wpdb;

    if (empty($table_name) || empty($payload)) {
        error_log("ska_data_insert_record: table_name or payload is empty. table_name: " . print_r($table_name, true));
        return false;
    }

    // Tự động bọc prefix bảo mật (Vd: truyền "ska_data_leads" -> "wp_ska_data_leads")
    $table_name_with_prefix = strpos($table_name, $wpdb->prefix) === 0 ? $table_name : $wpdb->prefix . $table_name;
    error_log("ska_data_insert_record: Processing insert for table: " . $table_name_with_prefix);

    // Bộ lọc Vô trùng: Loại bỏ thông tin rác (Ví Dụ Input người dùng chế bậy không có thật trong Schema)
    $columns = \Ska\Data\Core\Data_Fetcher::get_table_columns($table_name_with_prefix);
    $valid_columns = array();
    if (is_array($columns)) {
        foreach ($columns as $col) {
            $valid_columns[] = $col->Field;
        }
    }
    error_log("ska_data_insert_record: valid_columns: " . print_r($valid_columns, true));

    // Đọc Dictionary để hỗ trợ phân giải Label/Alias (Tên Người Dùng Đặt) về Tên Cột Vật lý
    $clean_table_name = str_replace($wpdb->prefix, '', $table_name_with_prefix);
    $all_dict = get_option('ska_data_dictionary', array());
    $table_dict = array();
    if (isset($all_dict[$table_name_with_prefix])) {
        $table_dict = $all_dict[$table_name_with_prefix];
    } elseif (isset($all_dict[$clean_table_name])) {
        $table_dict = $all_dict[$clean_table_name];
    } elseif (isset($all_dict[$table_name])) {
        $table_dict = $all_dict[$table_name];
    }

    $clean_insert_data = array();
    foreach ($payload as $key => $val) {
        $col_slug = false;

        // Trường hợp 1: Key khớp chính xác với Cột Vật lý MySQL (Ví dụ: title, name, text_1)
        if (in_array($key, $valid_columns)) {
            $col_slug = $key;
        } else {
            // Trường hợp 2: Key là Label Tiếng Việt hoặc Alias từ Form (Ví dụ: "Họ và tên", "name")
            $normalized_key = sanitize_title(str_replace('_', '-', $key));
            foreach ($table_dict as $s => $col_meta) {
                if ($s === '__table_info') continue;
                if (!empty($col_meta['label'])) {
                    $normalized_label = sanitize_title(str_replace('_', '-', $col_meta['label']));
                    if ($col_meta['label'] === $key || $normalized_label === $normalized_key) {
                        $col_slug = $s;
                        break;
                    }
                }
            }
        }

        if ($col_slug && in_array($col_slug, $valid_columns)) {
            $col_type = isset($table_dict[$col_slug]['type']) ? $table_dict[$col_slug]['type'] : '';
            $is_json_col = in_array($col_type, ['multi_select', 'relation', 'rollup'], true);

            if ($is_json_col) {
                if (empty($val) && $val !== 0 && $val !== '0') {
                    $clean_insert_data[$col_slug] = null;
                } else {
                    if (is_array($val)) {
                        $ids = array_map(function($item) {
                            if (is_array($item) && isset($item['id'])) {
                                return $item['id'];
                            } elseif (is_object($item) && isset($item->id)) {
                                return $item->id;
                            }
                            return $item;
                        }, array_values($val));
                        
                        if ($col_type === 'relation') {
                            $clean_insert_data[$col_slug] = wp_json_encode(array_filter(array_map('intval', $ids)));
                        } else {
                            $clean_insert_data[$col_slug] = wp_json_encode(array_values($val));
                        }
                    } elseif (is_string($val)) {
                        $trimmed = trim($val);
                        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                            $decoded = json_decode($trimmed, true);
                            if (is_array($decoded)) {
                                if ($col_type === 'relation') {
                                    $ids = array_map(function($item) {
                                        if (is_array($item) && isset($item['id'])) {
                                            return $item['id'];
                                        }
                                        return $item;
                                    }, $decoded);
                                    $clean_insert_data[$col_slug] = wp_json_encode(array_filter(array_map('intval', $ids)));
                                } else {
                                    $clean_insert_data[$col_slug] = $trimmed;
                                }
                            } else {
                                $clean_insert_data[$col_slug] = null;
                            }
                        } else {
                            if ($col_type === 'relation') {
                                $ids = array_filter(array_map('intval', array_map('trim', explode(',', $trimmed))));
                                $clean_insert_data[$col_slug] = !empty($ids) ? wp_json_encode(array_values($ids)) : null;
                            } else {
                                $clean_insert_data[$col_slug] = wp_json_encode([$trimmed]);
                            }
                        }
                    } else {
                        $clean_insert_data[$col_slug] = null;
                    }
                }
            } else {
                if (is_array($val)) {
                    $clean_insert_data[$col_slug] = wp_json_encode(array_values($val));
                } else {
                    $clean_insert_data[$col_slug] = $val;
                }
            }
        }
    }

    if (empty($clean_insert_data)) {
        error_log("ska_data_insert_record: clean_insert_data is empty after filtering. payload keys: " . print_r(array_keys($payload), true));
        return false; // Payload rỗng tuếch không có trường nào khớp
    }

    // Lệnh Đúc Bê Tông của WordPress
    $wpdb->insert($table_name_with_prefix, $clean_insert_data);
    
    if (!$wpdb->insert_id) {
        error_log("ska_data_insert_record: WPDB Insert Failed. Error: " . $wpdb->last_error);
    }

    return $wpdb->insert_id ? $wpdb->insert_id : false;
}, 10, 3);

// --- CẬP NHẬT DỮ LIỆU TỪ SKA-XI MĂNG ---
add_filter('ska_data_update_record', function ($result, $payload, $table_name, $where_conditions) {
    global $wpdb;

    if (empty($table_name) || empty($payload) || empty($where_conditions)) {
        return false;
    }

    $table_name_with_prefix = strpos($table_name, $wpdb->prefix) === 0 ? $table_name : $wpdb->prefix . $table_name;

    $columns = \Ska\Data\Core\Data_Fetcher::get_table_columns($table_name_with_prefix);
    $valid_columns = array();
    if (is_array($columns)) {
        foreach ($columns as $col) {
            $valid_columns[] = $col->Field;
        }
    }

    $clean_table_name = str_replace($wpdb->prefix, '', $table_name_with_prefix);
    $all_dict = get_option('ska_data_dictionary', array());
    $table_dict = array();
    if (isset($all_dict[$table_name_with_prefix])) {
        $table_dict = $all_dict[$table_name_with_prefix];
    } elseif (isset($all_dict[$clean_table_name])) {
        $table_dict = $all_dict[$clean_table_name];
    } elseif (isset($all_dict[$table_name])) {
        $table_dict = $all_dict[$table_name];
    }

    $clean_update_data = array();
    foreach ($payload as $key => $val) {
        $col_slug = false;

        if (in_array($key, $valid_columns)) {
            $col_slug = $key;
        } else {
            $normalized_key = sanitize_title(str_replace('_', '-', $key));
            foreach ($table_dict as $s => $col_meta) {
                if ($s === '__table_info') continue;
                if (!empty($col_meta['label'])) {
                    $normalized_label = sanitize_title(str_replace('_', '-', $col_meta['label']));
                    if ($col_meta['label'] === $key || $normalized_label === $normalized_key) {
                        $col_slug = $s;
                        break;
                    }
                }
            }
        }

        if ($col_slug && in_array($col_slug, $valid_columns)) {
            $col_type = isset($table_dict[$col_slug]['type']) ? $table_dict[$col_slug]['type'] : '';
            $is_json_col = in_array($col_type, ['multi_select', 'relation', 'rollup'], true);

            if ($is_json_col) {
                if (empty($val) && $val !== 0 && $val !== '0') {
                    $clean_update_data[$col_slug] = null;
                } else {
                    if (is_array($val)) {
                        $ids = array_map(function($item) {
                            if (is_array($item) && isset($item['id'])) {
                                return $item['id'];
                            } elseif (is_object($item) && isset($item->id)) {
                                return $item->id;
                            }
                            return $item;
                        }, array_values($val));
                        
                        if ($col_type === 'relation') {
                            $clean_update_data[$col_slug] = wp_json_encode(array_filter(array_map('intval', $ids)));
                        } else {
                            $clean_update_data[$col_slug] = wp_json_encode(array_values($val));
                        }
                    } elseif (is_string($val)) {
                        $trimmed = trim($val);
                        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                            $decoded = json_decode($trimmed, true);
                            if (is_array($decoded)) {
                                if ($col_type === 'relation') {
                                    $ids = array_map(function($item) {
                                        if (is_array($item) && isset($item['id'])) {
                                            return $item['id'];
                                        }
                                        return $item;
                                    }, $decoded);
                                    $clean_update_data[$col_slug] = wp_json_encode(array_filter(array_map('intval', $ids)));
                                } else {
                                    $clean_update_data[$col_slug] = $trimmed;
                                }
                            } else {
                                $clean_update_data[$col_slug] = null;
                            }
                        } else {
                            if ($col_type === 'relation') {
                                $ids = array_filter(array_map('intval', array_map('trim', explode(',', $trimmed))));
                                $clean_update_data[$col_slug] = !empty($ids) ? wp_json_encode(array_values($ids)) : null;
                            } else {
                                $clean_update_data[$col_slug] = wp_json_encode([$trimmed]);
                            }
                        }
                    } else {
                        $clean_update_data[$col_slug] = null;
                    }
                }
            } else {
                if (is_array($val)) {
                    $clean_update_data[$col_slug] = wp_json_encode(array_values($val));
                } else {
                    $clean_update_data[$col_slug] = $val;
                }
            }
        }
    }

    if (empty($clean_update_data)) {
        return false; // Payload rỗng tuếch không có trường nào khớp
    }

    // Lệnh Cập Nhật Data của WordPress
    $update_result = $wpdb->update($table_name_with_prefix, $clean_update_data, $where_conditions);
    
    if ( $update_result !== false ) {
        $row_id = isset( $where_conditions['id'] ) ? intval( $where_conditions['id'] ) : 0;
        if ( $row_id ) {
            do_action( 'ska_data_row_updated', $table_name_with_prefix, $row_id, $clean_update_data );
        }
    }
    
    return $update_result;
}, 10, 4);

// --- ĐOẠN CODE TEST NHANH (MÁY BƠM DỮ LIỆU THỬ NGHIỆM) ---
add_shortcode('ska_test_data', function ($atts) {
    if (!class_exists('\Ska\Data\Core\Database_Engine'))
        return __( 'Missing DB Engine', 'ska-data-pro' );
    $atts = shortcode_atts(['table' => '', 'id' => '', 'col' => ''], $atts);

    // Gọi thẳng Cỗ máy Query vòng qua Backend Hook
    $row = apply_filters('ska_data_get_row', null, $atts['table'], intval($atts['id']));

    if (is_array($row) && isset($row[$atts['col']])) {
        return sprintf(
            '<b>%s</b> <span style="color:green">%s</span>',
            esc_html__( 'Result from DB:', 'ska-data-pro' ),
            esc_html($row[$atts['col']])
        );
    }
    return '<i>' . esc_html__( 'No data found. Please check table name/ID!', 'ska-data-pro' ) . '</i>';
});
// --- ĐOẠN CODE TEST TRỰC TIẾP EXTENSIBILITY DATA PROVIDERS ---
add_shortcode('ska_test_ext', function () {
    if (!class_exists('\Ska\Builder\Logic\Core'))
        return __( '<i>Ska Builder Core is not enabled (Lack of Logic Engine)</i>', 'ska-data-pro' );

    $raw_html = '
    <div style="padding:15px; border: 2px dashed #10b981; border-radius: 8px; background: #ecfdf5; margin-top:20px;">
        <h4 style="color:#047857; margin-top:0;">' . esc_html__( '1. Connection status (Single):', 'ska-data-pro' ) . '</h4>
        <p><strong>Status:</strong> {{test:status}}</p>
        <p><strong>Server Time:</strong> {{test:time}}</p>
        
        <h4 style="color:#047857;">' . esc_html__( '2. Virtual Object Array Loop Machine (WooCommerce Loop Simulator):', 'ska-data-pro' ) . '</h4>
        <ul style="padding-left: 20px;">
            {{#foreach test:mock_users}}
            <li style="margin-bottom: 5px;">
                User Name: <b>{{test:name}}</b><br>
                Role: <span style="background:#d1fae5; padding:2px 8px; border-radius:4px; font-size:12px;">{{test:role}}</span>
            </li>
            {{/foreach}}
        </ul>
    </div>';

    // Gọi thẳng lõi Logic Engine biên dịch đoạn HTML thô này ra kết quả Frontend
    return \Ska\Builder\Logic\Core::instance()->compile($raw_html);
});

// --- SHORTCODE KIỂM CHỨNG TÍNH NĂNG VIRTUAL ROLLUP ---
// Cách dùng: [ska_dump_table table="wp_ska_data_hoc_sinh"]
add_shortcode('ska_dump_table', function ($atts) {
    if (!class_exists('\Ska\Data\Core\Data_Fetcher'))
        return __( 'Missing Core', 'ska-data-pro' );

    global $wpdb;
    $atts = shortcode_atts(['table' => ''], $atts);
    $table = $atts['table'];

    if (empty($table))
        return '<b style="color:red">' . esc_html__( 'No table="" parameter was entered', 'ska-data-pro' ) . '</b>';

    $fetcher = new \Ska\Data\Core\Data_Fetcher();
    // Kéo 5 dòng đầu tiên, quá trình này tự động kích hoạt Cỗ máy Enrich (Rollup & Relation)
    $args = array(
        'orderby' => 'id',
        'order' => 'DESC'
    );
    $rows = $fetcher->get_table_rows($table, $args, 5);

    if (empty($rows)) {
        return sprintf( '<i>%s</i>', esc_html( sprintf( __( 'Table %s is empty or does not exist.', 'ska-data-pro' ), $table ) ) );
    }

    // Build cấu trúc HTML để giả lập Frontend Component
    $html = '<div style="font-family: sans-serif; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0;">';
    $html .= sprintf(
        '<h3 style="margin-top:0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">%s <span style="color:#059669">%s</span>)</h3>',
        esc_html__( 'Frontend Returned Data (Table:', 'ska-data-pro' ),
        esc_html( $table )
    );

    $html .= '<div style="overflow-x:auto;"><table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">';

    // Header
    $first_row = $rows[0];
    $html .= '<thead><tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569;">';
    foreach (array_keys($first_row) as $col) {
        if ($col === 'chi_tien' || $col === 'gia_tien' || strpos($col, 'tien') !== false) {
            $html .= "<th style='padding: 12px; border-bottom: 1px solid #ddd; color: #d97706;'>🔥 $col (Rollup)</th>";
        } else {
            $html .= "<th style='padding: 12px; border-bottom: 1px solid #ddd;'>$col</th>";
        }
    }
    $html .= '</tr></thead><tbody>';

    // Body
    foreach ($rows as $row) {
        $html .= '<tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'#fff\'">';
        foreach ($row as $col_key => $val) {
            $display_val = esc_html($val);

            // Nếu là Mảng JSON do Cỗ máy Rollup nhả ra
            if (is_string($val) && (strpos($val, '[') === 0 || strpos($val, '{') === 0)) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Dễ nhìn trên Frontend
                    $display_val = sprintf(
                        '<i>%s -> </i> <strong style="color:#059669">%s</strong>',
                        esc_html__( '(Virtual JSON array)', 'ska-data-pro' ),
                        esc_html( print_r( $decoded, true ) )
                    );
                }
            } else if ($val === '' || $val === null) {
                $display_val = '<em style="color:#cbd5e1">' . esc_html__( 'NULL Under Database', 'ska-data-pro' ) . '</em>';
            }

            $html .= "<td style='padding: 12px; vertical-align: top;'>{$display_val}</td>";
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';

    $html .= '<div style="margin-top: 15px; padding: 12px; background: #fffbeb; border-left: 4px solid #fbbf24; border-radius: 4px; font-size: 13px; color: #b45309;">';
    $html .= sprintf(
        /* translators: 1: MySQL DB code tag, 2: Data_Fetcher code tag */
        __( '<strong>The Mystery:</strong> You see, even though the value under %1$s is NULL, when calling the Frontend with PHP, the %2$s machine has silently decompiled the ID and filled in the text for you! This is VIRTUAL DATA!', 'ska-data-pro' ),
        '<code>MySQL DB</code>',
        '<code style="background:#fcd34d; padding:2px 4px; border-radius:4px;">Data_Fetcher</code>'
    );
    $html .= '</div>';

    $html .= '</div>';

    return $html;
});

// --- TOOL MIGRATION TỰ ĐỘNG CSV -> JSON (Chạy 1 lần) ---
add_shortcode('ska_migrate_json', function () {
    global $wpdb;
    $dictionary = get_option('ska_data_dictionary', array());
    if (empty($dictionary))
        return __( 'There is no data dictionary.', 'ska-data-pro' );

    $global_prefix = $wpdb->prefix . 'ska_data_';
    $log = '<h3>' . esc_html__( 'Start converting TEXT to JSON:', 'ska-data-pro' ) . '</h3><ul>';

    foreach ($dictionary as $clean_table_name => $table_config) {
        $table_name = $global_prefix . $clean_table_name;

        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if ($exists !== $table_name)
            continue;

        $alter_queries = array();
        foreach ($table_config as $col_slug => $col_meta) {
            if ($col_slug === '__table_info')
                continue;

            $type = isset($col_meta['type']) ? $col_meta['type'] : '';
            if (in_array($type, array('multi_select', 'relation', 'rollup'))) {
                // 1. Kéo toàn bộ data cũ của cột này lên
                $rows = $wpdb->get_results("SELECT id, `{$col_slug}` FROM `{$table_name}` WHERE `{$col_slug}` IS NOT NULL AND `{$col_slug}` != ''");
                $fixed_count = 0;
                foreach ($rows as $row) {
                    $val = trim($row->$col_slug);
                    // Nếu nó chưa phải dạng mảng JSON (không bắt đầu bằng [ hoặc {)
                    if (strpos($val, '[') !== 0 && strpos($val, '{') !== 0) {
                        $arr = array_map('trim', explode(',', $val));
                        // Bỏ phần tử rỗng
                        $arr = array_filter($arr, function ($v) {
                            return $v !== ''; });
                        $valid_json = wp_json_encode(array_values($arr));
                        $wpdb->update($table_name, array($col_slug => $valid_json), array('id' => $row->id));
                        $fixed_count++;
                    }
                }

                $alter_queries[] = "MODIFY COLUMN `{$col_slug}` JSON";
                $log .= sprintf(
                    /* translators: 1: fixed count, 2: column slug, 3: table name */
                    __( '<li>Fixed %1$d lines containing old CSV to JSON for column <b>%2$s</b> (Table %3$s)</li>', 'ska-data-pro' ),
                    $fixed_count,
                    esc_html( $col_slug ),
                    esc_html( $table_name )
                );
            }
        }

        if (!empty($alter_queries)) {
            $sql = "ALTER TABLE `{$table_name}` " . implode(', ', $alter_queries);
            $result = $wpdb->query($sql);
            if (false === $result) {
                $log .= sprintf(
                    '<li><b style="color:red;">%s</b> %s</li>',
                    esc_html( sprintf( __( 'ERROR IN FORCING TABLE %s:', 'ska-data-pro' ), $table_name ) ),
                    esc_html( $wpdb->last_error )
                );
            } else {
                $log .= sprintf(
                    '<li><b style="color:green;">%s</b></li>',
                    esc_html( sprintf( __( 'SUCCESSFULLY CONVERTED TABLE %s TO JSON!', 'ska-data-pro' ), $table_name ) )
                );
            }
        }
    }

    $log .= '</ul><p>' . esc_html__( 'All completed. You can refresh the Schema section!', 'ska-data-pro' ) . '</p>';
    return $log;
});
