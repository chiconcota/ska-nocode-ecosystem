<?php
defined( 'ABSPATH' ) || exit;
?>
<!-- Tích hợp Tailwind CDN dùng tạm cho Dashboard UI để Design nhanh chóng -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#10b981', // Emerald 500
                    'primary-dark': '#047857', // Emerald 700
                    'dark-surface': '#1f2937', // Gray 800
                }
            }
        }
    }
</script>

<div class="wrap ska-data-dashboard p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Ska Data Pro ⚡</h1>
        <p class="text-gray-500 text-lg">Quản lý Dữ liệu Phẳng (Flat Tables) cực nhanh. Bắt đầu bằng cách chọn một bộ dữ liệu dự án mẫu.</p>
    </div>

    <!-- Template Gallery Section -->
    <h2 class="text-2xl font-semibold mb-6 text-gray-800 border-b pb-2">🖼️ Data Templates Gallery</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        <!-- Template: E-commerce -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col group p-6">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="dashicons dashicons-cart" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">E-Commerce App</h3>
            <p class="text-sm text-gray-500 flex-grow mb-4">Mô hình cửa hàng bán lẻ. Tích hợp sẵn schema Giỏ hàng, Danh sách Sản phẩm, Kho và Đơn Đặt Hàng.</p>
            <div class="bg-gray-50 rounded p-3 mb-5 border border-gray-100">
                <p class="text-xs text-gray-600 font-mono mb-1"><strong>Bảng tự tạo: </strong>2</p>
                <div class="flex flex-wrap gap-1 mt-2 -ml-1">
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">ska_data_products</span>
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">ska_data_orders</span>
                </div>
            </div>
            <button data-template-id="ecommerce" class="ska-install-btn w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                <span class="dashicons dashicons-download mt-0.5"></span> <span class="btn-text">Cài Đặt Mẫu Này</span>
            </button>
        </div>

        <!-- Template: LMS -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col group p-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="dashicons dashicons-welcome-learn-more" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">LMS & Academy</h3>
            <p class="text-sm text-gray-500 flex-grow mb-4">Hệ thống giáo dục trực tuyến. Gồm cấu trúc Khóa học, Bài giảng và Tiến độ Học viên.</p>
            <div class="bg-gray-50 rounded p-3 mb-5 border border-gray-100">
                <p class="text-xs text-gray-600 font-mono mb-1"><strong>Bảng tự tạo: </strong>3</p>
                <div class="flex flex-wrap gap-1 mt-2 -ml-1">
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">.._courses</span>
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">.._lessons</span>
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">.._students</span>
                </div>
            </div>
            <button data-template-id="lms" class="ska-install-btn w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                <span class="dashicons dashicons-download mt-0.5"></span> <span class="btn-text">Cài Đặt Mẫu Này</span>
            </button>
        </div>

        <!-- Template: Booking -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col group p-6">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="dashicons dashicons-calendar-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Booking Appointments</h3>
            <p class="text-sm text-gray-500 flex-grow mb-4">Mô hình đặt lịch dịch vụ (Spa, Y tế, Khách sạn). Gồm thông tin Dịch Vụ và Slot thời gian.</p>
            <div class="bg-gray-50 rounded p-3 mb-5 border border-gray-100">
                <p class="text-xs text-gray-600 font-mono mb-1"><strong>Bảng tự tạo: </strong>2</p>
                <div class="flex flex-wrap gap-1 mt-2 -ml-1">
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">.._services</span>
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">.._appointments</span>
                </div>
            </div>
            <button data-template-id="booking" class="ska-install-btn w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                <span class="dashicons dashicons-download mt-0.5"></span> <span class="btn-text">Cài Đặt Mẫu Này</span>
            </button>
        </div>

        <!-- Template: Bệnh Viện / Phòng Khám (Online Hospital) -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden flex flex-col group p-6">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="dashicons dashicons-heart" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Online Hospital</h3>
            <p class="text-sm text-gray-500 flex-grow mb-4">Hệ thống Y tế, Bệnh viện trực tuyến. Quản lý danh sách Bác sĩ, chuyên khoa, lịch khám.</p>
            <div class="bg-gray-50 rounded p-3 mb-5 border border-gray-100">
                <p class="text-xs text-gray-600 font-mono mb-1"><strong>Bảng tự tạo: </strong>1</p>
                <div class="flex flex-wrap gap-1 mt-2 -ml-1">
                    <span class="inline-block bg-white text-xs text-gray-600 px-2 py-1 rounded border">ska_data_doctors</span>
                </div>
            </div>
            <button data-template-id="hospital" class="ska-install-btn w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                <span class="dashicons dashicons-download mt-0.5"></span> <span class="btn-text">Cài Đặt Mẫu Này</span>
            </button>
        </div>

        <!-- Template: Custom Empty Table -->
        <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 shadow-sm hover:shadow-md hover:border-emerald-400 hover:bg-emerald-50 transition-all duration-300 overflow-hidden flex flex-col group p-6 items-center text-center cursor-pointer">
            <div class="w-16 h-16 bg-white border border-gray-100 text-gray-400 rounded-full flex items-center justify-center mb-4 group-hover:text-emerald-500 group-hover:scale-110 transition-transform shadow-sm">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 32px; width: 32px; height: 32px; margin-top: 4px;"></span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Tạo Bảng Trống</h3>
            <p class="text-sm text-gray-500 flex-grow mb-4 mt-1">Xây dựng Schema rỗng từ con số 0. Tùy ý thêm cột dữ liệu và định nghĩa quan hệ.</p>
            <button data-template-id="custom" class="ska-install-btn w-full bg-white border border-gray-300 group-hover:border-emerald-500 group-hover:text-emerald-600 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 shadow-sm mt-auto">
                <span class="btn-text">Bắt Đầu Ngay</span>
            </button>
        </div>

    </div>

    <!-- Features Bottom Area -->
    <div class="mt-12 bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-inner">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Tại sao dùng Flat Tables thay vì "Post & Meta" cũ?</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-emerald-500 font-bold">🚀 Siêu Tốc (Blazing Fast)</span>
                <p class="text-sm text-gray-600 mt-2">Dữ liệu được lưu trữ trên một Bảng phẳng duy nhất thay vì xé lẻ theo EAV Model của WordPress, giúp tốc độ Truy vấn (Query) tăng ít nhất x10 lần.</p>
            </div>
            <div>
                <span class="text-emerald-500 font-bold">⚡ Query Dễ Dàng (No-code)</span>
                <p class="text-sm text-gray-600 mt-2">Giúp Ska Builder dễ dàng nhúng dữ liệu ra Frontend qua trình tạo Vòng lặp giao diện đồ họa.</p>
            </div>
            <div>
                <span class="text-emerald-500 font-bold">🧹 Sạch Tinh Tươm (Clean)</span>
                <p class="text-sm text-gray-600 mt-2">Không bị lẫn lộn dữ liệu rác từ các plugin WordPress khác. Dữ liệu của ứng dụng bạn xây dựng hoàn toàn cách ly.</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Reset WP content box padding to make it full width */
