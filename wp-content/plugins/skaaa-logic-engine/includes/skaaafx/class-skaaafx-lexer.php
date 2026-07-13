<?php
namespace Skaaa\Logic\SkaaaFX;

defined( 'ABSPATH' ) || exit;

/**
 * Trạm 1: The Lexer (Trình Băm Từ Vựng)
 * Quét chuỗi Code SkaaaFX cực nhanh bằng RegEx để bóc tách thành các hạt phân tử (Tokens).
 */
class SkaaaFX_Lexer {
    
    // Khai báo Từ điển Hạt (Token Types)
    const T_VAR       = 'VAR';       // [biến_số]
    const T_IDENT     = 'IDENT';     // Tên hàm (IF, CONCAT) hoặc tên biến tự do (trong var x)
    const T_NUMBER    = 'NUMBER';    // Số nguyên/thực
    const T_STRING    = 'STRING';    // Chuỗi chữ "abc" hoặc 'abc'
    const T_OP        = 'OP';        // Các toán tử: +, -, *, /, >=, <=, !=, =, >, <
    const T_LOGIC_OP  = 'LOGIC_OP';  // AND, OR
    const T_LPAREN    = 'LPAREN';    // (
    const T_RPAREN    = 'RPAREN';    // )
    const T_COMMA     = 'COMMA';     // ,
    const T_SEMICOLON = 'SEMICOLON'; // ;
    const T_KW_VAR    = 'KW_VAR';    // Từ khóa "var"
    const T_EOF       = 'EOF';       // Kết thúc tệp

    private $input;
    private $position = 0;
    private $length = 0;
    private $line = 1;

    public function __construct( $input ) {
        $this->input = $input;
        $this->length = strlen( $input );
    }

    /**
     * Chạy máy Băm. Nuốt trọn chuỗi và nhả ra Mảng Tokens.
     */
    public function tokenize() {
        $tokens = [];
        
        while ( $this->position < $this->length ) {
            $char = $this->input[ $this->position ];

            // 1. Dọn rác: Khoảng trắng và Dấu xuống dòng
            if ( ctype_space( $char ) ) {
                if ( $char === "\n" ) {
                    $this->line++;
                }
                $this->position++;
                continue;
            }

            // 2. Dọn rác: Bỏ qua Comment (// hoặc /* */)
            if ( $char === '/' ) {
                $next_char = $this->peek( 1 );
                if ( $next_char === '/' ) {
                    // Dòng comment `//` - Bỏ qua tới cuối dòng
                    $this->skip_until( "\n" );
                    continue;
                } elseif ( $next_char === '*' ) {
                    // Cụm comment `/* */` - Bỏ qua tới khi gặp */
                    $this->skip_until_string( "*/" );
                    continue;
                }
            }

            // 3. Quét Số học (Numbers)
            if ( is_numeric( $char ) || ( $char === '.' && is_numeric( $this->peek(1) ) ) ) {
                $tokens[] = $this->consume_number();
                continue;
            }

            // 4. Quét Chuỗi (Strings)
            if ( $char === '"' || $char === "'" ) {
                $tokens[] = $this->consume_string( $char );
                continue;
            }

            // 5. Quét Biến Bọc Ngoặc (Variables: [table.field])
            if ( $char === '[' ) {
                $tokens[] = $this->consume_variable();
                continue;
            }

            // 6. Quét Mã Nhận Diện (Identifiers/Hàm/Keywords)
            if ( preg_match( '/[a-zA-Z_$]/', $char ) ) {
                $tokens[] = $this->consume_identifier();
                continue;
            }

            // 7. Quét Các ký tự rải rác và Toán Tử Rời Rạc
            if ( $char === '(' ) {
                $tokens[] = $this->make_token( self::T_LPAREN, '(' );
                $this->position++;
                continue;
            }
            if ( $char === ')' ) {
                $tokens[] = $this->make_token( self::T_RPAREN, ')' );
                $this->position++;
                continue;
            }
            if ( $char === ',' ) {
                $tokens[] = $this->make_token( self::T_COMMA, ',' );
                $this->position++;
                continue;
            }
            if ( $char === ';' ) {
                $tokens[] = $this->make_token( self::T_SEMICOLON, ';' );
                $this->position++;
                continue;
            }

            // 8. Quét Toán Tử Phức Hợp (Operators: >=, <=, !=, =, +, -, *, /)
            $op_token = $this->consume_operator();
            if ( $op_token ) {
                $tokens[] = $op_token;
                continue;
            }

            // Nếu không lọt vào bất kỳ Trạm quét nào => Ném Exception ngay lập tức.
            throw new SkaaaFX_Syntax_Error( sprintf(__( 'Unknown syntax error: Character \'%s\' at line %d', 'skaaa-logic-engine' ), $char, $this->line) );
        }

        $tokens[] = $this->make_token( self::T_EOF, null );
        return $tokens;
    }

