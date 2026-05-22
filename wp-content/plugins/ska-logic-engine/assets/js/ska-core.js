window.$ska = {
    /**
     * Submit Form Helper (Decoupled No-Code Entry)
     * Kích hoạt chu trình xử lý: Alpine Data -> WP REST API -> Ska Logic Engine -> Ska Event Bus
     * 
     * @param {HTMLElement} el Thẻ form (this/$el)
     * @param {Object} payload Dữ liệu người dùng truyền từ giao diện
     */
    submitForm: async (el, payload = {}) => {
        window.$ska.lastClickedElement = el;
        // 1. Nhận diện ID Workflow
        const logicId = el.getAttribute('data-ska-action') || el.dataset.logicId || 'default_form_submit';
        
        // 2. Chuẩn bị Dữ Liệu
        const requestData = {
            ska_form_id: logicId,
            ...payload
        };

        // 3. UI State: Hỗ trợ tự động vô hiệu hóa nút bấm
        const submitBtn = el.querySelector('button[type="submit"], input[type="submit"]') || el.querySelector('a.wp-block-button__link') || el;
        let originalText = '';
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span style="display:inline-block; animation: ska-spin 1s linear infinite; margin-right: 8px;">⏳</span> Đang xử lý...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.style.pointerEvents = 'none';
            
            // Add spinner animation CSS if missing
            if (!document.getElementById('ska-spin-css')) {
                const style = document.createElement('style');
                style.id = 'ska-spin-css';
                style.innerHTML = '@keyframes ska-spin { 100% { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }
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
                    window.$ska.processEventBus(result.data._ska_events, el);
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
            window.$ska.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            // Khôi phục nút bấm để bấm lại
            if (submitBtn) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
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
    processEventBus: (events, initiatorEl = null) => {
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
            else if (evt.type === 'remove_row') {
                if (evt.message) {
                    window.$ska.showToast(evt.message, evt.toast_type || 'success');
                }
                const targetEl = initiatorEl || window.$ska.lastClickedElement;
                if (targetEl) {
                    let rowEl = null;

                    // 1. Nếu nằm trong Loop, phần tử lặp lại (hàng cần xóa) luôn là con trực tiếp của Loop container
                    const loopContainer = targetEl.closest('.wp-block-ska-builder-loop, .ska-loop-wrapper');
                    if (loopContainer) {
                        let current = targetEl;
                        while (current && current.parentElement && current.parentElement !== loopContainer) {
                            current = current.parentElement;
                        }
                        if (current && current !== loopContainer) {
                            rowEl = current;
                        }
                    }

                    // 2. Nếu không nằm trong Loop hoặc không tìm thấy, dùng selector truyền thống
                    if (!rowEl) {
                        rowEl = targetEl.closest('tr') || 
                                targetEl.closest('.ska-organism-row') || 
                                targetEl.closest('.ska-loop-item') || 
                                targetEl.closest('.loop-item');
                    }

                    // 3. Fallback cuối cùng: Tránh việc closest() trả về chính targetEl nếu targetEl có class container
                    if (!rowEl || rowEl === targetEl) {
                        const parent = targetEl.parentElement;
                        if (parent) {
                            rowEl = parent.closest('[data-id]') ||
                                    parent.closest('.ska-container-block') ||
                                    parent.closest('.wp-block-ska-builder-container') ||
                                    parent;
                        }
                    }

                    if (rowEl) {
                        rowEl.style.transition = 'all 0.4s ease';
                        rowEl.style.opacity = '0';
                        rowEl.style.transform = 'scale(0.95)';
                        rowEl.style.maxHeight = rowEl.offsetHeight + 'px';
                        setTimeout(() => {
                            rowEl.style.height = '0';
                            rowEl.style.paddingTop = '0';
                            rowEl.style.paddingBottom = '0';
                            rowEl.style.marginTop = '0';
                            rowEl.style.marginBottom = '0';
                            rowEl.style.overflow = 'hidden';
                            setTimeout(() => {
                                rowEl.remove();
                            }, 400);
                        }, 100);
                    }
                }
            }
            else if (evt.type === 'toast' && evt.message) {
                window.$ska.showToast(evt.message, evt.toast_type || 'success');
            }
        });
    },

    /**
     * Hiển thị thông báo Toast
     */
    showToast: (message, type = 'success') => {
        console.log(`[Ska UI] ShowToast: "${message}" (${type})`);
        const toast = document.createElement('div');
        toast.className = `ska-toast ska-toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-family: sans-serif;
            font-size: 14px;
            font-weight: 600;
            z-index: 99999999;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            display: flex;
            align-items: center;
            gap: 8px;
        `;
        
        // Add icon
        const icon = type === 'success' ? '✅' : '❌';
        toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);

        // Animate out
        setTimeout(() => {
            toast.style.transform = 'translateY(-100px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }
};

/**
 * Global Action Click Listener
 * Hỗ trợ kích hoạt API Workflow từ MỌI nút bấm (không cần bọc trong Form)
 * Bằng cách thêm class CSS: ska-action-[workflow_id]
 */
document.addEventListener('click', function(e) {
    // Tìm element có class bắt đầu bằng ska-action-
    const actionEl = e.target.closest('[class*="ska-action-"]');
    if (!actionEl) return;
    
    // Nếu nó nằm trong Form đã được binding bằng Alpine (skaForm) thì bỏ qua để Form tự xử lý
    if (actionEl.closest('form[x-data*="skaForm"]')) return;

    // Lấy confirm message trước khi chạy
    const confirmMsg = actionEl.getAttribute('data-ska-confirm');
    if (confirmMsg && !window.confirm(confirmMsg)) {
        e.preventDefault();
        return;
    }

    e.preventDefault();

    // Trích xuất ID workflow từ class. Ví dụ: ska-action-api_test -> api_test
    const match = actionEl.className.match(/ska-action-([a-zA-Z0-9_-]+)/);
    if (!match) return;

    const workflowId = match[1];
    
    // Gắn ID tạm để submitForm nhận diện
    actionEl.dataset.logicId = workflowId;
    
    // Lấy payload tĩnh nếu có
    let payload = {};
    const payloadStr = actionEl.getAttribute('data-ska-payload');
    if (payloadStr) {
        try {
            payload = JSON.parse(payloadStr);
        } catch(err) {
            console.warn('Ska Core: Invalid JSON in data-ska-payload', err);
        }
    }
    
    // Kích hoạt chu trình gọi Backend
    window.$ska.submitForm(actionEl, payload);
});
