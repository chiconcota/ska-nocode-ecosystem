window.$ska = {
    /**
     * Submit Form Helper (Decoupled No-Code Entry)
     * Kích hoạt chu trình xử lý: Alpine Data -> WP REST API -> Ska Logic Engine -> Ska Event Bus
     * 
     * @param {HTMLElement} el Thẻ form (this/$el)
     * @param {Object} payload Dữ liệu người dùng truyền từ giao diện
     */
    submitForm: async (el, payload = {}) => {
        // 1. Nhận diện ID Workflow
        const logicId = el.getAttribute('data-ska-action') || el.dataset.logicId || 'default_form_submit';
        
        // 2. Chuẩn bị Dữ Liệu
        const requestData = {
            ska_form_id: logicId,
            ...payload
        };

        // 3. UI State: Hỗ trợ tự động vô hiệu hóa nút bấm
        const submitBtn = el.querySelector('button[type="submit"], input[type="submit"]') || el.querySelector('a.wp-block-button__link');
        let originalText = '';
        if (submitBtn) {
            originalText = submitBtn.textContent || submitBtn.value;
            submitBtn.textContent = 'Đang xử lý...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        }

        // 3.1. Giao tiếp với AlpineJS (Nếu có) để người dùng tự do hiển thị Loading bằng logic x-show
        if (typeof Alpine !== 'undefined' && Alpine.$data) {
            try { 
                const dataScope = Alpine.$data(el);
                if (dataScope.isSubmitting !== undefined) dataScope.isSubmitting = true;
                if (dataScope.errorMessage !== undefined) dataScope.errorMessage = '';
                if (dataScope.success !== undefined) dataScope.success = false;
            } catch (e) {
                console.warn('Ska Core: Lỗi khi đồng bộ UI State xuống Alpine', e);
            }
        }

        try {
            // 4. "Ném Cầu" vào Backend - TRÁNH TRỰC TIẾP PHỤ THUỘC CLASS (Decoupled Rule)
            const res = await fetch(skaAppEnv.restUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': skaAppEnv.nonce // Đảm bảo an toàn
                },
                body: JSON.stringify(requestData)
            });

            const result = await res.json();
            
            // 5. Kiểm tra kết quả
            if (result.success) {
                // Thành công: Cập nhật biến success trong Alpine để UI hiện thông báo
                if (typeof Alpine !== 'undefined' && Alpine.$data) {
                    try { 
                        const dataScope = Alpine.$data(el);
                        if (dataScope.success !== undefined) dataScope.success = true;
                    } catch (e) {}
                }
                
                // KÍCH HOẠT EVENT BUS - HỆ TUẦN HOÀN CHO PHÉP BACKEND RA LỆNH NGƯỢC LẠI FRONTEND
                if (result.data && result.data._ska_events) {
                    window.$ska.processEventBus(result.data._ska_events);
                }

                // Dọn dẹp Form (Reset Trắng)
                if (el.tagName === 'FORM') el.reset();

                // Ném một tín hiệu global để ai muốn bắt thì bắt (Vd: Analytics)
                el.dispatchEvent(new CustomEvent('ska-submit-success', { bubbles: true, detail: result }));
                
                return result;
            } else {
                throw new Error(result.message || 'Lỗi chưa xác định từ máy chủ!');
            }
        } catch (error) {
            // Lỗi: Cập nhật biến errorMessage trong Alpine để UI báo đỏ
            if (typeof Alpine !== 'undefined' && Alpine.$data) {
                try { 
                    const dataScope = Alpine.$data(el);
                    if (dataScope.errorMessage !== undefined) dataScope.errorMessage = error.message;
                } catch (e) {}
            }
            
            // Bắn tín hiệu lỗi
            el.dispatchEvent(new CustomEvent('ska-submit-error', { bubbles: true, detail: error }));
            
            // Hỗ trợ console để dev kiểm tra thêm thông tin
            console.error('Ska Core Form Submit Error:', error);
            alert('❌ Lỗi Logic Engine: ' + error.message);
        } finally {
            // Khôi phục nút bấm để bấm lại
            if (submitBtn) {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto';
            }
            
            // Tắt cờ Loading ở Alpine
            if (typeof Alpine !== 'undefined' && Alpine.$data) {
                try { 
                    const dataScope = Alpine.$data(el);
                    if (dataScope.isSubmitting !== undefined) dataScope.isSubmitting = false;
                } catch (e) {}
            }
        }
    },

    /**
     * Dàn Nhạc Trưởng (Event Bus)
     * Thực thi các phản hồi do Server đẩy xuống (Vd: Lệnh chuyển trang, Lệnh mở Modal Pop-up)
     * 
     * @param {Array} events Danh sách lệnh
     */
    processEventBus: (events) => {
        if (!Array.isArray(events)) return;
        
        events.forEach(evt => {
            console.log('Ska Event Bus triggered:', evt);
            
            if (evt.type === 'redirect' && evt.url) {
                window.location.href = evt.url;
            } 
            else if (evt.type === 'open_modal' && evt.modal_id) {
                // Giả định: Modal có ID và lắng nghe event "open-modal"
                const modal = document.getElementById(evt.modal_id);
                if (modal) {
                    modal.dispatchEvent(new CustomEvent('open-modal'));
                }
            } 
            else if (evt.type === 'fire_event' && evt.event_name) {
                window.dispatchEvent(new CustomEvent(evt.event_name, { detail: evt.payload || {} }));
            }
        });
    }
};
