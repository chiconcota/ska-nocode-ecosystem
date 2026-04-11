<?php
namespace Ska\Logic\SkaFX;

defined( 'ABSPATH' ) || exit;

/**
 * Các Node (Nút Phả Hệ) của Cây AST
 */
abstract class SkaFX_AST_Node {}

class Node_Number extends SkaFX_AST_Node {
    public $value;
    public function __construct($value) { $this->value = $value; }
}

class Node_String extends SkaFX_AST_Node {
    public $value;
    public function __construct($value) { $this->value = $value; }
}

class Node_Variable extends SkaFX_AST_Node {
    public $name;
    public function __construct($name) { $this->name = $name; }
}

class Node_Function extends SkaFX_AST_Node {
    public $name;
    public $args; // Mảng các Node biểu thức con
    public function __construct($name, $args) { $this->name = $name; $this->args = $args; }
}

class Node_BinaryOp extends SkaFX_AST_Node {
    public $left;
    public $operator;
    public $right;
    public function __construct($left, $operator, $right) {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }
}

class Node_Assign extends SkaFX_AST_Node {
    public $var_name; // string: tên biến tự do `tuoi`
    public $expression; // Node
    public function __construct($var_name, $expression) {
        $this->var_name = $var_name;
        $this->expression = $expression;
    }
}

/**
 * Trạm 2: The Parser (Cỗ Máy Ráp Cây Hình Thái)
 * Dùng thuật toán Pratt Parsing (hoặc Recursive Descent Parser kết hợp vớt Precedence)
 */
class SkaFX_Parser {
    
    private $tokens;
    private $position = 0;
    private $length;

    public function __construct( array $tokens ) {
        $this->tokens = $tokens;
        $this->length = count( $tokens );
    }

    /**
     * Dòng chảy chính: Mồi hệ thống nuốt nhiều câu lệnh (Statements) tách bằng dấu chấm phẩy
     */
    public function parse() {
        $statements = [];
        
        while ( ! $this->is_at_end() ) {
            $stmt = $this->parse_statement();
            if ($stmt) {
                $statements[] = $stmt;
            }
            // Nuốt dấu chấm phẩy nếu có
            if ( $this->match( SkaFX_Lexer::T_SEMICOLON ) ) {
                $this->consume( SkaFX_Lexer::T_SEMICOLON );
            }
        }
        
        return $statements;
    }

    private function parse_statement() {
        // Nêu gặp Keyword `var`, đây là dạng Gán Biến (Assignment statement)
        if ( $this->match( SkaFX_Lexer::T_KW_VAR ) ) {
            $this->consume( SkaFX_Lexer::T_KW_VAR );
            
            // Bắt buộc sau chữ `var` phải là tên biến (T_IDENT)
            if ( $this->match( SkaFX_Lexer::T_IDENT ) ) {
                $var_name_token = $this->consume( SkaFX_Lexer::T_IDENT );
                
                // Kế tiếp phải là dấu bằng `=`
                $op_token = $this->consume( SkaFX_Lexer::T_OP );
                if ( $op_token['value'] !== '=' ) {
                    throw new SkaFX_Syntax_Error("Lỗi khai báo: Cần dấu `=` sau tên biến ở dòng " . $op_token['line']);
                }
                
                // Phần sau dấu bằng là Expression
                $expr = $this->parse_expression();
                return new Node_Assign( $var_name_token['value'], $expr );
            } else {
                throw new SkaFX_Syntax_Error("Lỗi khai báo: Cần một tên biến hợp lệ sau `var`.");
            }
        }

        // Còn lại (Bình thường), đây là một biểu thức (Expression statement)
        return $this->parse_expression();
    }

    /**
     * Pratt Parsing lõi cho phép tính ưu tiên. Mặc định ưu tiên hạng 0 (Lowest)
     */
    private function parse_expression( $precedence = 0 ) {
        // Hút phần bên trái (Prefix)
        $left = $this->parse_prefix();

        // Xử lý các toán tử theo cành phải (Infix) dựa vào sự ưu tiên (Precedence)
        while ( ! $this->is_at_end() && $precedence < $this->get_precedence() ) {
            $left = $this->parse_infix( $left );
        }

        return $left;
    }

