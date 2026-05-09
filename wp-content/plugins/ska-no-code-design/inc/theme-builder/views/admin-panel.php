<div class="wrap ska-theme-builder-panel bg-slate-50 min-h-screen -ml-5 -mt-2 p-8" x-data="themeBuilderData()">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 m-0 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-indigo-500">web</span>
                    Theme Builder
                </h1>
                <p class="text-slate-500 mt-2">Thiết kế các khối giao diện nền tảng cho Website & Web App (Header, Footer, Single, Archive).</p>
            </div>
            <div>
                <button @click="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all flex items-center gap-2 border-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Tạo Template Mới
                </button>
            </div>
        </div>

        <!-- Navigation / Tabs -->
        <div class="flex space-x-1 bg-white p-1.5 rounded-2xl border border-slate-200/60 shadow-sm mb-6 w-max">
            <template x-for="tab in tabs" :key="tab.id">
                <button 
                    @click="activeTab = tab.id"
                    :class="activeTab === tab.id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                    class="px-5 py-2.5 rounded-xl text-sm transition-all border-0 cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" x-text="tab.icon"></span>
                    <span x-text="tab.name"></span>
                </button>
            </template>
        </div>

        <!-- Template List (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="template in filteredTemplates" :key="template.id">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md"
                                      :class="getTabColorClass(template.location)">
                                    <span x-text="getTabName(template.location)"></span>
                                </span>
                                <span x-show="template.is_active" class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-md bg-emerald-100 text-emerald-700">Active</span>
                                <span x-show="!template.is_active" class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-md bg-slate-100 text-slate-500">Draft</span>
                            </div>
                            
                            <!-- Context Menu -->
                            <div class="relative" x-data="{ openMenu: false }">
                                <button @click="openMenu = !openMenu" @click.outside="openMenu = false" class="text-slate-400 hover:text-slate-700 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-slate-50">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="openMenu" x-transition.opacity class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-10 py-1">
                                    <button @click="editTemplate(template.id)" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                        Sửa Settings
                                    </button>
                                    <button @click="deleteTemplate(template.id)" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Xóa Template
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-800 m-0 mb-2" x-text="template.title"></h3>
                        <p class="text-sm text-slate-500 m-0" x-text="'Organism ID: ' + (template.organism_id || 'Chưa chọn')"></p>
                        <p class="text-xs text-slate-400 m-0 mt-1 truncate" x-text="template.conditions ? 'Điều kiện: ' + template.conditions : 'Điều kiện: Mặc định (Toàn trang)'"></p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                            Cập nhật: <span x-text="template.updated_at"></span>
                        </span>
                        <a :href="getEditorUrl(template.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1 no-underline">
                            <span class="material-symbols-outlined text-[18px]">design_services</span>
                            Mở Editor
                        </a>
                    </div>
                </div>
            </template>
            
            <!-- Empty State -->
            <div x-show="filteredTemplates.length === 0" class="col-span-full py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">inventory_2</span>
                <p class="text-slate-500 font-medium">Chưa có template nào trong danh mục này.</p>
                <button @click="openCreateModal()" class="mt-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-lg shadow-sm transition-all border-0 cursor-pointer">
                    Tạo template đầu tiên
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 m-0" x-text="modalMode === 'create' ? 'Tạo Template Mới' : 'Sửa Template'"></h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Tên Template -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tên Template</label>
                        <input type="text" x-model="currentTemplate.title" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all" placeholder="Ví dụ: Header Mặc Định">
                    </div>
                    
                    <!-- Vị trí (Location) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Vị trí (Location)</label>
                        <select x-model="currentTemplate.location" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                            <template x-for="tab in tabs.filter(t => t.id !== 'all')" :key="tab.id">
                                <option :value="tab.id" x-text="tab.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Component (Organism) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Thiết kế (Organism Component)</label>
                        <select x-model="currentTemplate.organism_id" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                            <option value="">-- Chọn Component --</option>
                            <template x-for="org in organisms" :key="org.id">
                                <option :value="org.id" x-text="org.name"></option>
                            </template>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Chọn component từ Design Workspace để gán vào vị trí này.</p>
                    </div>

                    <!-- Điều kiện (Conditions) Rule Builder -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 m-0">Điều kiện hiển thị (Display Conditions)</label>
                                <p class="text-[11px] text-slate-500 m-0 mt-0.5">Xác định nơi Template này được xuất hiện.</p>
                            </div>
                            <button @click.prevent="addRule()" class="text-xs bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-2 py-1.5 rounded-md font-bold border-0 cursor-pointer flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[14px]">add</span> Thêm Rule
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-for="(rule, index) in rules" :key="index">
                                <div class="flex gap-2 items-start bg-white p-2.5 rounded-lg border border-slate-200 shadow-sm">
                                    <!-- Type (Include/Exclude) -->
                                    <select x-model="rule.type" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm font-medium focus:border-indigo-500 outline-none w-28 bg-white" :class="rule.type === 'include' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-rose-700 bg-rose-50 border-rose-200'">
                                        <option value="include">Include</option>
                                        <option value="exclude">Exclude</option>
                                    </select>
                                    
                                    <!-- Rule (Condition) -->
                                    <select x-model="rule.rule" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:border-indigo-500 outline-none flex-1 bg-white">
                                        <template x-for="opt in ruleOptions" :key="opt.value">
                                            <option :value="opt.value" x-text="opt.label"></option>
                                        </template>
                                    </select>

                                    <!-- Value (If needed) -->
                                    <template x-if="['post_type', 'specific_post'].includes(rule.rule)">
                                        <input type="text" x-model="rule.value" :placeholder="rule.rule === 'specific_post' ? 'ID (VD: 12)' : 'slug (VD: post, page)'" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:border-indigo-500 outline-none w-36">
                                    </template>
                                    
                                    <!-- Delete -->
                                    <button @click.prevent="removeRule(index)" class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 bg-transparent border-0 cursor-pointer p-1.5 rounded-md transition-colors flex-shrink-0">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                            </template>
                            
                            <div x-show="rules.length === 0" class="text-center py-4 text-sm text-slate-500 italic bg-white rounded-lg border border-slate-200 border-dashed">
                                Chưa có điều kiện nào. Template sẽ hiển thị theo Location mặc định.
                            </div>
                        </div>
                    </div>

                    <!-- Trạng thái Active -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="currentTemplate.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <span class="text-sm font-bold text-slate-700">Kích hoạt Template (Active)</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    <button @click="closeModal()" class="px-5 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                        Hủy
                    </button>
                    <button @click="saveTemplate()" class="px-5 py-2 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                        <span x-show="isLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                        <span x-text="modalMode === 'create' ? 'Tạo Mới' : 'Lưu Thay Đổi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('themeBuilderData', () => ({
        tabs: [
            { id: 'all', name: 'Tất cả', icon: 'grid_view' },
            { id: 'header', name: 'Header', icon: 'vertical_align_top' },
            { id: 'footer', name: 'Footer', icon: 'vertical_align_bottom' },
            { id: 'single', name: 'Single', icon: 'article' },
            { id: 'archive', name: 'Archive', icon: 'view_agenda' },
            { id: '404', name: '404 Page', icon: 'error' },
            { id: 'app_layout', name: 'App Layout', icon: 'web' },
            { id: 'custom', name: 'Custom Page', icon: 'layers' }
        ],
        activeTab: 'all',
        templates: [],
        
        isModalOpen: false,
        modalMode: 'create',
        isLoading: false,
        organisms: [], // Store organisms for selection
        currentTemplate: {
            id: null,
            title: '',
            location: 'header',
            organism_id: '',
            conditions: '',
            is_active: 1
        },
        rules: [],
        ruleOptions: [
            { value: 'all', label: 'Toàn bộ trang (Entire Site)' },
            { value: 'is_front_page', label: 'Trang chủ (Front Page)' },
            { value: 'is_archive', label: 'Tất cả Lưu trữ (All Archives)' },
            { value: 'is_single', label: 'Tất cả Bài viết/Trang (Singular)' },
            { value: 'post_type', label: 'Theo Loại bài viết (Post Type)' },
            { value: 'specific_post', label: 'Bài cụ thể (Post/Page ID)' },
            { value: 'is_404', label: 'Trang lỗi (404)' },
            { value: 'is_search', label: 'Kết quả tìm kiếm (Search)' }
        ],

        apiUrl: '<?php echo esc_url( rest_url( 'ska-builder/v1/theme-templates' ) ); ?>',
        apiOrganismsUrl: '<?php echo esc_url( rest_url( 'ska-design/v1/organisms' ) ); ?>',
        apiNonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',

        init() {
            this.loadTemplates();
            this.loadOrganisms();
        },

        get filteredTemplates() {
            if (this.activeTab === 'all') return this.templates;
            return this.templates.filter(t => t.location === this.activeTab);
        },

        getTabName(locationId) {
            const tab = this.tabs.find(t => t.id === locationId);
            return tab ? tab.name : locationId;
        },

        getTabColorClass(locationId) {
            const colors = {
                'header': 'bg-blue-100 text-blue-700',
                'footer': 'bg-purple-100 text-purple-700',
                'single': 'bg-emerald-100 text-emerald-700',
                'archive': 'bg-amber-100 text-amber-700',
                '404': 'bg-rose-100 text-rose-700',
                'app_layout': 'bg-indigo-100 text-indigo-700',
                'custom': 'bg-cyan-100 text-cyan-700'
            };
            return colors[locationId] || 'bg-slate-100 text-slate-700';
        },

        getEditorUrl(templateId) {
            return `<?php echo admin_url('admin.php?page=ska-theme-builder-editor&template_id='); ?>${templateId}`;
        },

        async loadTemplates() {
            try {
                const response = await fetch(this.apiUrl, {
                    headers: { 'X-WP-Nonce': this.apiNonce }
                });
                if (response.ok) {
                    const result = await response.json();
                    if(result.success) {
                        this.templates = result.data;
                    }
                } else {
                    console.error('Failed to load templates');
                }
            } catch (error) {
                console.error(error);
            }
        },

        async loadOrganisms() {
            try {
                const response = await fetch(this.apiOrganismsUrl, {
                    headers: { 'X-WP-Nonce': this.apiNonce }
                });
                if (response.ok) {
                    const result = await response.json();
                    if(result.success) {
                        this.organisms = result.data.data ? result.data.data : result.data;
                    }
                } else {
                    console.error('Failed to load organisms');
                }
            } catch (error) {
                console.error(error);
            }
        },

        openCreateModal() {
            this.modalMode = 'create';
            this.currentTemplate = {
                id: null,
                title: '',
                location: this.activeTab === 'all' ? 'header' : this.activeTab,
                organism_id: '',
                conditions: '',
                is_active: 1
            };
            this.rules = [{ type: 'include', rule: 'all', value: '' }];
            this.isModalOpen = true;
        },

        editTemplate(id) {
            const tmpl = this.templates.find(t => t.id === id);
            if (tmpl) {
                this.modalMode = 'edit';
                this.currentTemplate = { ...tmpl };
                
                try {
                    let parsed = JSON.parse(this.currentTemplate.conditions || '[]');
                    if (parsed && !Array.isArray(parsed)) {
                        let newRules = [];
                        if (parsed.include === 'all') newRules.push({ type: 'include', rule: 'all', value: '' });
                        parsed = newRules;
                    }
                    this.rules = Array.isArray(parsed) ? parsed : [];
                } catch(e) {
                    this.rules = [];
                }
                
                if (this.rules.length === 0) {
                    this.rules.push({ type: 'include', rule: 'all', value: '' });
                }

                this.isModalOpen = true;
            }
        },

        async saveTemplate() {
            if (!this.currentTemplate.title.trim()) {
                alert('Vui lòng nhập tên template.');
                return;
            }

            this.currentTemplate.conditions = JSON.stringify(this.rules);

            this.isLoading = true;
            
            try {
                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.apiNonce
                    },
                    body: JSON.stringify(this.currentTemplate)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    await this.loadTemplates();
                    this.closeModal();
                } else {
                    alert(result.message || 'Có lỗi xảy ra khi lưu template.');
                }
            } catch (error) {
                console.error(error);
                alert('Lỗi kết nối.');
            } finally {
                this.isLoading = false;
            }
        },

        async deleteTemplate(id) {
            if (confirm('Bạn có chắc chắn muốn xóa template này? Hành động này không thể hoàn tác.')) {
                try {
                    const response = await fetch(`${this.apiUrl}/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': this.apiNonce }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        this.templates = this.templates.filter(t => t.id !== id);
                    } else {
                        alert(result.message || 'Không thể xóa template.');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Lỗi kết nối.');
                }
            }
        },

        closeModal() {
            this.isModalOpen = false;
        },

        addRule() {
            this.rules.push({ type: 'include', rule: 'all', value: '' });
        },

        removeRule(index) {
            this.rules.splice(index, 1);
        }
    }));
});
</script>
