<?php
namespace Ska\Logic\SkaFX;

defined( 'ABSPATH' ) || exit;

/**
 * Trạm: The Context Resolver
 * Phân giải các biến tương đối (như [doctors.name] hay [name]) thành địa chỉ Vật lý của MySQL 
 * (ví dụ wp_ska_data_clinic_doctors) dựa trên Từ điển Schema và URL Header.
 */
class SkaFX_Context_Resolver {

    /**
     * Dò tìm Ngữ cảnh của Biến SkaFX
     * @param string $var_name Tên biến người dùng nhập (Vd: "name", "doctors.name", "clinic.doctors.name")
     * @param array $context Mảng Ngữ cảnh (chứa GLOBAL_ID và GLOBAL_TABLE)
     * @return array|null Mảng chứa [ 'table_name' => 'wp_ska_data_xyz', 'column' => 'abc', 'record_id' => 123 ] hoặc rỗng
     */
    public static function resolve( $var_name, $context ) {
        // Biến môi trường bắt buộc
        if ( ! isset( $context['GLOBAL_ID'] ) || empty( $context['GLOBAL_ID'] ) ) {
            return null;
        }

        $record_id = intval( $context['GLOBAL_ID'] );
        if ( $record_id <= 0 ) {
            return null;
        }

        $parts = explode( '.', $var_name );
        $count = count( $parts );

        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $target_table_name = '';
        $target_col = '';

        // Trường hợp 1: Biến Mức 3 (Tuyệt Đối) [app.model.col]
        if ( $count === 3 ) {
            $app_id = sanitize_key( $parts[0] );
            $model  = sanitize_key( $parts[1] );
            $target_col = $parts[2];

            if ( $app_id !== 'uncategorized' && strpos( $app_id, 'app_' ) !== 0 ) {
                $app_id = 'app_' . $app_id;
            }

            $target_table_name = $prefix . 'ska_data_' . $app_id . '_' . $model;
        }
        
        // Trường hợp 2: Biến Mức 2 (Tương Đối 1 Nửa) [model.col]
        elseif ( $count === 2 ) {
            $model = sanitize_key( $parts[0] );
            $target_col = $parts[1];
            
            $schema_match = false;

            // Ưu tiên 1: Lấy ngữ cảnh URL (GLOBAL_TABLE thường chứa App Slug) để rèn Table trước khi Xoi Từ điển
            if ( isset( $context['GLOBAL_TABLE'] ) && ! empty( $context['GLOBAL_TABLE'] ) ) {
                $app_slug = sanitize_key( $context['GLOBAL_TABLE'] );
                if ( $app_slug !== 'uncategorized' && strpos( $app_slug, 'app_' ) !== 0 ) {
                    $app_slug = 'app_' . $app_slug;
                }
                
                $guessed_table = $prefix . 'ska_data_' . $app_slug . '_' . $model;
                if ( self::is_table_in_dictionary( $guessed_table ) ) {
                    $schema_match = $guessed_table;
                }
            }
            
            // Xoi Từ điển Dictionary tìm bảng nào có đuôi khớp với _model
            if ( ! $schema_match ) {
                $schema_match = self::find_table_by_suffix( $model );
            }
            
            if ( $schema_match ) {
                // Khắc phục BUG: Key trong Dictionary vốn DĨ ĐÃ CHỨA sẵn tiền tố wp_ska_data_ nên không nối thêm nữa!
                if ( strpos( $schema_match, $prefix . 'ska_data_' ) === 0 ) {
                    $target_table_name = $schema_match;
                } else {
                    $target_table_name = $prefix . 'ska_data_' . $schema_match; 
                }
            } else {
                // Không tìm thấy trong Schema, bốc lụi bảng nhỡ user đang gõ tên đầy đủ mà ko có config
                if ( strpos( $model, $prefix . 'ska_data_' ) === 0 ) {
                    $target_table_name = $model;
                } else {
                    $target_table_name = $prefix . 'ska_data_' . $model;
                }
            }
        }
        
        // Trường hợp 3: Biến Mức 1 (Ngữ Cảnh Đơn Tĩnh) [col]
        elseif ( $count === 1 ) {
            $target_col = $parts[0];
            
            if ( isset( $context['GLOBAL_TABLE'] ) && ! empty( $context['GLOBAL_TABLE'] ) ) {
                $table_alias = sanitize_key( $context['GLOBAL_TABLE'] );
                
                // Xem table trên URL truyền lên dạng "ska_data_xxx" hay "xxx"
                // Hoặc có thể nó chỉ là "doctors" và ta sẽ dùng cơ chế tìm đuôi Suffix giống Mức 2!
                $schema_match = self::find_table_by_suffix( $table_alias );
                if ( $schema_match ) {
                    if ( strpos( $schema_match, $prefix . 'ska_data_' ) === 0 ) {
                        $target_table_name = $schema_match;
                    } else {
                        $target_table_name = $prefix . 'ska_data_' . $schema_match;
                    }
                } else {
                    if ( strpos( $table_alias, $prefix . 'ska_data_' ) === 0 ) {
                        $target_table_name = $table_alias;
                    } else {
                        $target_table_name = $prefix . 'ska_data_' . $table_alias;
                    }
                }
            } else {
                // Không có Tên bảng truyền vào qua GET hay Context thì mù hoàn toàn
                return null;
            }
        } else {
            return null; // Tạp nham, sai format
        }

        return [
            'table_name' => $target_table_name,
            'column'     => $target_col,
            'record_id'  => $record_id
        ];
    }

