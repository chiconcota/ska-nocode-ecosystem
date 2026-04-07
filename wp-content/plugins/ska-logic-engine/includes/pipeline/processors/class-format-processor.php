<?php
defined( 'ABSPATH' ) || exit;

class Ska_Format_Processor implements Ska_Logic_Node {
    
    /**
     * Rèn Dữ Liệu (Format & Compute) trên Memory
     * Cấu trúc mong đợi: 
     * $config['formats'] = [
     *     [ 'source_key' => 'tieu_de', 'action' => 'to_slug', 'target_key' => 'tieu_de_slug' ],
     *     [ 'source_key' => 'n_sinh', 'action' => 'to_date_ymd', 'target_key' => 'DOB' ],
     *     [ 'source_key' => 'my_tag', 'action' => 'lowercase', 'target_key' => '' ] // Tự đè nguồn
     * ]
     */
    public function execute( $payload, $config ) {
        
        // Hỗ trợ cả 2 chuẩn: Mảng nhiều format (Json Graph Mở rộng) hoặc cấu hình tĩnh 1 format từ UI MVP
        $formats = $config['formats'] ?? [];
        if ( empty( $formats ) && ! empty( $config['action'] ) && ! empty( $config['source_key'] ) ) {
            $formats = [ $config ]; // Đúc thành mảng chuẩn để chạy vòng lặp
        }

        if ( empty( $formats ) || ! is_array( $formats ) ) {
            return $payload;
        }

        foreach ( $formats as $format ) {
            $source_key = $format['source_key'] ?? '';
            $action     = $format['action'] ?? '';
            $target_key = $format['target_key'] ?? '';
            
            if ( empty( $target_key ) ) {
                $target_key = $source_key; // Mặc định tự ghi đè biến nguồn
            }
            
            // Bỏ qua nếu Nguồn không tồn tại trong data
            $actual_source = $source_key;
            if ( ! isset( $payload[ $actual_source ] ) ) {
                // Fallback: Khi form submit qua HTTP, PHP tự động biến khoảng trắng trong name="" thành dấu gạch dưới. 
                // Vd: "ho va ten" -> "ho_va_ten"
                $underscored = str_replace( ' ', '_', $source_key );
                if ( isset( $payload[ $underscored ] ) ) {
                    $actual_source = $underscored;
                } else {
                    continue;
                }
            }
            
            $val = $payload[ $actual_source ];
            
            // Chỉ ép kiểu / phân giải chuỗi
            if ( is_string( $val ) || is_numeric( $val ) ) {
                switch ( $action ) {
                    case 'to_slug':
                        $val = sanitize_title( $val );
                        break;
                        
                    case 'to_date_ymd':
                        // Đảm bảo strtotime không sinh mảng rỗng do format lạ
                        $time = strtotime( $val );
                        if ( $time !== false ) {
                            $val = date( 'Y-m-d', $time );
                        }
                        break;
                        
                    case 'to_date_dmY':
                        $time = strtotime( $val );
                        if ( $time !== false ) {
                            $val = date( 'd/m/Y', $time );
                        }
                        break;
                        
                    case 'trim':
                        $val = trim( $val );
                        break;
                        
                    case 'uppercase':
                        if ( function_exists( 'mb_strtoupper' ) ) {
                            $val = mb_strtoupper( $val, 'UTF-8' );
                        } else {
                            $val = strtoupper( $val );
                        }
                        break;
                        
                    case 'lowercase':
                        if ( function_exists( 'mb_strtolower' ) ) {
                            $val = mb_strtolower( $val, 'UTF-8' );
                        } else {
                            $val = strtolower( $val );
                        }
                        break;
                        
                    case 'copy':
                    default:
                        // Giữ nguyên, chỉ copy sang Target
                        break;
                }
            }
            
            // Ghi Cột Đích (Dán lên RAM)
            $payload[ $target_key ] = $val;
        }

        return $payload;
    }
}
