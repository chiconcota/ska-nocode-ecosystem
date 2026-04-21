<?php
defined( 'ABSPATH' ) || exit;
?>
<script src="https://cdn.tailwindcss.com"></script>
<!-- Alpine Plugins -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
/* Đè Reset WP padding */
#wpcontent { padding-left: 0; padding-bottom: 0; }
.ska-token-wrap {
    min-height: calc(100vh - 32px);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}
</style>

<div class="ska-token-wrap bg-slate-50 relative flex text-slate-800" x-data="skaDesignTokensApp()" x-init="initApp()">
    
    <!-- Sidebar / Tabs Menu -->
    <div class="w-64 bg-white border-r border-slate-200 flex flex-col shrink-0 min-h-full shadow-sm z-10">
        <div class="h-16 flex items-center px-6 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">token</span>
                </div>
                <h1 class="font-bold text-slate-900 leading-tight m-0 border-0 p-0 text-base">Design Tokens</h1>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-1">
            <p class="text-xs font-bold tracking-wider text-slate-400 uppercase mb-3 ml-2 mt-2">Cấu hình Global</p>
            
            <template x-for="tab in tabs" :key="tab.id">
                <button 
                    @click="activeTab = tab.id"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors border-0 cursor-pointer"
                    :class="activeTab === tab.id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 bg-transparent'">
                    <span class="material-symbols-outlined text-[20px]" x-text="tab.icon"></span>
                    <span x-text="tab.name"></span>
                </button>
            </template>
        </div>

        <div class="p-4 border-t border-slate-200">
            <button @click="saveTokens" :disabled="isSaving" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium border-0 cursor-pointer disabled:opacity-50">
                <span class="material-symbols-outlined text-[18px]" x-text="isSaving ? 'sync' : 'save'" :class="isSaving ? 'animate-spin' : ''"></span>
                <span x-text="isSaving ? 'Đang lưu...' : 'Lưu Thay Đổi'"></span>
            </button>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto relative h-[calc(100vh-32px)]">
        
        <!-- Tab 1: Colors -->
        <div x-show="activeTab === 'colors'" style="display: none;" class="p-8 max-w-4xl mx-auto space-y-8 animate-[fade-in_0.3s_ease-out]">
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-2xl font-bold text-slate-900 m-0 border-0 p-0">Màu sắc (Colors)</h2>
                <p class="text-slate-500 mt-2">Bảng màu hệ thống định nghĩa Brand Identity, được áp dụng tự động qua Tailwind.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vòng lặp màu sắc cơ bản -->
                <template x-for="(val, key) in formData.colors" :key="key">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg border border-slate-200 shadow-inner flex-shrink-0 relative overflow-hidden" :style="'background-color: ' + val">
                            <input type="color" x-model="formData.colors[key]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 capitalize mb-1" x-text="key"></label>
                            <input type="text" x-model="formData.colors[key]" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none uppercase font-mono">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab 2: Typography -->
        <div x-show="activeTab === 'typography'" style="display: none;" class="p-8 max-w-4xl mx-auto space-y-8 animate-[fade-in_0.3s_ease-out]">
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-2xl font-bold text-slate-900 m-0 border-0 p-0">Kiểu chữ (Typography)</h2>
                <p class="text-slate-500 mt-2">Đinh nghĩa Font chữ chính (Primary) và Font phụ (Secondary) lấy từ Google Fonts.</p>
            </div>
            
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Primary Font</label>
                    <input type="text" x-model="formData.typography.primary" placeholder="e.g. 'Inter', sans-serif" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                    <p class="text-xs text-slate-500 mt-2">Dùng cho nội dung văn bản (Body Text).</p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Secondary Font (Headings)</label>
                    <input type="text" x-model="formData.typography.secondary" placeholder="e.g. 'Outfit', sans-serif" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                    <p class="text-xs text-slate-500 mt-2">Dùng cho các thẻ Tiêu đề H1, H2, H3...</p>
                </div>
            </div>
        </div>

        <!-- Tab 3: Spacing & Tokens -->
        <div x-show="activeTab === 'spacing'" style="display: none;" class="p-8 max-w-4xl mx-auto space-y-8 animate-[fade-in_0.3s_ease-out]">
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-2xl font-bold text-slate-900 m-0 border-0 p-0">Advanced Tokens</h2>
                <p class="text-slate-500 mt-2">Cấu hình Global Tokens cho Grid System cỡ lớn và UI Details.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Border Radius -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Global Border Radius</label>
                    <select x-model="formData.tokens.borderRadius" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                        <option value="0px">Vuông vức (0px)</option>
                        <option value="4px">Bo góc nhẹ (4px)</option>
                        <option value="8px">Bo góc vừa (8px)</option>
                        <option value="12px">Bo góc tròn mượt (12px)</option>
                        <option value="9999px">Bo cong hoàn toàn (Pill shape)</option>
                    </select>
                </div>

                <!-- Box Shadow -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Default Elevation (Shadow)</label>
                    <select x-model="formData.tokens.boxShadow" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                        <option value="none">Không đổ bóng (Flat)</option>
                        <option value="0 1px 2px 0 rgb(0 0 0 / 0.05)">Bóng mờ siêu nhẹ (sm)</option>
                        <option value="0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)">Bóng tiêu chuẩn (md)</option>
                        <option value="0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)">Đổ bóng sâu (lg)</option>
                        <option value="0 25px 50px -12px rgb(0 0 0 / 0.25)">Bóng khổng lồ (2xl)</option>
                    </select>
                </div>

                <!-- Container Width -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Container Max-Width</label>
                    <input type="text" x-model="formData.tokens.containerWidth" placeholder="e.g. 1280px or 80rem" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                    <p class="text-xs text-slate-500 mt-2">Chiều rộng tựa giới hạn tối đa cho Layout App.</p>
                </div>

                <!-- Transition Speed -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Base Transition Duration</label>
                    <select x-model="formData.tokens.transitionDuration" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:border-indigo-500 outline-none">
                        <option value="150ms">Tức thì / Snappy (150ms)</option>
                        <option value="300ms">Mượt mà / Smooth (300ms)</option>
                        <option value="500ms">Chậm rãi / Relaxed (500ms)</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-2">Tốc độ chuẩn dùng cho Hover, Animation.</p>
                </div>
            </div>
        </div>

        <!-- Tab 4: Components -->
        <div x-show="activeTab === 'components'" style="display: none;" class="p-8 max-w-4xl mx-auto space-y-8 animate-[fade-in_0.3s_ease-out]">
            <div class="border-b border-slate-200 pb-5 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 m-0 border-0 p-0">Atomic Components</h2>
                    <p class="text-slate-500 mt-2">Viết class Tailwind Utility để định hình presets cho các Components gốc.</p>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-bold text-slate-800 m-0 border-0 p-0">Buttons Presets</h3>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-700 w-1/4">Tên Preset</th>
                                <th class="px-4 py-3 text-sm font-semibold text-slate-700">Tailwind Classes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr>
                                <td class="px-4 py-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold uppercase">Primary</span></td>
                                <td class="px-4 py-4"><textarea x-model="formData.components.button.primary" rows="2" class="w-full border border-slate-300 rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none font-mono"></textarea></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-4"><span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold uppercase">Secondary</span></td>
                                <td class="px-4 py-4"><textarea x-model="formData.components.button.secondary" rows="2" class="w-full border border-slate-300 rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none font-mono"></textarea></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-4"><span class="border border-slate-300 text-slate-700 px-2 py-1 rounded text-xs font-bold uppercase">Outline</span></td>
                                <td class="px-4 py-4"><textarea x-model="formData.components.button.outline" rows="2" class="w-full border border-slate-300 rounded p-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none font-mono"></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notification Toast -->
        <div x-show="toast.show" x-transition.duration.300ms style="display: none;" class="fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50 text-white" :class="toast.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'">
            <span class="material-symbols-outlined text-[20px]" x-text="toast.type === 'error' ? 'error' : 'check_circle'"></span>
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>
    </div>