    private function parse_prefix() {
        $token = $this->consume_any();

        switch ( $token['type'] ) {
            case SkaFX_Lexer::T_NUMBER:
                return new Node_Number( $token['value'] );
            
            case SkaFX_Lexer::T_STRING:
                return new Node_String( $token['value'] );
            
            case SkaFX_Lexer::T_VAR: // Ví dụ: [doctors.rating]
                return new Node_Variable( $token['value'] );
            
            case SkaFX_Lexer::T_IDENT:
                // Nếu là Hàm PhepTinh(a, b)
                if ( $this->match( SkaFX_Lexer::T_LPAREN ) ) {
                    $this->consume( SkaFX_Lexer::T_LPAREN );
                    $args = [];
                    // Đọc tham số trong ngoặc
                    if ( ! $this->match( SkaFX_Lexer::T_RPAREN ) ) {
                        do {
                            $args[] = $this->parse_expression();
                        } while ( $this->match( SkaFX_Lexer::T_COMMA ) && $this->consume( SkaFX_Lexer::T_COMMA ) );
                    }
                    $this->consume( SkaFX_Lexer::T_RPAREN, "Đợi dấu ngoặc đóng ')' cho hàm " . $token['value'] );
                    return new Node_Function( strtoupper( $token['value'] ), $args );
                }
                // Nếu không có ngoặc, thì đây là gọi Local Variable (Biến ảo tạo bằng 'var')
                return new Node_Variable( $token['value'] );
            
            case SkaFX_Lexer::T_LPAREN:
                // Nhóm biểu thức có ngoặc `( 1 + 2 )`
                $expr = $this->parse_expression();
                $this->consume( SkaFX_Lexer::T_RPAREN, "Cần hàm dấu ngoặc đóng ')'" );
                return $expr;
        }

        throw new SkaFX_Syntax_Error("Ký tự bất định không thể bóc tách Prefix tại dòng " . $token['line'] . ": " . print_r($token, true));
    }

    private function parse_infix( $left ) {
        $op_token = $this->consume_any();
        $precedence = $this->get_precedence( $op_token );

        // Quét cành phải dựa vào luật ưu tiên.
        $right = $this->parse_expression( $precedence );

        return new Node_BinaryOp( $left, $op_token['value'], $right );
    }

    /**
     * Bảng thứ tự ưu tiên (BODMAS)
     */
    private function get_precedence( $token = null ) {
        if ( ! $token ) {
            if ( $this->is_at_end() ) return 0;
            $token = $this->peek();
        }

        if ( $token['type'] === SkaFX_Lexer::T_LOGIC_OP ) {
            // AND, OR
            return 10;
        }

        if ( $token['type'] === SkaFX_Lexer::T_OP ) {
            switch ( $token['value'] ) {
                case '=':
                case '==':
                case '!=':
                    return 20;
                case '>':
                case '<':
                case '>=':
                case '<=':
                    return 30;
                case '+':
                case '-':
                    return 40;
                case '*':
                case '/':
                    return 50;
            }
        }
        return 0; // Không phải Toán tử (Có thể là dấu ; hoặc EOF)
    }

    // Các hàm trợ lý siêu tốc (Helpers)
    private function peek() {
        return $this->tokens[ $this->position ];
    }

    private function is_at_end() {
        return $this->peek()['type'] === SkaFX_Lexer::T_EOF;
    }

    private function match( $type ) {
        if ( $this->is_at_end() ) return false;
        return $this->peek()['type'] === $type;
    }

    private function consume( $type, $error_msg = '' ) {
        if ( $this->match( $type ) ) {
            $token = $this->tokens[ $this->position ];
            $this->position++;
            return $token;
        }
        $err = $error_msg ? $error_msg : "Lỗi cú pháp: Ký tự không đúng chuẩn.";
        throw new SkaFX_Syntax_Error( $err );
    }

    private function consume_any() {
        $token = $this->tokens[ $this->position ];
        $this->position++;
        return $token;
    }
}
