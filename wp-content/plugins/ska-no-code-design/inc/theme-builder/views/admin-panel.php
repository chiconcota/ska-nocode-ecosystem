<div class="wrap ska-theme-builder-panel bg-slate-50 min-h-screen -ml-5 -mt-2 p-8" x-data="themeBuilderData()">
    <div class="max-w-7xl mx-auto flex gap-6">
        <!-- Sidebar: Tree View cho App Categorization -->
        <div class="w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-4 sticky top-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-800 m-0 uppercase tracking-wider">App Folders</h2>
                    <button @click="openFolderModal()" class="text-indigo-600 hover:bg-indigo-50 p-1 rounded-md border-0 bg-transparent cursor-pointer transition-colors" title="<?php esc_attr_e( 'Add Folder', 'ska-no-code-design' ); ?>">
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
                                <?php esc_html_e( 'All Apps', 'ska-no-code-design' ); ?>
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
                    <?php esc_html_e( 'Create Template', 'ska-no-code-design' ); ?>
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
                                            <?php esc_html_e( 'Edit Settings', 'ska-no-code-design' ); ?>
                                        </button>
                                        <button @click="deleteTemplate(template.id)" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            <?php esc_html_e( 'Delete Template', 'ska-no-code-design' ); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-bold text-slate-800 m-0 mb-2" x-text="template.title"></h3>
                            <p class="text-xs text-slate-500 m-0 font-medium mb-1 flex items-center gap-1" x-show="getTemplateFolderId(template)">
                                <span class="material-symbols-outlined text-[14px] text-amber-400">folder</span>
                                <span x-text="getFolderName(getTemplateFolderId(template))"></span>
                            </p>
                            <p class="text-sm text-slate-500 m-0" x-text="'<?php echo esc_js( __( 'Organism:', 'ska-no-code-design' ) ); ?> ' + (template.organism_id || '<?php echo esc_js( __( 'Not selected', 'ska-no-code-design' ) ); ?>')"></p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                 <?php esc_html_e( 'Updated:', 'ska-no-code-design' ); ?> <span x-text="template.updated_at || '<?php echo esc_js( __( 'Recently', 'ska-no-code-design' ) ); ?>'"></span>
                            </span>
                            <a :href="getEditorUrl(template.id)" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1 no-underline">
                                <span class="material-symbols-outlined text-[18px]">design_services</span>
                                <?php esc_html_e( 'Open Editor', 'ska-no-code-design' ); ?>
                            </a>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="filteredTemplates.length === 0" class="col-span-full py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">inventory_2</span>
                    <p class="text-slate-500 font-medium"><?php esc_html_e( 'There are no templates in this category yet.', 'ska-no-code-design' ); ?></p>
                    <button @click="openCreateModal()" class="mt-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-lg shadow-sm transition-all border-0 cursor-pointer">
                        <?php esc_html_e( 'Create first template', 'ska-no-code-design' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Template Modal -->
        <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 m-0" x-text="modalMode === 'create' ? '<?php echo esc_js( __( 'Create New Template', 'ska-no-code-design' ) ); ?>' : '<?php echo esc_js( __( 'Edit Template', 'ska-no-code-design' ) ); ?>'"></h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <!-- Tên Template -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Template name', 'ska-no-code-design' ); ?></label>
                        <input type="text" x-model="currentTemplate.title" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all" placeholder="<?php esc_attr_e( 'For example: Default Header', 'ska-no-code-design' ); ?>">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- App Folder -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Belongs to App/Folder', 'ska-no-code-design' ); ?></label>
                            <select x-model="currentTemplate.folder_id" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                                <option value="">Core / Global</option>
                                <template x-for="folder in folders" :key="folder.id">
                                    <option :value="folder.id" x-text="folder.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Vị trí (Location) -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Location', 'ska-no-code-design' ); ?></label>
                            <select x-model="currentTemplate.location" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                                <template x-for="tab in tabs.filter(t => t.id !== 'all')" :key="tab.id">
                                    <option :value="tab.id" x-text="tab.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Component (Organism) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Design (Organism Component)', 'ska-no-code-design' ); ?></label>
                        <select x-model="currentTemplate.organism_id" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all bg-white">
                            <option value=""><?php esc_html_e( '-- Select Component --', 'ska-no-code-design' ); ?></option>
                            <template x-for="org in organisms" :key="org.id">
                                <option :value="org.id" x-text="org.name"></option>
                            </template>
                        </select>
                        <p class="text-xs text-slate-500 mt-1"><?php esc_html_e( 'Select the component from Design Workspace to assign to this location.', 'ska-no-code-design' ); ?></p>
                    </div>

                    <!-- Điều kiện (Conditions) Rule Builder -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 m-0"><?php esc_html_e( 'Display Conditions', 'ska-no-code-design' ); ?></label>
                                <p class="text-[11px] text-slate-500 m-0 mt-0.5"><?php esc_html_e( 'Determines where this Template appears.', 'ska-no-code-design' ); ?></p>
                            </div>
                            <button @click.prevent="addRule()" class="text-xs bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-2 py-1.5 rounded-md font-bold border-0 cursor-pointer flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[14px]">add</span> <?php esc_html_e( 'Add Rule', 'ska-no-code-design' ); ?>
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
                                        <input type="text" x-model="rule.value" :placeholder="rule.rule === 'specific_post' ? 'ID (VD: 12)' : 'slug (VD: post, page)'" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:border-indigo-500 outline-none w-48">
                                    </template>
                                    
                                    <template x-if="['specific_portal', 'specific_portal_list', 'specific_portal_detail', 'specific_portal_create'].includes(rule.rule)">
                                        <select x-model="rule.value" class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:border-indigo-500 outline-none min-w-48 bg-white">
                                            <option value=""><?php esc_html_e( '-- Select App Portal --', 'ska-no-code-design' ); ?></option>
                                            <template x-for="portal in portals" :key="portal.slug">
                                                <option :value="portal.slug" x-text="portal.label"></option>
                                            </template>
                                        </select>
                                    </template>
                                    
                                    <!-- Delete -->
                                    <button @click.prevent="removeRule(index)" class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 bg-transparent border-0 cursor-pointer p-1.5 rounded-md transition-colors flex-shrink-0">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                            </template>
                            
                            <div x-show="rules.length === 0" class="text-center py-4 text-sm text-slate-500 italic bg-white rounded-lg border border-slate-200 border-dashed">
                                <?php esc_html_e( 'No conditions yet. Template will display on default Location.', 'ska-no-code-design' ); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Trạng thái Active -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="currentTemplate.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                        <span class="text-sm font-bold text-slate-700"><?php esc_html_e( 'Activate Template (Active)', 'ska-no-code-design' ); ?></span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    <button @click="closeModal()" class="px-5 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                        <?php esc_html_e( 'Cancel', 'ska-no-code-design' ); ?>
                    </button>
                    <button @click="saveTemplate()" class="px-5 py-2 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                        <span x-show="isLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                        <span x-text="modalMode === 'create' ? '<?php echo esc_js( __( 'Create Template', 'ska-no-code-design' ) ); ?>' : '<?php echo esc_js( __( 'Save Changes', 'ska-no-code-design' ) ); ?>'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Folder Modal -->
        <div x-show="isFolderModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="closeFolderModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 m-0" x-text="folderModalMode === 'create' ? '<?php echo esc_js( __( 'Add New Folder', 'ska-no-code-design' ) ); ?>' : '<?php echo esc_js( __( 'Edit Folder Name', 'ska-no-code-design' ) ); ?>'"></h3>
                    <button @click="closeFolderModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-6">
                    <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Folder Name (Example: LMS App)', 'ska-no-code-design' ); ?></label>
                    <input type="text" x-model="currentFolder.name" @keydown.enter="saveFolder()" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    <button @click="closeFolderModal()" class="px-4 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all"><?php esc_html_e( 'Cancel', 'ska-no-code-design' ); ?></button>
                    <button @click="saveFolder()" class="px-4 py-2 rounded-xl text-white font-bold bg-indigo-600 hover:bg-indigo-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                        <span x-show="isFolderLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                        <?php esc_html_e( 'Save', 'ska-no-code-design' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$folders_json = get_option('ska_theme_builder_folders', '[]'); 
$dictionary = get_option('ska_data_dictionary', array());
$portals = array();
foreach ($dictionary as $table_name => $schema) {
    $table_info = isset($schema['__table_info']) ? $schema['__table_info'] : array();
    
    $portal_settings = isset($table_info['portal_settings']) ? $table_info['portal_settings'] : array();
    if (empty($portal_settings['active']) || empty($portal_settings['slug'])) {
        continue; // Chỉ lấy các App Portal đã được kích hoạt
    }
    
    $table_label = isset($table_info['label']) ? $table_info['label'] : $table_name;
    $portal_slug = $portal_settings['slug'];
    
    $portals[] = array(
        'label' => $table_label . ' (/' . $portal_slug . ')',
        'slug' => $portal_slug
    );
}
?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('themeBuilderData', () => ({
        portals: <?php echo wp_json_encode($portals); ?>,
        tabs: [
            { id: 'all', name: '<?php echo esc_js( __( 'All', 'ska-no-code-design' ) ); ?>', icon: 'grid_view' },
            { id: 'header', name: 'Header', icon: 'vertical_align_top' },
            { id: 'footer', name: 'Footer', icon: 'vertical_align_bottom' },
            { id: 'single', name: 'Single', icon: 'article' },
            { id: 'archive', name: 'Archive', icon: 'view_agenda' },
            { id: '404', name: '404 Page', icon: 'error' },
            { id: '403', name: '403 Forbidden', icon: 'lock' },
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
            { value: 'all', label: '<?php echo esc_js( __( 'Entire Site', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_front_page', label: '<?php echo esc_js( __( 'Home page (Front Page)', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_archive', label: '<?php echo esc_js( __( 'All Archives', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_single', label: '<?php echo esc_js( __( 'All Posts/Pages (Singular)', 'ska-no-code-design' ) ); ?>' },
            { value: 'post_type', label: '<?php echo esc_js( __( 'By Post Type (Post Type)', 'ska-no-code-design' ) ); ?>' },
            { value: 'specific_post', label: '<?php echo esc_js( __( 'Specific post (Post/Page ID)', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_404', label: '<?php echo esc_js( __( 'Error page (404)', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_403', label: '<?php echo esc_js( __( 'Access Denied page (403)', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_search', label: '<?php echo esc_js( __( 'Search results (Search)', 'ska-no-code-design' ) ); ?>' },
            { value: 'is_portal', label: '<?php echo esc_js( __( 'All App Portals', 'ska-no-code-design' ) ); ?>' },
            { value: 'specific_portal', label: '<?php echo esc_js( __( 'Specific App Portal (According to Slug)', 'ska-no-code-design' ) ); ?>' },
            { value: 'specific_portal_list', label: '<?php echo esc_js( __( 'App Portal List View (By Slug)', 'ska-no-code-design' ) ); ?>' },
            { value: 'specific_portal_detail', label: '<?php echo esc_js( __( 'App Portal Detail View (By Slug)', 'ska-no-code-design' ) ); ?>' },
            { value: 'specific_portal_create', label: '<?php echo esc_js( __( 'App Portal Create View (By Slug)', 'ska-no-code-design' ) ); ?>' }
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
                '403': 'bg-red-100 text-red-700',
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
                alert('<?php echo esc_js( __( 'Please enter a template name.', 'ska-no-code-design' ) ); ?>');
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
                    alert(result.message || '<?php echo esc_js( __( 'An error occurred while saving the template.', 'ska-no-code-design' ) ); ?>');
                }
            } catch (error) {
                console.error(error);
                alert('<?php echo esc_js( __( 'Connection error.', 'ska-no-code-design' ) ); ?>');
            } finally {
                this.isLoading = false;
            }
        },

        async deleteTemplate(id) {
            if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this template? ', 'ska-no-code-design' ) ); ?>')) {
                try {
                    const response = await fetch(`${this.apiUrl}/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': this.apiNonce }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        this.templates = this.templates.filter(t => t.id !== id);
                    } else {
                        alert(result.message || '<?php echo esc_js( __( 'Templates cannot be deleted.', 'ska-no-code-design' ) ); ?>');
                    }
                } catch (error) {
                    console.error(error);
                    alert('<?php echo esc_js( __( 'Connection error.', 'ska-no-code-design' ) ); ?>');
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
            if (confirm('<?php echo esc_js( __( 'Delete this folder? ', 'ska-no-code-design' ) ); ?>')) {
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
                    alert('<?php echo esc_js( __( 'Folder save error:', 'ska-no-code-design' ) ); ?> ' + (res.data || ''));
                }
            } catch (e) {
                alert('<?php echo esc_js( __( 'Connection error when saving folder.', 'ska-no-code-design' ) ); ?>');
            } finally {
                this.isFolderLoading = false;
            }
        }
    }));
});
</script>