</div>

<script>
function skaDesignTokensApp() {
    return {
        activeTab: 'colors',
        isSaving: false,
        tabs: [
            { id: 'colors', name: 'Identity Colors', icon: 'palette' },
            { id: 'typography', name: 'Typography', icon: 'match_case' },
            { id: 'spacing', name: 'Advanced Tokens', icon: 'space_dashboard' },
            { id: 'components', name: 'UI Components', icon: 'interests' },
        ],
        formData: {
            colors: {
                primary: '#4f46e5',
                secondary: '#0f172a',
                accent: '#0ea5e9',
                background: '#f8fafc',
                text: '#1e293b'
            },
            typography: {
                primary: 'Inter, sans-serif',
                secondary: 'Outfit, sans-serif'
            },
            tokens: {
                borderRadius: '8px',
                boxShadow: 'none',
                containerWidth: '1280px',
                transitionDuration: '150ms'
            },
            components: {
                button: {
                    primary: 'bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition',
                    secondary: 'bg-slate-800 text-white hover:bg-slate-900 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-slate-800 transition',
                    outline: 'bg-transparent border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition'
                }
            }
        },
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        initApp() {
            this.fetchTokens();
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        async fetchTokens() {
            try {
                const response = await fetch('/wp-json/ska-design/v1/tokens', {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' }
                });
                const res = await response.json();
                
                if (res.success && Object.keys(res.data || {}).length > 0) {
                    // Cẩn thận merge object để tránh thiếu field khi JSON trên server rỗng 1 phần
                    this.formData = {
                         ...this.formData,
                         ...res.data,
                         colors: { ...this.formData.colors, ...(res.data.colors || {}) },
                         typography: { ...this.formData.typography, ...(res.data.typography || {}) },
                         tokens: { ...this.formData.tokens, ...(res.data.tokens || {}) },
                         components: { 
                             button: { ...this.formData.components.button, ...(res.data.components?.button || {}) } 
                         }
                    };
                }
            } catch (err) {
                console.error('Failed to fetch tokens', err);
            }
        },

        async saveTokens() {
            this.isSaving = true;
            try {
                const response = await fetch('/wp-json/ska-design/v1/tokens', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>' 
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const res = await response.json();
                
                if (res.success) {
                    this.showToast('Lưu Token thành công & Đã sinh File Cache!');
                } else {
                    this.showToast(res.message || 'Lỗi lưu dữ liệu', 'error');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Lỗi Network', 'error');
            } finally {
                this.isSaving = false;
            }
        }
    }
}
</script>
<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
