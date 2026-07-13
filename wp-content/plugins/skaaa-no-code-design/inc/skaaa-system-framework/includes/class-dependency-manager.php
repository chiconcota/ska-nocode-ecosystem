<?php
namespace Skaaa_System_Framework;

defined('ABSPATH') || exit;

/**
 * Circuit Breaker / Dependency Manager
 * Đảm bảo các plugin trong hệ sinh thái (Skaaa Data Pro, Skaaa Logic Engine) 
 * tương tác an toàn. Ngắt mạch (Graceful Fallback) nếu thiếu dependency.
 */
class Dependency_Manager
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Init if needed
    }

    /**
     * Kiểm tra xem Skaaa Data Pro có đang kích hoạt không
     * Dựa trên Namespace cốt lõi hoặc constant.
     */
    public static function is_data_pro_active()
    {
        // Chờ các plugin load xong mới check class_exists được
        // Ở đây Skaaa Data Pro dùng namespace Skaaa\Data
        return class_exists('\Skaaa\Data\Core\App_Manager') || defined('SKAAA_DATA_PRO_VERSION');
    }

    /**
     * Kiểm tra xem Skaaa Logic Engine có đang kích hoạt không
     */
    public static function is_logic_engine_active()
    {
        return class_exists('\Skaaa_Logic_Core') || defined('SKAAA_LOGIC_ENGINE_VERSION');
    }

    /**
     * Tính năng Theme Builder (Phase 4) yêu cầu toàn bộ hệ sinh thái
     */
    public static function is_theme_builder_supported()
    {
        return self::is_data_pro_active() && self::is_logic_engine_active();
    }
}
