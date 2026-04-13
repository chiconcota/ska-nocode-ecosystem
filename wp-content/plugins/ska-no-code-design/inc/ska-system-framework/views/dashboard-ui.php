<?php
/**
 * View: Dashboard UI
 */
defined( 'ABSPATH' ) || exit;
?>

<!-- Tailwind CSS & Icons (Dành cho Dashboard nội bộ) -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />

<style>
    /* Resetting some default WP styles inside our wrapper to avoid conflicts */
    .ska-framework-wrap {
        background-color: #f0f0f1;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .ska-framework-wrap .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .ska-framework-wrap .toggle-checkbox:checked { right: 0; border-color: #4f46e5; }
    .ska-framework-wrap .toggle-checkbox:checked+.toggle-label { background-color: #4f46e5; }
    .ska-framework-wrap .modal-overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .ska-framework-wrap .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .ska-framework-wrap .modal-content { transform: scale(0.95) translateY(10px); opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .ska-framework-wrap .modal-overlay.active .modal-content { transform: scale(1) translateY(0); opacity: 1; }
    .ska-framework-wrap .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .ska-framework-wrap .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .ska-framework-wrap .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    /* WordPress admin notice fix */
    #wpcontent { padding-left: 0; }
</style>

<div class="wrap ska-framework-wrap p-4 md:p-8 text-slate-800" style="margin:0;">
    <div class="max-w-6xl mx-auto space-y-8">

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-sm">
                    <span class="material-symbols-outlined text-[28px]">hub</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 leading-tight">Hệ Sinh Thái Ska</h1>
                    <p class="text-sm text-slate-500" style="margin-top: 4px;">Quản lý các module (Plugins) và cài đặt tổng thể cho ứng dụng của
                        bạn.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <!-- Các nút Tương Tác Top (Import Blueprint/AI) tạm thời bị dời đi theo Roadmap Milestone 4 -->
            </div>
        </header>

        <!-- Grid 2 Cột: Modules (Trái) & Settings (Phải) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- CỘT TRÁI: QUẢN LÝ MODULES VÀ THEME -->
            <div class="lg:col-span-2 space-y-6">

                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2 m-0 border-0 pb-0">
                        <span class="material-symbols-outlined text-slate-500">extension</span>
                        Các Module Đã Cài Đặt
                    </h2>
                    <span class="text-xs font-medium bg-slate-200 text-slate-600 px-2.5 py-1 rounded-full">Tính năng động</span>
                </div>

                <?php 
                /**
                 * Hook để các plugin khác (Data Pro, Logic Engine, Design Engine) inject card module của họ vào đây.
                 */
                do_action( 'ska_system_dashboard_modules' ); 
                ?>

            </div>

            <!-- CỘT PHẢI: GLOBAL SETTINGS & DANGER ZONE -->
            <div class="space-y-6">

                <!-- Global Cài Đặt (Global Settings) -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200">
                        <h3 class="m-0 font-bold text-slate-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-500 text-[20px]">settings_applications</span>
                            Cài Đặt Hệ Thống
                        </h3>
                    </div>
                    <form id="skaSystemSettingsForm" method="post" action="" class="p-5 space-y-5" style="margin:0;">
                        <?php if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' ) : ?>
                            <div class="bg-emerald-50 text-emerald-700 p-3 rounded-lg text-sm border border-emerald-200 flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Đã cập nhật cài đặt hệ thống.
                            </div>
                        <?php endif; ?>

                        <?php 
                        /**
                         * Hook để các plugin tự đăng ký setting vào đây (Dev Mode, Tailwinc parity...)
                         */
                        do_action( 'ska_system_dashboard_settings' ); 
                        ?>
                        
                        <!-- Nút lưu cài đặt (Dành cho ajax forms) -->
                        <div class="pt-5 border-t border-slate-100">
                            <button type="submit"
                                class="w-full py-2 border-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors cursor-pointer">
                                Lưu Cài Đặt
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-rose-50 rounded-xl border border-rose-200 shadow-sm p-5">
                    <h3 class="m-0 border-0 pb-0 pt-0 font-bold text-rose-800 flex items-center gap-2 mb-2 text-sm">
                        <span class="material-symbols-outlined text-[18px]">warning</span>
                        Khu vực nguy hiểm
                    </h3>
                    <p class="text-xs text-rose-600 mb-4 mt-0">Các thao tác dưới đây ảnh hưởng trực tiếp đến Cấu trúc
                        Blueprint và hiệu năng của hệ thống (RAM/Cache).</p>
                    <button
                        class="w-full mb-2 py-2 bg-white border border-rose-300 text-rose-700 hover:bg-rose-600 hover:text-white rounded-lg text-sm font-medium transition-colors cursor-pointer">
                        Xóa Cache Cấu trúc (Clear Context)
                    </button>
                    <button
                        class="w-full py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                        Xóa Cache & Quét lại CSS (Flush JIT)
                    </button>
                </div>

            </div>

        </div>

        <!-- KHU VỰC TIỆN ÍCH MỞ RỘNG (EXTENSIONS) -->
        <div class="pt-6 pb-10">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-6">
                <h2 class="m-0 border-0 pb-0 pt-0 text-xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-500">grid_view</span>
                    Tiện Ích Mở Rộng (Extensions)
                </h2>
            </div>

            <!-- Grid Extension Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php 
                /**
                 * Hook để các tiện ích (API Connect, Fonts, Logic...) inject card điều hướng nhanh 
                 */
                do_action( 'ska_system_dashboard_extensions' ); 
                ?>
            </div>
            
            <?php if ( ! has_action( 'ska_system_dashboard_extensions' ) ) : ?>
                <div class="text-center py-8 text-slate-500 text-sm italic">
                    Chưa có tiện ích mở rộng nào được cài đặt.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Khu vực Modals dự phòng -->
</div>