    private function make_token( $type, $value ) {
        return [
            'type'  => $type,
            'value' => $value,
            'line'  => $this->line
        ];
    }

    private function peek( $offset = 1 ) {
        if ( $this->position + $offset < $this->length ) {
            return $this->input[ $this->position + $offset ];
        }
        return null;
    }

    private function skip_until( $target ) {
        while ( $this->position < $this->length && $this->input[ $this->position ] !== $target ) {
            $this->position++;
        }
    }

    private function skip_until_string( $target_string ) {
        $found_pos = strpos($this->input, $target_string, $this->position);
        if ($found_pos !== false) {
            // Nhảy tới đít dấu chấm dứt comment
            $this->position = $found_pos + strlen($target_string); 
        } else {
            // Không bao giờ đóng comment -> Hút cạn chuỗi
            $this->position = $this->length;
        }
    }

    private function consume_number() {
        $start = $this->position;
        $is_float = false;
        
        while ( $this->position < $this->length ) {
            $char = $this->input[ $this->position ];
            if ( is_numeric( $char ) ) {
                $this->position++;
            } elseif ( $char === '.' && !$is_float ) {
                $is_float = true;
                $this->position++;
            } else {
                break;
            }
        }
        
        $val = substr( $this->input, $start, $this->position - $start );
        return $this->make_token( self::T_NUMBER, $is_float ? (float)$val : (int)$val );
    }

    private function consume_string( $quote_type ) {
        $this->position++; // Bỏ qua dấu nháy mở
        $start = $this->position;
        
        while ( $this->position < $this->length && $this->input[ $this->position ] !== $quote_type ) {
            $this->position++;
        }
        
        $val = substr( $this->input, $start, $this->position - $start );
        
        // Nhảy qua dấu nháy đóng. Nếu thiếu dấu nháy -> Báo lỗi.
        if ( $this->position < $this->length ) {
            $this->position++;
        } else {
            throw new SkaaaFX_Syntax_Error("Lỗi cú pháp: Thiếu nháy kép/đơn đóng chuỗi tại dòng " . $this->line);
        }
        
        return $this->make_token( self::T_STRING, $val );
    }

    private function consume_variable() {
        $this->position++; // Bỏ qua ngoặc `[`
        $start = $this->position;

        while ( $this->position < $this->length && $this->input[ $this->position ] !== ']' ) {
            $this->position++;
        }

        $val = trim( substr( $this->input, $start, $this->position - $start ) );
        
        if ( $this->position < $this->length ) {
            $this->position++; // Bỏ qua ngoặc `]`
        } else {
            throw new SkaaaFX_Syntax_Error("Lỗi cú pháp: Thiếu ngoặc vuông `]` tại dòng " . $this->line);
        }

        return $this->make_token( self::T_VAR, $val );
    }

    private function consume_identifier() {
        $start = $this->position;
        
        // Quét cho phép a-z, A-Z, gạch dưới, số, $ và dấu chấm . (phục vụ biến hệ thống như trigger.nam_sinh)
        while ( $this->position < $this->length ) {
            $char = $this->input[ $this->position ];
            if ( preg_match( '/[a-zA-Z0-9_$.]/', $char ) ) {
                $this->position++;
            } else {
                break;
            }
        }

        $val = substr( $this->input, $start, $this->position - $start );
        $val_upper = strtoupper( $val );

        // Bẫy Logic (Keyword)
        if ( $val_upper === 'AND' || $val_upper === 'OR' ) {
            return $this->make_token( self::T_LOGIC_OP, $val_upper );
        }
        if ( $val === 'var' ) {
            return $this->make_token( self::T_KW_VAR, $val );
        }

        return $this->make_token( self::T_IDENT, $val );
    }

    private function consume_operator() {
        $char2 = substr( $this->input, $this->position, 2 );
        if ( in_array( $char2, ['>=', '<=', '!=', '=='] ) ) {
            $this->position += 2;
            return $this->make_token( self::T_OP, $char2 );
        }

        $char1 = $this->input[ $this->position ];
        if ( in_array( $char1, ['+', '-', '*', '/', '=', '>', '<'] ) ) {
            $this->position++;
            return $this->make_token( self::T_OP, $char1 );
        }

        return null;
    }
}