    /**
     * Tra cứu một bảng có nằm trong Từ điển hay không
     */
    private static function is_table_in_dictionary( $table_name ) {
        static $dict_cache = null;
        if ( $dict_cache === null ) {
            $dict_cache = get_option( 'ska_data_dictionary', [] );
        }
        return isset( $dict_cache[ $table_name ] );
    }

    /**
     * Thuật toán Tìm Bảng từ Kí tự Hậu tố thông qua Từ Điển
     * @throws \Exception Khi tìm thấy nhiều hơn 1 bảng khớp (Collision)
     */
    private static function find_table_by_suffix( $model_query ) {
        // Caching Dictionary Lookup để không đọc DB liên tục
        static $dict_cache = null;
        if ( $dict_cache === null ) {
            $dict_cache = get_option( 'ska_data_dictionary', [] );
        }

        if ( empty( $dict_cache ) || ! is_array( $dict_cache ) ) {
            return false;
        }

        // Ưu tiên 1: Lỡ $model_query khớp Mật Danh đầy đủ 100% của bảng (VD: app_clinic_doctors)
        if ( isset( $dict_cache[ $model_query ] ) ) {
            return $model_query;
        }

        // Ưu tiên 2: Tìm phần đuôi $model_query ở dạng _model
        // VD: app_A_users (thì substr đuôi sẽ là _users)
        $search_suffix = '_' . $model_query;
        $matches = [];
        
        foreach ( $dict_cache as $full_alias => $schema ) {
            $len = strlen( $search_suffix );
            if ( $len > 0 && substr( $full_alias, -$len ) === $search_suffix ) {
                $matches[] = $full_alias;
            }
            // Lắm lúc user tạo App = Uncategorized, bảng tên là `doctors` chứ không có `app_doctors`
            elseif ( $full_alias === $model_query ) {
                $matches[] = $full_alias;
            }
        }
        
        $matches = array_unique($matches);

        // Phát hiện XUNG ĐỘT (Collision)
        if ( count( $matches ) > 1 ) {
            throw new \Exception( sprintf( "Trùng lặp Context: Tìm thấy %d bảng có đuôi '%s' (%s). Vui lòng gõ rõ Tên App (VD: [appA.%s.tencot]) để hệ thống phân biệt.", count($matches), $model_query, implode(', ', $matches), $model_query ) );
        }
        
        if ( count( $matches ) === 1 ) {
            return $matches[0];
        }
        
        return false;
    }
}
