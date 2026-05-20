/**
 * Ska Frontend Engine
 * Bộ não Alpine.js Controller cho Ska Form Builder.
 *
 * Tính năng:
 * 1. Data Binding (x-model tự inject từ render.php)
 * 2. Validation (blur-based, required check)
 * 3. Loading State (isSubmitting)
 * 4. Success/Error Handling (status, message)
 * 5. Honeypot (bot_trap field)
 * 6. Draft Save (Alpine $persist plugin)
 * 7. Reset Form (auto-reset sau submit thành công)
 * 8. Multi-step (step variable)
 * 9. Conditional Logic (dùng x-show trực tiếp trên block)
 * 10. Auto-Format (dùng x-mask plugin trên attribute)
 */
/**
 * Đăng ký skaForm Controller vào Alpine.
 * Hỗ trợ 2 kịch bản:
 * - Alpine đã load rồi (script defer) → Gọi trực tiếp Alpine.data()
 * - Alpine chưa load (edge-case) → Lắng nghe alpine:init
 */
function _registerSkaForm() {

    /**
     * skaForm Controller
     *
     * @param {string} actionId - ID Workflow trong Ska Logic Engine
     * @param {Object} options  - Tùy chọn { persist: false }
     */
    Alpine.data('skaForm', (actionId = 'default', options = {}) => ({

        // === TRẠNG THÁI (State) ===
        fields: {},
        errors: {},
        isSubmitting: false,
        status: '', // '', 'success', 'error'
        message: '',
        step: 1,
        bot_trap: '', // Honeypot

        // === GETTERS DÀNH CHO UI ===
        get success() {
            return this.status === 'success';
        },
        get errorMessage() {
            return this.status === 'error' ? this.message : '';
        },

        // === KHỞI TẠO ===
        init() {
            // Quét tất cả input bên trong form này, khởi tạo fields nếu chưa có
            this.$nextTick(() => {
                const inputs = this.$el.querySelectorAll('input[name], select[name], textarea[name]');
                inputs.forEach((input) => {
                    let name = input.getAttribute('name');
                    if (!name) return;

                    let isArray = false;
                    if (name.endsWith('[]')) {
                        name = name.slice(0, -2);
                        isArray = true;
                    }

                    if (typeof this.fields[name] === 'undefined') {
                        // NẾU CÓ currentData TRONG PORTAL CONTEXT, HÃY ƯU TIÊN LẤY TỪ currentData!
                        const portalStore = Alpine.store('skaPortal');
                        if (portalStore && portalStore.currentData && typeof portalStore.currentData[name] !== 'undefined') {
                            let val = portalStore.currentData[name];
                            if (Array.isArray(val)) {
                                if (val.length > 0 && typeof val[0] === 'object' && val[0] !== null && typeof val[0].id !== 'undefined') {
                                    let ids = val.map(item => item.id.toString());
                                    this.fields[name] = isArray ? ids : (ids[0] || '');
                                } else {
                                    this.fields[name] = val;
                                }
                            } else if (typeof val === 'object' && val !== null && typeof val.id !== 'undefined') {
                                this.fields[name] = isArray ? [val.id.toString()] : val.id.toString();
                            } else {
                                this.fields[name] = val;
                            }
                        } else {
                            // Khởi tạo giá trị mặc định dựa theo loại input
                            if (isArray) {
                                this.fields[name] = [];
                            } else if (input.type === 'checkbox') {
                                this.fields[name] = input.checked || false;
                            } else {
                                this.fields[name] = input.value || '';
                            }
                        }
                    }

                    // Nếu là mảng và input này được check/select mặc định, push vào mảng
                    if (isArray) {
                        if ((input.type === 'checkbox' || input.type === 'radio') && input.checked) {
                            if (!this.fields[name].includes(input.value)) {
                                this.fields[name].push(input.value);
                            }
                        } else if (input.type === 'select-multiple') {
                            Array.from(input.selectedOptions).forEach(opt => {
                                if (!this.fields[name].includes(opt.value)) {
                                    this.fields[name].push(opt.value);
                                }
                            });
                        }
                    }
                });
            });
        },

        // === VALIDATION ===
        validate(fieldName) {
            const input = this.$el.querySelector(`[name="${fieldName}"]`);
            if (!input) return true;

            // Reset lỗi cũ
            delete this.errors[fieldName];

            // Kiểm tra Required
            if (input.hasAttribute('required') && !this.fields[fieldName]?.toString().trim()) {
                this.errors[fieldName] = 'Trường này là bắt buộc.';
                return false;
            }

            // Kiểm tra Email format
            if (input.type === 'email' && this.fields[fieldName]) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.fields[fieldName])) {
                    this.errors[fieldName] = 'Email không hợp lệ.';
                    return false;
                }
            }

            // Kiểm tra Number min/max
            if (input.type === 'number' && this.fields[fieldName]) {
                const val = parseFloat(this.fields[fieldName]);
                if (input.min && val < parseFloat(input.min)) {
                    this.errors[fieldName] = `Giá trị tối thiểu là ${input.min}.`;
                    return false;
                }
                if (input.max && val > parseFloat(input.max)) {
                    this.errors[fieldName] = `Giá trị tối đa là ${input.max}.`;
                    return false;
                }
            }

            return true;
        },

        // Kiểm tra toàn bộ form trước khi submit
        validateAll() {
            let isValid = true;
            const inputs = this.$el.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach((input) => {
                const name = input.getAttribute('name');
                if (name && !this.validate(name)) {
                    isValid = false;
                }
            });
            return isValid;
        },

        // === GỬI FORM (Submit) ===
        async submitForm() {
            // 1. Chặn Bot (Honeypot check)
            if (this.bot_trap) {
                console.warn('[SkaForm] Bot detected via honeypot. Aborting.');
                return;
            }

            // 2. Validate toàn bộ
            if (!this.validateAll()) {
                this.status = 'error';
                this.message = 'Vui lòng kiểm tra lại các trường bắt buộc.';
                return;
            }

            // 3. Bật Loading State
            this.isSubmitting = true;
            this.status = '';
            this.message = '';

            // 4. Phát sự kiện (cho Alpine/UI bắt nếu cần)
            this.$el.dispatchEvent(new CustomEvent('ska-form-submitting', { bubbles: true }));

            try {
                // 5. Gửi Fetch POST tới Ska Logic Engine
                const payload = {
                    ...this.fields,
                    ska_form_id: actionId,
                };

                const restUrl = (window.skaAppEnv && window.skaAppEnv.restUrl) ? window.skaAppEnv.restUrl : '/wp-json/ska-logic/v1/submit';
                const response = await fetch(restUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.skaAppEnv ? window.skaAppEnv.nonce : ''
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (data.success) {
                    this.status = 'success';
                    this.message = data.message || 'Gửi thành công!';

                    // KÍCH HOẠT EVENT BUS - HỆ TUẦN HOÀN CHO PHÉP BACKEND RA LỆNH NGƯỢC LẠI FRONTEND
                    if (data.data && data.data._ska_events && window.$ska && window.$ska.processEventBus) {
                        window.$ska.processEventBus(data.data._ska_events);
                    }

                    // Reset Form
                    Object.keys(this.fields).forEach((key) => {
                        this.fields[key] = Array.isArray(this.fields[key]) ? [] : '';
                    });
                    this.errors = {};
                    this.step = 1;

                    // Phát sự kiện thành công
                    this.$el.dispatchEvent(new CustomEvent('ska-form-success', {
                        bubbles: true,
                        detail: data,
                    }));
                } else {
                    this.status = 'error';
                    this.message = data.message || 'Có lỗi xảy ra. Vui lòng thử lại.';

                    // Phát sự kiện lỗi
                    this.$el.dispatchEvent(new CustomEvent('ska-form-error', {
                        bubbles: true,
                        detail: data,
                    }));
                }
            } catch (err) {
                this.status = 'error';
                this.message = 'Lỗi kết nối mạng. Vui lòng thử lại.';
                console.error('[SkaForm] Network error:', err);

                this.$el.dispatchEvent(new CustomEvent('ska-form-error', {
                    bubbles: true,
                    detail: { error: err.message },
                }));
            } finally {
                // 6. Tắt Loading
                this.isSubmitting = false;
            }
        },

        // === MULTI-STEP ===
        nextStep() {
            this.step++;
        },
        prevStep() {
            if (this.step > 1) this.step--;
        },

        resetForm() {
            Object.keys(this.fields).forEach((key) => {
                this.fields[key] = Array.isArray(this.fields[key]) ? [] : '';
            });
            this.errors = {};
            this.status = '';
            this.message = '';
            this.step = 1;
        },
    }));
}

