<div class="wrap ska-theme-builder-panel bg-slate-50 min-h-screen -ml-5 -mt-2 p-8" x-data="themeBuilderData()">
    <div class="max-w-7xl mx-auto flex gap-6">
        <!-- Sidebar: Tree View cho App Categorization -->
        <div class="w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-4 sticky top-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-800 m-0 uppercase tracking-wider">App Folders</h2>
                    <button @click="openFolderModal()" class="text-indigo-600 hover:bg-indigo-50 p-1 rounded-md border-0 bg-transparent cursor-pointer transition-colors" title="Thêm Thư Mục">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </button>
                </div>
                
                <ul class="list-none m-0 p-0 space-y-1">
                    <li>
                        <button @click="activeFolder = 'all'" 
                            :class="activeFolder === 'all' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm border-0 cursor-pointer flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">apps</span>
                                Tất cả App
                            </span>
                            <span class="bg-slate-100 text-slate-500 text-xs py-0.5 px-2 rounded-full font-bold" x-text="templates.length"></span>
                        </button>
                    </li>
                    <li>
                        <button @click="activeFolder = 'core'" 
                            :class="activeFolder === 'core' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm border-0 cursor-pointer flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">public</span>
                                Core / Global
                            </span>
                            <span class="bg-slate-100 text-slate-500 text-xs py-0.5 px-2 rounded-full font-bold" x-text="templates.filter(t => !getTemplateFolderId(t)).length"></span>
                        </button>
                    </li>
                    <li class="my-2 border-t border-slate-100 pt-2"></li>
                    <template x-for="folder in folders" :key="folder.id">
                        <li class="group relative">
                            <button @click="activeFolder = folder.id" 
                                :class="activeFolder === folder.id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm border-0 cursor-pointer flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-symbols-outlined text-[18px] text-amber-400">folder</span>
                                    <span x-text="folder.name" class="truncate"></span>
                                </span>
                                <span class="bg-slate-100 text-slate-500 text-xs py-0.5 px-2 rounded-full font-bold" x-text="templates.filter(t => getTemplateFolderId(t) === folder.id).length"></span>
                            </button>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 shadow-sm rounded flex items-center">
                                <button @click.stop="editFolder(folder)" class="text-slate-400 hover:text-indigo-600 p-1 bg-transparent border-0 cursor-pointer"><span class="material-symbols-outlined text-[14px]">edit</span></button>
                                <button @click.stop="deleteFolder(folder.id)" class="text-slate-400 hover:text-rose-600 p-1 bg-transparent border-0 cursor-pointer"><span class="material-symbols-outlined text-[14px]">delete</span></button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header for Main Content (Tabs + Create Button) -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <!-- Navigation / Tabs -->
                <div class="flex space-x-1 bg-white p-1.5 rounded-2xl border border-slate-200/60 shadow-sm w-max overflow-x-auto max-w-full">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button 
                            @click="activeTab = tab.id"
                            :class="activeTab === tab.id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                            class="px-5 py-2.5 rounded-xl text-sm transition-all border-0 cursor-pointer flex items-center gap-2 whitespace-nowrap">
                            <span class="material-symbols-outlined text-[18px]" x-text="tab.icon"></span>
                            <span x-text="tab.name"></span>
                        </button>
                    </template>
                </div>

                <!-- Create Button -->
                <button @click="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Tạo Template
                </button>
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
                            <p class="text-xs text-slate-500 m-0 font-medium mb-1 flex items-center gap-1" x-show="getTemplateFolderId(template)">
                                <span class="material-symbols-outlined text-[14px] text-amber-400">folder</span>
                                <span x-text="getFolderName(getTemplateFolderId(template))"></span>
                            </p>
                            <p class="text-sm text-slate-500 m-0" x-text="'Organism: ' + (template.organism_id || 'Chưa chọn')"></p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                Cập nhật: <span x-text="template.updated_at || 'Mới đây'"></span>
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
        </div>

        <!-- Create/Edit Template Modal -->
        <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 m-0" x-text="modalMode === 'create' ? 'Tạo Template Mới' : 'Sửa Template'"></h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <!-- Tên Template -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Tên Template</label>
                        <input type="text" x-model="currentTemplate.title" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all" placeholder="Ví dụ: Header Mặc Định">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- App Folder -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Thuộc App / Thư mục</label>
                            <select x-model="currentTemplate.folder_id" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                                <option value="">Core / Global</option>
                                <template x-for="folder in folders" :key="folder.id">
                                    <option :value="folder.id" x-text="folder.name"></option>
                                </template>
                            </select>
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
                                    <template x-if="['post_type', 'specific_post', 'specific_portal', 'specific_portal_list', 'specific_portal_detail'].includes(rule.rule)">
                                        <input type="text" x-model="rule.value" :placeholder="rule.rule === 'specific_post' ? 'ID (VD: 12)' : (rule.rule.includes('portal') ? 'Portal slug (VD: nhan-vien)' : 'slug (VD: post, page)')" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:border-indigo-500 outline-none w-36">
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

        <!-- Folder Modal -->
        <div x-show="isFolderModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="closeFolderModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 m-0" x-text="folderModalMode === 'create' ? 'Thêm Thư Mục Mới' : 'Sửa Tên Thư Mục'"></h3>
                    <button @click="closeFolderModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Tên Thư Mục (VD: LMS App)</label>
                    <input type="text" x-model="currentFolder.name" @keydown.enter="saveFolder()" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    <button @click="closeFolderModal()" class="px-4 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">Hủy</button>
                    <button @click="saveFolder()" class="px-4 py-2 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                        <span x-show="isFolderLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                        Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $folders_json = get_option('ska_theme_builder_folders', '[]'); ?>
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
        
        folders: <?php echo $folders_json ? $folders_json : '[]'; ?>,
        activeFolder: 'all', // 'all', 'core', or folder.id

        isFolderModalOpen: false,
        folderModalMode: 'create',
        isFolderLoading: false,
        currentFolder: { id: '', name: '' },

        isModalOpen: false,
        modalMode: 'create',
        isLoading: false,
        organisms: [], // Store organisms for selection
        currentTemplate: {
            id: null,
            title: '',
            location: 'header',
            organism_id: '',
            folder_id: '',
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
            { value: 'is_search', label: 'Kết quả tìm kiếm (Search)' },
            { value: 'is_portal', label: 'Tất cả App Portal' },
            { value: 'specific_portal', label: 'App Portal cụ thể (Theo Slug)' },
            { value: 'specific_portal_list', label: 'App Portal List View (Theo Slug)' },
            { value: 'specific_portal_detail', label: 'App Portal Detail View (Theo Slug)' }
        ],

        apiUrl: '<?php echo esc_url( rest_url( 'ska-builder/v1/theme-templates' ) ); ?>',
        apiFoldersUrl: ajaxurl, // admin-ajax.php for folder updates
        apiOrganismsUrl: '<?php echo esc_url( rest_url( 'ska-design/v1/organisms' ) ); ?>',
        apiNonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',
        adminNonce: '<?php echo esc_js( wp_create_nonce( 'ska_theme_builder_folders_nonce' ) ); ?>',

        init() {
            this.loadTemplates();
            this.loadOrganisms();
        },

        get filteredTemplates() {
            let result = this.templates;
            
            // Filter by Folder
            if (this.activeFolder === 'core') {
                result = result.filter(t => !this.getTemplateFolderId(t));
            } else if (this.activeFolder !== 'all') {
                result = result.filter(t => this.getTemplateFolderId(t) === this.activeFolder);
            }

            // Filter by Tab
            if (this.activeTab !== 'all') {
                result = result.filter(t => t.location === this.activeTab);
            }

            return result;
        },

        getTemplateFolderId(template) {
            try {
                const parsed = JSON.parse(template.conditions || '{}');
                return (parsed && parsed.folder_id) ? parsed.folder_id : '';
            } catch(e) {
                return '';
            }
        },

        getFolderName(folderId) {
            if (!folderId) return '';
            const folder = this.folders.find(f => f.id === folderId);
            return folder ? folder.name : folderId;
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
                folder_id: (this.activeFolder !== 'all' && this.activeFolder !== 'core') ? this.activeFolder : '',
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
                    let parsed = JSON.parse(this.currentTemplate.conditions || '{}');
                    if (Array.isArray(parsed)) {
                        this.rules = parsed;
                        this.currentTemplate.folder_id = '';
                    } else if (parsed && typeof parsed === 'object') {
                        this.rules = parsed.rules || [];
                        this.currentTemplate.folder_id = parsed.folder_id || '';
                        
                        if (parsed.include === 'all') { // Legacy fallback
                            this.rules = [{ type: 'include', rule: 'all', value: '' }];
                            this.currentTemplate.folder_id = '';
                        }
                    }
                } catch(e) {
                    this.rules = [];
                    this.currentTemplate.folder_id = '';
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

            this.currentTemplate.conditions = JSON.stringify({
                rules: this.rules,
                folder_id: this.currentTemplate.folder_id
            });

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
        },

        // Folder Methods
        openFolderModal() {
            this.folderModalMode = 'create';
            this.currentFolder = { id: '', name: '' };
            this.isFolderModalOpen = true;
        },

        editFolder(folder) {
            this.folderModalMode = 'edit';
            this.currentFolder = { ...folder };
            this.isFolderModalOpen = true;
        },

        closeFolderModal() {
            this.isFolderModalOpen = false;
        },

        async saveFolder() {
            if (!this.currentFolder.name.trim()) return;
            
            this.isFolderLoading = true;
            let newFolders = [...this.folders];
            
            if (this.folderModalMode === 'create') {
                const newId = 'f_' + Math.random().toString(36).substr(2, 9);
                newFolders.push({ id: newId, name: this.currentFolder.name.trim() });
            } else {
                const index = newFolders.findIndex(f => f.id === this.currentFolder.id);
                if (index > -1) newFolders[index].name = this.currentFolder.name.trim();
            }

            await this.syncFoldersToServer(newFolders);
            this.closeFolderModal();
        },

        async deleteFolder(id) {
            if (confirm('Xóa thư mục này? Các template bên trong sẽ được chuyển về Core/Global.')) {
                let newFolders = this.folders.filter(f => f.id !== id);
                
                // Cần dọn folder_id ở template, Frontend dọn UI trước, Backend dọn sau nếu cần
                this.templates.forEach(t => {
                    if (this.getTemplateFolderId(t) === id) {
                        try {
                            let parsed = JSON.parse(t.conditions || '{}');
                            if (parsed && typeof parsed === 'object') {
                                parsed.folder_id = '';
                                t.conditions = JSON.stringify(parsed);
                                
                                // Auto save template? Optional. UI updates locally.
                                fetch(this.apiUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': this.apiNonce },
                                    body: JSON.stringify(t)
                                });
                            }
                        } catch(e) {}
                    }
                });

                if (this.activeFolder === id) this.activeFolder = 'all';
                await this.syncFoldersToServer(newFolders);
            }
        },

        async syncFoldersToServer(newFolders) {
            const formData = new FormData();
            formData.append('action', 'ska_theme_builder_save_folders');
            formData.append('_ajax_nonce', this.adminNonce);
            formData.append('folders', JSON.stringify(newFolders));

            try {
                const response = await fetch(this.apiFoldersUrl, {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                if (res.success) {
                    this.folders = newFolders;
                } else {
                    alert('Lỗi lưu thư mục: ' + (res.data || ''));
                }
            } catch (e) {
                alert('Lỗi kết nối khi lưu thư mục.');
            } finally {
                this.isFolderLoading = false;
            }
        }
    }));
});
</script>
