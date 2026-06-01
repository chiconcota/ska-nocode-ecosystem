<div class="wrap ska-workspace-panel bg-slate-50 min-h-screen -ml-5 -mt-2 flex overflow-hidden" x-data="workspaceData()">
    <!-- Left Sidebar: Categories -->
    <div class="w-64 bg-white border-r border-slate-200 flex flex-col h-screen shrink-0 shadow-sm z-10">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-pink-500 font-semibold text-2xl">category</span>
                <span class="font-bold text-slate-800 text-lg"><?php esc_html_e( 'Categories', 'ska-no-code-design' ); ?></span>
            </div>
            <button @click="isCategoryModalOpen = true" class="text-slate-400 hover:text-pink-600 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-slate-55 flex items-center transition-all" title="<?php echo esc_attr( __( 'Manage Categories', 'ska-no-code-design' ) ); ?>">
                <span class="material-symbols-outlined text-[20px]">settings</span>
            </button>
        </div>
        
        <!-- Categories List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-1">
            <!-- All Symbols -->
            <button @click="activeCategory = 'all'" 
                    :class="activeCategory === 'all' ? 'bg-pink-50 text-pink-600 font-bold border-l-4 border-pink-600' : 'text-slate-600 hover:text-pink-600 hover:bg-slate-50 border-l-4 border-transparent'"
                    class="w-full text-left px-4 py-3 rounded-xl flex items-center justify-between transition-all border-0 bg-transparent cursor-pointer">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] opacity-80">view_cozy</span>
                    <span><?php esc_html_e( 'All Symbols', 'ska-no-code-design' ); ?></span>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold" x-text="organisms.length"></span>
            </button>

            <!-- Uncategorized -->
            <button @click="activeCategory = 'uncategorized'" 
                    :class="activeCategory === 'uncategorized' ? 'bg-pink-50 text-pink-600 font-bold border-l-4 border-pink-600' : 'text-slate-600 hover:text-pink-600 hover:bg-slate-50 border-l-4 border-transparent'"
                    class="w-full text-left px-4 py-3 rounded-xl flex items-center justify-between transition-all border-0 bg-transparent cursor-pointer">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] opacity-80">folder_open</span>
                    <span><?php esc_html_e( 'Uncategorized', 'ska-no-code-design' ); ?></span>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold" x-text="organisms.filter(o => !o.category).length"></span>
            </button>
            
            <div class="h-px bg-slate-100 my-4 mx-2"></div>
            
            <!-- Custom Categories (Drag & Drop + Inline Rename) -->
            <template x-for="(cat, catIndex) in categories" :key="cat">
                <div class="group/cat flex items-center gap-1 relative"
                     draggable="true"
                     @dragstart="dragStart(catIndex, $event)"
                     @dragover.prevent="dragOver(catIndex, $event)"
                     @dragend="dragEnd()"
                     @drop.prevent="dropCategory(catIndex)"
                     :class="dragOverIndex === catIndex ? 'border-t-2 border-pink-400' : 'border-t-2 border-transparent'">
                    <!-- Grip Handle -->
                    <span class="material-symbols-outlined text-[16px] text-slate-300 cursor-grab opacity-0 group-hover/cat:opacity-100 transition-opacity shrink-0 select-none"
                          :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'"
                          title="<?php echo esc_attr( __( 'Drag to reorder', 'ska-no-code-design' ) ); ?>"
                    >drag_indicator</span>

                    <!-- Normal display mode -->
                    <button x-show="editingCategory !== cat"
                            @click="activeCategory = cat"
                            :class="activeCategory === cat ? 'bg-pink-50 text-pink-600 font-bold border-l-4 border-pink-600' : 'text-slate-600 hover:text-pink-600 hover:bg-slate-50 border-l-4 border-transparent'"
                            class="flex-1 text-left px-3 py-3 rounded-xl flex items-center justify-between transition-all border-0 bg-transparent cursor-pointer min-w-0">
                        <div class="flex items-center gap-2 truncate min-w-0">
                            <span class="material-symbols-outlined text-[20px] opacity-80 shrink-0">folder</span>
                            <span class="truncate" x-text="cat"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <!-- Rename icon (pencil) -->
                            <span @click.stop="startRenameCategory(cat)"
                                  class="material-symbols-outlined text-[16px] text-slate-300 hover:text-pink-500 opacity-0 group-hover/cat:opacity-100 transition-all cursor-pointer"
                                  title="<?php echo esc_attr( __( 'Rename', 'ska-no-code-design' ) ); ?>"
                            >edit</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold" x-text="organisms.filter(o => o.category === cat).length"></span>
                        </div>
                    </button>

                    <!-- Inline rename input mode -->
                    <div x-show="editingCategory === cat" x-cloak class="flex-1 flex items-center gap-1 px-2 py-1.5">
                        <input type="text" x-model="editingCategoryName"
                               @keydown.enter.prevent="confirmRenameCategory(cat)"
                               @keydown.escape.prevent="cancelRenameCategory()"
                               x-ref="renameInput"
                               class="flex-1 border border-pink-400 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-pink-500 outline-none min-w-0">
                        <button @click="confirmRenameCategory(cat)" class="text-green-600 hover:text-green-700 bg-transparent border-0 cursor-pointer p-0.5 rounded flex items-center" title="<?php echo esc_attr( __( 'Save', 'ska-no-code-design' ) ); ?>">
                            <span class="material-symbols-outlined text-[18px]">check</span>
                        </button>
                        <button @click="cancelRenameCategory()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer p-0.5 rounded flex items-center" title="<?php echo esc_attr( __( 'Cancel', 'ska-no-code-design' ) ); ?>">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Right Side: Content Area -->
    <div class="flex-1 p-8 overflow-y-auto h-screen">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 m-0 flex items-center gap-3">
                        <span class="material-symbols-outlined text-4xl text-pink-500">grid_view</span>
                        <?php esc_html_e( 'Design Workspace', 'ska-no-code-design' ); ?>
                    </h1>
                    <p class="text-slate-500 mt-2"><?php esc_html_e( 'Reusable Component Manager (Organisms / Symbols) for the Ska ecosystem.', 'ska-no-code-design' ); ?></p>
                </div>
                <div>
                    <button @click="openCreateModal()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all flex items-center gap-2 border-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <?php esc_html_e( 'Create New Symbol', 'ska-no-code-design' ); ?>
                    </button>
                </div>
            </div>

            <!-- Current Category Title Banner -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-6 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-pink-500">folder_open</span>
                    <span class="font-bold text-slate-800 text-base" x-text="activeCategory === 'all' ? '<?php echo esc_js( __( 'All Symbols', 'ska-no-code-design' ) ); ?>' : (activeCategory === 'uncategorized' ? '<?php echo esc_js( __( 'Uncategorized', 'ska-no-code-design' ) ); ?>' : activeCategory)"></span>
                </div>
                <span class="text-sm text-slate-500 font-medium" x-text="filteredOrganisms.length + ' <?php echo esc_js( __( 'item(s)', 'ska-no-code-design' ) ); ?>'"></span>
            </div>

            <!-- List Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="organism in filteredOrganisms" :key="organism.id">
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-pink-300 transition-all group flex flex-col justify-between relative min-h-[160px]">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex flex-wrap gap-1.5 max-w-[80%]">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-pink-100 text-pink-700">
                                        <?php esc_html_e( 'Symbol', 'ska-no-code-design' ); ?>
                                    </span>
                                    <span x-show="organism.category" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 truncate max-w-[120px]" x-text="organism.category" :title="organism.category"></span>
                                    <span x-show="!organism.category" class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-amber-50 text-amber-700">
                                        <?php esc_html_e( 'Uncategorized', 'ska-no-code-design' ); ?>
                                    </span>
                                </div>
                                
                                <!-- Context Menu -->
                                <div class="relative" x-data="{ openMenu: false }">
                                    <button @click="openMenu = !openMenu" @click.outside="openMenu = false" class="text-slate-400 hover:text-slate-700 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-slate-50">
                                        <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                    </button>
                                    <div x-show="openMenu" x-transition.opacity class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1" style="display: none;">
                                        <button @click="openMoveModal(organism)" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">drive_file_move</span>
                                            <?php esc_html_e( 'Move to Category', 'ska-no-code-design' ); ?>
                                        </button>
                                        <button @click="deleteOrganism(organism.id)" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            <?php esc_html_e( 'Delete Symbol', 'ska-no-code-design' ); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-bold text-slate-800 m-0 mb-2 truncate" x-text="organism.name" :title="organism.name"></h3>
                            <p class="text-sm text-slate-500 m-0"><?php esc_html_e( 'Atomic Component', 'ska-no-code-design' ); ?></p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <?php esc_html_e( 'Updated:', 'ska-no-code-design' ); ?> <span x-text="organism.updated_at"></span>
                            </span>
                            <a :href="getEditorUrl(organism.id)" class="text-pink-600 hover:text-pink-800 font-bold text-sm flex items-center gap-1 no-underline">
                                <span class="material-symbols-outlined text-[18px]">design_services</span>
                                <?php esc_html_e( 'Open Editor', 'ska-no-code-design' ); ?>
                            </a>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="filteredOrganisms.length === 0" class="col-span-full py-16 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">folder_off</span>
                    <p class="text-slate-500 font-medium"><?php esc_html_e( 'No components found in this category.', 'ska-no-code-design' ); ?></p>
                    <button @click="openCreateModal()" class="mt-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-lg shadow-sm transition-all border-0 cursor-pointer">
                        <?php esc_html_e( 'Create New Symbol', 'ska-no-code-design' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Symbol Modal -->
    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800 m-0"><?php esc_html_e( 'Create New Symbol', 'ska-no-code-design' ); ?></h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Tên Symbol -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Symbol Name (Component)', 'ska-no-code-design' ); ?></label>
                    <input type="text" x-model="currentName" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all" placeholder="<?php echo esc_attr( __( 'Example: Primary button', 'ska-no-code-design' ) ); ?>">
                </div>
                
                <!-- Category Select -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1"><?php esc_html_e( 'Category', 'ska-no-code-design' ); ?></label>
                    <select x-model="currentCreateCategory" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 bg-white focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all">
                        <option value=""><?php esc_html_e( 'Uncategorized', 'ska-no-code-design' ); ?></option>
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                <button @click="closeModal()" class="px-5 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                    <?php esc_html_e( 'Cancel', 'ska-no-code-design' ); ?>
                </button>
                <button @click="saveOrganism()" class="px-5 py-2 rounded-xl text-white font-bold bg-pink-600 hover:bg-pink-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                    <span x-show="isLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                    <span><?php esc_html_e( 'Create New', 'ska-no-code-design' ); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Manage Categories Modal -->
    <div x-show="isCategoryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="isCategoryModalOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800 m-0"><?php esc_html_e( 'Manage Categories', 'ska-no-code-design' ); ?></h3>
                <button @click="isCategoryModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Add Category -->
                <div class="flex gap-2">
                    <input type="text" x-model="newCategoryName" @keydown.enter.prevent="addCategory()" class="flex-1 border border-slate-300 rounded-xl px-4 py-2 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all" placeholder="<?php echo esc_attr( __( 'New category name...', 'ska-no-code-design' ) ); ?>">
                    <button @click="addCategory()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold px-4 py-2 rounded-xl border-0 cursor-pointer transition-all">
                        <?php esc_html_e( 'Add', 'ska-no-code-design' ); ?>
                    </button>
                </div>
                
                <!-- Category List in Modal -->
                <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-60 overflow-y-auto bg-slate-50">
                    <template x-for="(cat, catIndex) in categories" :key="cat">
                        <div class="group/modal-cat px-4 py-3 flex items-center justify-between bg-white"
                             draggable="true"
                             @dragstart="dragStart(catIndex, $event)"
                             @dragover.prevent="dragOver(catIndex, $event)"
                             @dragend="dragEnd()"
                             @drop.prevent="dropCategory(catIndex)"
                             :class="dragOverIndex === catIndex ? 'border-t-2 border-pink-400' : ''">
                            <!-- Normal display -->
                            <div x-show="editingCategory !== cat" class="flex items-center gap-2 flex-1 min-w-0">
                                <span class="material-symbols-outlined text-[16px] text-slate-300 cursor-grab opacity-0 group-hover/modal-cat:opacity-100 transition-opacity shrink-0">drag_indicator</span>
                                <span class="font-medium text-slate-700 truncate" x-text="cat"></span>
                            </div>
                            <!-- Inline rename mode -->
                            <div x-show="editingCategory === cat" x-cloak class="flex items-center gap-1 flex-1 min-w-0">
                                <input type="text" x-model="editingCategoryName"
                                       @keydown.enter.prevent="confirmRenameCategory(cat)"
                                       @keydown.escape.prevent="cancelRenameCategory()"
                                       class="flex-1 border border-pink-400 rounded-lg px-2 py-1 text-sm focus:ring-1 focus:ring-pink-500 outline-none min-w-0">
                                <button @click="confirmRenameCategory(cat)" class="text-green-600 hover:text-green-700 bg-transparent border-0 cursor-pointer p-0.5 rounded flex items-center">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                </button>
                                <button @click="cancelRenameCategory()" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer p-0.5 rounded flex items-center">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>
                            <!-- Action buttons (show when not editing) -->
                            <div x-show="editingCategory !== cat" class="flex items-center gap-1 shrink-0">
                                <button @click="startRenameCategory(cat)" class="text-slate-400 hover:text-pink-600 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-slate-50 flex items-center opacity-0 group-hover/modal-cat:opacity-100 transition-opacity" title="<?php echo esc_attr( __( 'Rename Category', 'ska-no-code-design' ) ); ?>">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="deleteCategory(cat)" class="text-rose-500 hover:text-rose-700 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-rose-50 flex items-center" title="<?php echo esc_attr( __( 'Delete Category', 'ska-no-code-design' ) ); ?>">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="categories.length === 0" class="p-4 text-center text-slate-400 italic text-sm">
                        <?php esc_html_e( 'No custom categories yet.', 'ska-no-code-design' ); ?>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end bg-slate-50">
                <button @click="isCategoryModalOpen = false" class="px-5 py-2 rounded-xl text-slate-700 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                    <?php esc_html_e( 'Close', 'ska-no-code-design' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Move Symbol Modal -->
    <div x-show="isMoveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="isMoveModalOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all" x-transition.scale.origin.bottom>
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800 m-0"><?php esc_html_e( 'Move Symbol to Category', 'ska-no-code-design' ); ?></h3>
                <button @click="isMoveModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-transparent border-0 cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-slate-600 m-0 mb-3"><?php esc_html_e( 'Choose a category for', 'ska-no-code-design' ); ?> <strong x-text="symbolToMove ? symbolToMove.name : ''"></strong>:</p>
                    <select x-model="moveToCategoryVal" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 bg-white focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all">
                        <option value=""><?php esc_html_e( 'Uncategorized', 'ska-no-code-design' ); ?></option>
                        <template x-for="cat in categories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                <button @click="isMoveModalOpen = false" class="px-5 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                    <?php esc_html_e( 'Cancel', 'ska-no-code-design' ); ?>
                </button>
                <button @click="confirmMove()" class="px-5 py-2 rounded-xl text-white font-bold bg-pink-600 hover:bg-pink-700 shadow-sm border-0 cursor-pointer transition-all">
                    <?php esc_html_e( 'Save Changes', 'ska-no-code-design' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('workspaceData', () => ({
        organisms: [],
        categories: [],
        activeCategory: 'all',
        
        isModalOpen: false,
        isCategoryModalOpen: false,
        isMoveModalOpen: false,
        isLoading: false,
        currentName: '',
        currentCreateCategory: '',
        newCategoryName: '',
        symbolToMove: null,
        moveToCategoryVal: '',

        // Rename state
        editingCategory: null,
        editingCategoryName: '',

        // Drag & Drop state
        isDragging: false,
        dragIndex: null,
        dragOverIndex: null,

        apiUrl: '<?php echo esc_url( rest_url( 'ska-design/v1/organisms' ) ); ?>',
        categoriesUrl: '<?php echo esc_url( rest_url( 'ska-design/v1/categories' ) ); ?>',
        apiNonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',

        init() {
            this.loadOrganisms();
            this.loadCategories();
        },

        getEditorUrl(organismId) {
            return `<?php echo admin_url('admin.php?page=ska-organism-editor&organism_id='); ?>${organismId}`;
        },

        async loadOrganisms() {
            try {
                const response = await fetch(this.apiUrl, {
                    headers: { 'X-WP-Nonce': this.apiNonce }
                });
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        this.organisms = result.data.data ? result.data.data : result.data;
                    }
                } else {
                    console.error('Failed to load organisms');
                }
            } catch (error) {
                console.error(error);
            }
        },

        async loadCategories() {
            try {
                const response = await fetch(this.categoriesUrl, {
                    headers: { 'X-WP-Nonce': this.apiNonce }
                });
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        this.categories = result.data;
                    }
                }
            } catch (error) {
                console.error(error);
            }
        },

        async saveCategoriesList() {
            try {
                const response = await fetch(this.categoriesUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.apiNonce
                    },
                    body: JSON.stringify({ categories: this.categories })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    this.categories = result.data;
                } else {
                    alert(result.message || 'Error saving categories');
                }
            } catch (error) {
                console.error(error);
            }
        },

        addCategory() {
            const name = this.newCategoryName.trim();
            if (!name) return;
            if (this.categories.includes(name)) {
                alert('<?php echo esc_js( __( 'Category already exists.', 'ska-no-code-design' ) ); ?>');
                return;
            }
            this.categories.push(name);
            this.newCategoryName = '';
            this.saveCategoriesList();
        },

        deleteCategory(name) {
            if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this category? Symbols in this category will become Uncategorized.', 'ska-no-code-design' ) ); ?>')) {
                this.categories = this.categories.filter(c => c !== name);
                this.saveCategoriesList();
                
                // Cập nhật local state các Symbol thuộc category này về rỗng
                this.organisms.forEach(async (org) => {
                    if (org.category === name) {
                        org.category = '';
                        await this.updateSymbolCategory(org.id, '');
                    }
                });
                
                if (this.activeCategory === name) {
                    this.activeCategory = 'all';
                }
            }
        },

        async updateSymbolCategory(id, category) {
            try {
                const response = await fetch(`${this.apiUrl}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.apiNonce
                    },
                    body: JSON.stringify({ category: category })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    const org = this.organisms.find(o => o.id === id);
                    if (org) {
                        org.category = category;
                    }
                } else {
                    alert(result.message || 'Error moving symbol');
                }
            } catch (error) {
                console.error(error);
            }
        },

        openMoveModal(org) {
            this.symbolToMove = org;
            this.moveToCategoryVal = org.category || '';
            this.isMoveModalOpen = true;
        },

        async confirmMove() {
            if (!this.symbolToMove) return;
            await this.updateSymbolCategory(this.symbolToMove.id, this.moveToCategoryVal);
            this.isMoveModalOpen = false;
            this.symbolToMove = null;
        },

        openCreateModal() {
            this.currentName = '';
            this.currentCreateCategory = this.activeCategory !== 'all' && this.activeCategory !== 'uncategorized' ? this.activeCategory : '';
            this.isModalOpen = true;
        },

        async saveOrganism() {
            if (!this.currentName.trim()) {
                alert('<?php echo esc_js( __( 'Please enter a Symbol name.', 'ska-no-code-design' ) ); ?>');
                return;
            }

            this.isLoading = true;
            
            try {
                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.apiNonce
                    },
                    body: JSON.stringify({
                        name: this.currentName,
                        category: this.currentCreateCategory,
                        block_json: {} // Empty object for new organism
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    await this.loadOrganisms();
                    this.closeModal();
                } else {
                    alert(result.message || '<?php echo esc_js( __( 'An error occurred while creating Symbol.', 'ska-no-code-design' ) ); ?>');
                }
            } catch (error) {
                console.error(error);
                alert('<?php echo esc_js( __( 'Connection error.', 'ska-no-code-design' ) ); ?>');
            } finally {
                this.isLoading = false;
            }
        },

        async deleteOrganism(id) {
            if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete this Symbol?', 'ska-no-code-design' ) ); ?>')) {
                try {
                    const response = await fetch(`${this.apiUrl}/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': this.apiNonce }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        this.organisms = this.organisms.filter(t => t.id !== id);
                    } else {
                        alert(result.message || '<?php echo esc_js( __( 'Symbol cannot be deleted.', 'ska-no-code-design' ) ); ?>');
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

        // ── Rename Category ──────────────────────────────
        startRenameCategory(cat) {
            this.editingCategory = cat;
            this.editingCategoryName = cat;
            this.$nextTick(() => {
                const input = this.$refs.renameInput;
                if (input) { input.focus(); input.select(); }
            });
        },

        cancelRenameCategory() {
            this.editingCategory = null;
            this.editingCategoryName = '';
        },

        async confirmRenameCategory(oldName) {
            const newName = this.editingCategoryName.trim();
            if (!newName || newName === oldName) {
                this.cancelRenameCategory();
                return;
            }
            if (this.categories.includes(newName)) {
                alert('<?php echo esc_js( __( 'Category already exists.', 'ska-no-code-design' ) ); ?>');
                return;
            }

            // Đổi tên trong mảng categories
            const idx = this.categories.indexOf(oldName);
            if (idx > -1) this.categories[idx] = newName;

            // Cập nhật tên category trên các organisms thuộc danh mục cũ
            const affectedOrgs = this.organisms.filter(o => o.category === oldName);
            affectedOrgs.forEach(org => { org.category = newName; });

            // Cập nhật active filter nếu đang xem danh mục này
            if (this.activeCategory === oldName) {
                this.activeCategory = newName;
            }

            this.cancelRenameCategory();

            // Lưu danh sách categories
            await this.saveCategoriesList();

            // Cập nhật category cho từng organism ở backend
            for (const org of affectedOrgs) {
                await this.updateSymbolCategory(org.id, newName);
            }
        },

        // ── Drag & Drop Reorder ──────────────────────────
        dragStart(index, event) {
            this.isDragging = true;
            this.dragIndex = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', index);
        },

        dragOver(index, event) {
            if (this.dragIndex === null || this.dragIndex === index) {
                this.dragOverIndex = null;
                return;
            }
            this.dragOverIndex = index;
            event.dataTransfer.dropEffect = 'move';
        },

        dragEnd() {
            this.isDragging = false;
            this.dragIndex = null;
            this.dragOverIndex = null;
        },

        dropCategory(toIndex) {
            const fromIndex = this.dragIndex;
            if (fromIndex === null || fromIndex === toIndex) {
                this.dragEnd();
                return;
            }
            // Swap phần tử trong mảng
            const item = this.categories.splice(fromIndex, 1)[0];
            this.categories.splice(toIndex, 0, item);
            this.dragEnd();

            // Lưu thứ tự mới lên server
            this.saveCategoriesList();
        },

        get filteredOrganisms() {
            if (this.activeCategory === 'all') {
                return this.organisms;
            }
            if (this.activeCategory === 'uncategorized') {
                return this.organisms.filter(o => !o.category);
            }
            return this.organisms.filter(o => o.category === this.activeCategory);
        }
    }));
});
</script>