#wpcontent { padding-left: 0; }
.wrap.ska-data-dashboard { max-width: 1400px; margin: 0 auto; margin-top: 20px;}

/* Loading Spinner CSS */
@keyframes spin { 100% { transform: rotate(360deg); } }
.ska-spinner {
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    width: 16px;
    height: 16px;
    animation: spin 1s ease-in-out infinite;
    display: inline-block;
}
.ska-spinner-dark {
    border-color: rgba(0,0,0,0.1);
    border-top-color: #10b981;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.ska-install-btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const templateId = this.getAttribute('data-template-id');
            if ( ! templateId ) return;
            
            // Lấy UI elements để làm hiệu ứng Loading
            const originalHtml = this.innerHTML;
            const textSpan = this.querySelector('.btn-text');
            const isCustom = templateId === 'custom';
            
            // Khóa nút chờ Server
            this.disabled = true;
            this.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Render cục Spinner
            const spinnerClass = isCustom ? 'ska-spinner ska-spinner-dark' : 'ska-spinner';
            this.innerHTML = `<span class="${spinnerClass}"></span> <span class="btn-text">Đang cài đặt...</span>`;
            
            // Gửi dữ liệu bằng form URL encoded giả lập jQuery.ajax
            const formData = new URLSearchParams();
            formData.append('action', 'ska_install_data_template');
            formData.append('security', '<?php echo wp_create_nonce("ska_data_nonce"); ?>');
            formData.append('template_id', templateId);
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    this.innerHTML = `<span class="dashicons dashicons-yes"></span> <span class="btn-text">Hoàn tất!</span>`;
                    this.classList.replace('bg-primary', 'bg-green-600');
                    
                    // Thực thi vòng lặp Redirect như yêu cầu của người dùng
                    setTimeout(() => {
                        window.location.href = res.data.redirect_url;
                    }, 500);
                } else {
                    alert('Lỗi: ' + (res.data.message || 'Hệ thống bận'));
                    // Trả lại nút nếu lỗi
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                    this.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            })
            .catch(error => {
                console.error(error);
                alert('Có lỗi mạng xảy ra khi gửi request');
                this.innerHTML = originalHtml;
                this.disabled = false;
                this.classList.remove('opacity-75', 'cursor-not-allowed');
            });
        });
    });
});
</script>
