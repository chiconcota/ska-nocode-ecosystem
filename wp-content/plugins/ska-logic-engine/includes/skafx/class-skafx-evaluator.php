<?php
namespace Ska\Logic\SkaFX;

defined( 'ABSPATH' ) || exit;

/**
 * Trạm 3: The Evaluator (Cỗ Máy Phán Xét & Tính Toán)
 * Duyệt cây AST và thực thi kết quả thực tế.
 */
class SkaFX_Evaluator {
    
    // Kho chứa biến tạm khi Lễ tân xài `var x = 1`
    private $symbol_table = []; 
    
    // Kho chứa dữ liệu Bảng Phẳng / Context truyền vào (VD: ['rating' => 4])
    private $row_context = [];  

    public function __construct( array $row_context = [] ) {
        $this->row_context = $row_context;
    }

    /**
     * Chạy luồng Kịch bản (Nhiều statements)
     */
    public function evaluate_script( $ast_statements ) {
        $last_value = null;
        
        foreach ( $ast_statements as $node ) {
            $last_value = $this->evaluate( $node );
        }
        
        return $last_value;
    }

    /**
     * Tính toán Đệ quy trên từng Nhánh cây
     */
    private function evaluate( SkaFX_AST_Node $node ) {
        
        // 1. Phân loại Giá trị Đơn (Nguyên thủy)
        if ( $node instanceof Node_Number ) {
            return $node->value;
        }

        if ( $node instanceof Node_String ) {
            return $node->value;
        }

        // 2. Chế độ Lưu Biến Tạm (Memory Assignment)
        if ( $node instanceof Node_Assign ) {
            $val = $this->evaluate( $node->expression );
            $this->symbol_table[ $node->var_name ] = $val;
            return $val;
        }

        // 3. Chế độ Móc Biến / Móc Context
        if ( $node instanceof Node_Variable ) {
            $var_name = $node->name;

            // Ưu tiên 1: Quét biến ảo trong RAM (VD: Lễ tân vừa gán `var tuoi = 18`)
            if ( array_key_exists( $var_name, $this->symbol_table ) ) {
                return $this->symbol_table[ $var_name ];
            }

            // Ưu tiên 2: Quét mảng dữ liệu Dòng nội khu (Tự nhận diện `[nam_kinh_nghiem]`)
            if ( array_key_exists( $var_name, $this->row_context ) ) {
                return $this->row_context[ $var_name ];
            }

            // Ưu tiên 3: Quét chéo Object (Smart Object / App Context)
            // Ví dụ gõ `[clinic.doctors.age]`. Tính năng này sẽ được kích hoat ở Update sau khi Database Schema có bảng App Entities.
            // ... (Context Manager External LooKup)

            // Nuốt lỗi an toàn: Trả NULL thay vì ném Lỗi để tránh Error 500 nếu cột bay màu.
            return null; 
        }

        // 4. Lõi Tính Toán Toán Học & Logic
        if ( $node instanceof Node_BinaryOp ) {
            $left = $this->evaluate( $node->left );
            $right = $this->evaluate( $node->right );

            switch ( $node->operator ) {
                case '+':  return $left + $right;
                case '-':  return $left - $right;
                case '*':  return $left * $right;
                // Chặn lỗi chia cho số 0
                case '/':  return $right != 0 ? $left / $right : 0; 
                // Cú pháp gõ 1 dấu = vẫn coi là phép so sánh theo chuẩn Nocode
                case '=':  return $left == $right; 
                case '!=': return $left != $right;
                case '>':  return $left > $right;
                case '<':  return $left < $right;
                case '>=': return $left >= $right;
                case '<=': return $left <= $right;
                case 'AND': return $left && $right;
                case 'OR':  return $left || $right;
            }
        }

        // 5. Ngân Hàng Hàm Nội Bộ (Built-in Functions)
        if ( $node instanceof Node_Function ) {
            $fn_name = $node->name;

            // Xử lý Short-circuit (Chặn sớm nhánh sai) cho riêng hàm IF để tránh lãng phí RAM
            if ( $fn_name === 'IF' ) {
                if ( count($node->args) < 3 ) throw new SkaFX_Runtime_Error("Hàm IF cần 3 tham số: IF(Điều kiện, Đúng, Sai)");
                $condition = $this->evaluate( $node->args[0] );
                if ( $condition ) {
                    return $this->evaluate( $node->args[1] );
                } else {
                    return $this->evaluate( $node->args[2] );
                }
            }

            // Với các hàm khác, bóc vỏ Evaluate toàn bộ Biến
            $arg_vals = [];
            foreach ( $node->args as $arg ) {
                $arg_vals[] = $this->evaluate( $arg );
            }

            switch ( $fn_name ) {
                case 'CONCAT':
                    return implode( '', $arg_vals );
                case 'LOWER':
                    return strtolower( $arg_vals[0] );
                case 'UPPER':
                    return strtoupper( $arg_vals[0] );
                case 'ROUND':
                    $precision = isset( $arg_vals[1] ) ? $arg_vals[1] : 0;
                    return round( $arg_vals[0], $precision );
                case 'IS_NULL':
                    return is_null( $arg_vals[0] );
                default:
                    throw new SkaFX_Runtime_Error("Hàm không tồn tại trong từ điển SkaFX: " . $fn_name);
            }
        }

        throw new SkaFX_Runtime_Error("Cỗ máy không nhận diện được loại Node: Phá kiến trúc AST.");
    }
}

/**
 * Controller Đại Diện: Tàu Cảng public đón Lệnh
 */
class SkaFX_Engine {
    
    /**
     * API Phóng tên lửa tính toán
     * @param string $script_string Chuỗi SkaFX người dùng gõ
     * @param array $context Mảng dữ liệu bơm vào vòng lặp
     * @return mixed Kết quả biểu thức hoặc False nếu lỗi
     */
    public static function execute( $script_string, $context = [] ) {
        // Tắt early return khi chuỗi rỗng
        if ( trim( $script_string ) === '' ) {
            return true; // Mặc định chuỗi rỗng mang ý nghĩa True (Để Render hiển thị Block)
        }

        try {
            // Vận hành Dòng chuyền
            $lexer = new SkaFX_Lexer( $script_string );
            $tokens = $lexer->tokenize();
            
            $parser = new SkaFX_Parser( $tokens );
            $statements = $parser->parse();
            
            $evaluator = new SkaFX_Evaluator( $context );
            return $evaluator->evaluate_script( $statements );
            
        } catch ( SkaFX_Syntax_Error | SkaFX_Runtime_Error $e ) {
             // Đạt tiêu chuẩn Nuốt Lỗi: Ghi log, Không là sập web frontend.
             error_log("[SkaFX Engine Failure]: " . $e->getMessage());
             return false;
        }
    }
}