/**
 * Đăng ký skaTheme Store vào Alpine để quản lý Dark Mode.
 */
function _registerSkaTheme() {
    if (!window.Alpine) return;
    
    // Sử dụng $persist nếu có, nếu không fallback đọc từ localStorage
    let isDarkInit = false;
    if (typeof Alpine.$persist !== 'undefined') {
        isDarkInit = Alpine.$persist(false).as('ska_dark_mode');
    } else {
        try {
            const stored = localStorage.getItem('ska_dark_mode');
            if (stored !== null) {
                isDarkInit = JSON.parse(stored);
            }
        } catch(e) {}
    }

    Alpine.store('skaTheme', {
        isDark: isDarkInit,
        init() {
            this.applyTheme();
            
            // Theo dõi thay đổi từ storage ở tab khác nếu cần
            window.addEventListener('storage', (e) => {
                if (e.key === 'ska_dark_mode') {
                    try {
                        this.isDark = JSON.parse(e.newValue);
                        this.applyTheme();
                    } catch (err) {}
                }
            });
        },
        toggle() {
            this.isDark = !this.isDark;
            this.applyTheme();
            
            // Fallback save nếu $persist không tồn tại
            if (typeof Alpine.$persist === 'undefined') {
                localStorage.setItem('ska_dark_mode', JSON.stringify(this.isDark));
            }
        },
        applyTheme() {
            if (this.isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
}

/**
 * Đăng ký skaPortal Store vào Alpine để quản lý Dữ liệu trang hiện tại (Current Page Data).
 */
function _registerSkaPortal() {
    if (!window.Alpine) return;

    Alpine.store('skaPortal', {
        config: {},
        columns: {},
        currentData: null, // Dữ liệu của trang hiện tại (Mảng List hoặc Object Detail)
        isLoading: false,
        
        init() {
            if (window.SkaPortalContext) {
                this.config = window.SkaPortalContext.portal || {};
                this.columns = window.SkaPortalContext.columns || {};
                this.currentData = window.SkaPortalContext.currentData || null;
            }
        }
    });
}



/**
 * Đăng ký skaScratchpad Controller vào Alpine để quản lý Rich Text & Gutenberg Iframe
 */
function _registerSkaScratchpad() {
    Alpine.data('skaScratchpad', (fieldName) => ({
        isOpen: false,
        isLoading: false,
        iframeUrl: '',
        postId: null,
        
        init() {
            // Watch changes if needed
        },

        async openDesigner() {
            this.isOpen = true;
            this.isLoading = true;

            try {
                // Gọi API lấy Iframe URL
                const restUrl = (window.skaAppEnv && window.skaAppEnv.restUrl) ? window.skaAppEnv.restUrl.replace('ska-logic', 'ska-builder') : '/wp-json/ska-builder/v1';
                const currentHtml = this.$refs.editor.value || '';
                
                const response = await fetch(`${restUrl}/scratchpad/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.skaAppEnv ? window.skaAppEnv.nonce : ''
                    },
                    body: JSON.stringify({ content: currentHtml })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.postId = data.post_id;
                    this.iframeUrl = data.iframe_url;
                } else {
                    alert('Lỗi khởi tạo Trình thiết kế: ' + data.message);
                    this.isOpen = false;
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối khi khởi tạo Trình thiết kế.');
                this.isOpen = false;
            }
        },

        async closeDesigner() {
            this.isOpen = false;
            this.iframeUrl = '';
            
            if (!this.postId) return;

            // Xóa Scratchpad để dọn rác
            try {
                const restUrl = (window.skaAppEnv && window.skaAppEnv.restUrl) ? window.skaAppEnv.restUrl.replace('ska-logic', 'ska-builder') : '/wp-json/ska-builder/v1';
                await fetch(`${restUrl}/scratchpad/destroy`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.skaAppEnv ? window.skaAppEnv.nonce : ''
                    },
                    body: JSON.stringify({ post_id: this.postId })
                });
                this.postId = null;
            } catch (err) {
                console.error('Lỗi khi dọn dẹp Scratchpad', err);
            }
        }
    }));
}

// Script này load TRƯỚC Alpine.js (thứ tự enqueue trong render.php).
// Khi Alpine load sau → phát alpine:init → hàm này hứng → đăng ký skaForm & skaTheme.
// Fallback: Nếu Alpine đã load trước (edge-case HTML Attributes) → gọi trực tiếp.
if (window.Alpine) {
    _registerSkaForm();
    _registerSkaTheme();
    _registerSkaPortal();
    _registerSkaScratchpad();
} else {
    document.addEventListener('alpine:init', () => {
        _registerSkaForm();
        _registerSkaTheme();
        _registerSkaPortal();
        _registerSkaScratchpad();
    });
}
