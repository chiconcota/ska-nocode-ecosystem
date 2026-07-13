<?php
/**
 * Lớp Quản lý Đăng ký Node (Skaaa_Node_Registry)
 * 
 * Quản lý tập trung các trạm logic (Nodes) trong hệ sinh thái.
 * Cho phép bên thứ ba tự đăng ký các Node tùy biến thông qua WP Filter.
 * 
 * @package Skaaa_Logic_Engine
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class Skaaa_Node_Registry {

    /**
     * Thể hiện duy nhất của class (Singleton)
     * 
     * @var Skaaa_Node_Registry|null
     */
    private static $instance = null;

    /**
     * Danh sách các nodes đã đăng ký
     * 
     * @var array
     */
    private $nodes = [];

    /**
     * Lấy thể hiện duy nhất của class (Singleton)
     * 
     * @return Skaaa_Node_Registry
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hàm khởi tạo
     */
    private function __construct() {
        // Tự động đăng ký các default nodes khi khởi tạo
        $this->register_defaults();
    }

    /**
     * Đăng ký một Node mới vào hệ thống
     * 
     * @param string $type Định danh duy nhất cho loại node (vd: 'TriggerNode')
     * @param array  $args Tham số cấu hình chi tiết của node
     * @return bool Trả về true nếu đăng ký thành công, ngược lại là false
     */
    public function register_node( $type, $args ) {
        $defaults = [
            'type'            => $type,
            'class'           => '',
            'label'           => '',
            'icon'            => '', // Tên icon Lucide hoặc class CSS
            'description'     => '',
            'color'           => '', // CSS classes
            'category'        => 'logic', // 'trigger', 'logic', 'data', 'presentation'
            'settings_schema' => [], // JSON Schema cho settings UI vẽ động
            'disabled'        => false,
        ];

        $node_data = wp_parse_args( $args, $defaults );

        // Kiểm tra tính hợp lệ tối thiểu
        if ( empty( $node_data['type'] ) || empty( $node_data['class'] ) ) {
            return false;
        }

        $this->nodes[ $type ] = $node_data;
        return true;
    }

    /**
     * Hủy đăng ký một Node khỏi hệ thống
     * 
     * @param string $type Định danh duy nhất của node
     * @return bool
     */
    public function unregister_node( $type ) {
        if ( isset( $this->nodes[ $type ] ) ) {
            unset( $this->nodes[ $type ] );
            return true;
        }
        return false;
    }

    /**
     * Lấy toàn bộ các node đã đăng ký (Sau khi lọc qua filter của bên thứ 3)
     * 
     * @return array
     */
    public function get_all_nodes() {
        /**
         * Filter cho phép bên thứ ba đăng ký thêm custom nodes hoặc chỉnh sửa nodes hiện có.
         * 
         * @param array $nodes Danh sách các nodes hiện tại
         */
        $filtered_nodes = apply_filters( 'skaaa_logic_registered_nodes', $this->nodes );

        // Đọc danh sách các node bị vô hiệu hóa từ bảng phẳng settings hệ thống
        if ( function_exists( 'skaaa_get_system_setting' ) ) {
            $disabled_nodes = skaaa_get_system_setting( 'skaaa_disabled_nodes', [] );
            if ( is_array( $disabled_nodes ) ) {
                foreach ( $filtered_nodes as $type => &$node ) {
                    if ( in_array( $type, $disabled_nodes, true ) ) {
                        $node['disabled'] = true;
                    }
                }
            }
        }
        
        return array_values( $filtered_nodes );
    }

    /**
     * Đăng ký các primitive nodes mặc định của hệ thống
     */
    private function register_defaults() {
        // Trạm 1: Trigger Node
        $this->register_node( 'TriggerNode', [
            'class'       => 'Skaaa_Logic_Trigger_Node',
            'label'       => __( 'Trigger Node', 'skaaa-logic-engine' ),
            'icon'        => 'Zap',
            'description' => __( 'Start workflow on event', 'skaaa-logic-engine' ),
            'color'       => 'bg-red-50 text-red-700 border-red-200',
            'category'    => 'trigger',
        ] );

        // Trạm 2: Set Data
        $this->register_node( 'SetDataNode', [
            'class'       => 'Skaaa_Logic_Set_Data',
            'label'       => __( 'Set Data', 'skaaa-logic-engine' ),
            'icon'        => 'Variable',
            'description' => __( 'Assign variables', 'skaaa-logic-engine' ),
            'color'       => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'category'    => 'data',
        ] );

        // Trạm 3: DB CRUD Action
        $this->register_node( 'DBActionNode', [
            'class'       => 'Skaaa_Logic_DB_Action',
            'label'       => __( 'DB CRUD Action', 'skaaa-logic-engine' ),
            'icon'        => 'Database',
            'description' => __( 'Read/Write to DB', 'skaaa-logic-engine' ),
            'color'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'category'    => 'data',
        ] );

        // Trạm 4: DB Query
        $this->register_node( 'DBQueryNode', [
            'class'       => 'Skaaa_Logic_DB_Query',
            'label'       => __( 'DB Query', 'skaaa-logic-engine' ),
            'icon'        => 'Search',
            'description' => __( 'Fetch from DB', 'skaaa-logic-engine' ),
            'color'       => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'category'    => 'data',
        ] );

        // Trạm 5: If/Else Condition
        $this->register_node( 'ConditionNode', [
            'class'       => 'Skaaa_Condition_Node',
            'label'       => __( 'If/Else', 'skaaa-logic-engine' ),
            'icon'        => 'GitBranch',
            'description' => __( 'Branching logic', 'skaaa-logic-engine' ),
            'color'       => 'bg-amber-50 text-amber-700 border-amber-200',
            'category'    => 'logic',
        ] );

        // Trạm 6: Switch Router
        $this->register_node( 'SwitchNode', [
            'class'       => 'Skaaa_Logic_Switch',
            'label'       => __( 'Switch Router', 'skaaa-logic-engine' ),
            'icon'        => 'GitMerge',
            'description' => __( 'Multi-branch routing', 'skaaa-logic-engine' ),
            'color'       => 'bg-purple-50 text-purple-700 border-purple-200',
            'category'    => 'logic',
        ] );

        // Trạm 7: Iterator / Loop
        $this->register_node( 'IteratorNode', [
            'class'       => 'Skaaa_Logic_Iterator',
            'label'       => __( 'Iterator / Loop', 'skaaa-logic-engine' ),
            'icon'        => 'Repeat',
            'description' => __( 'Loop over items', 'skaaa-logic-engine' ),
            'color'       => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            'category'    => 'logic',
        ] );

        // Trạm 8: HTTP Request
        $this->register_node( 'ApiNode', [
            'class'       => 'Skaaa_Logic_Http_Request',
            'label'       => __( 'HTTP Request', 'skaaa-logic-engine' ),
            'icon'        => 'Globe',
            'description' => __( 'Call external API', 'skaaa-logic-engine' ),
            'color'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'category'    => 'data',
        ] );

        // Trạm 9: Client Response
        $this->register_node( 'ClientResponseNode', [
            'class'       => 'Skaaa_Logic_Client_Response',
            'label'       => __( 'Client Response', 'skaaa-logic-engine' ),
            'icon'        => 'reply',
            'description' => __( 'Send UI commands (Toast/Modal)', 'skaaa-logic-engine' ),
            'color'       => 'bg-teal-50 text-teal-700 border-teal-200',
            'category'    => 'presentation',
        ] );

        // Trạm 10: Render Template
        $this->register_node( 'RenderTemplateNode', [
            'class'       => 'Skaaa_Logic_Render_Template',
            'label'       => __( 'Render Template', 'skaaa-logic-engine' ),
            'icon'        => 'FileCode2',
            'description' => __( 'Interpolate data into HTML template', 'skaaa-logic-engine' ),
            'color'       => 'bg-sky-50 text-sky-700 border-sky-200',
            'category'    => 'presentation',
        ] );
    }
}
