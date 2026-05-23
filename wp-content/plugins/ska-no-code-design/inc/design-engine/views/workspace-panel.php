<div class="wrap ska-workspace-panel bg-slate-50 min-h-screen -ml-5 -mt-2 p-8" x-data="workspaceData()">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 m-0 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-pink-500">category</span>
                    Design Workspace
                </h1>
                <p class="text-slate-500 mt-2"><?php esc_html_e( 'Reusable Component Manager (Organisms / Symbols) for the Ska ecosystem.', 'ska-no-code-design' ); ?></p>
            </div>
            <div>
                <button @click="openCreateModal()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all flex items-center gap-2 border-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Tạo Symbol Mới
                </button>
            </div>
        </div>

        <!-- Template List (Grid) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="organism in organisms" :key="organism.id">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-pink-300 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-pink-100 text-pink-700">
                                Symbol
                            </span>
                            
                            <!-- Context Menu -->
                            <div class="relative" x-data="{ openMenu: false }">
                                <button @click="openMenu = !openMenu" @click.outside="openMenu = false" class="text-slate-400 hover:text-slate-700 bg-transparent border-0 cursor-pointer p-1 rounded-lg hover:bg-slate-50">
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                                <div x-show="openMenu" x-transition.opacity class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-10 py-1">
                                    <button @click="deleteOrganism(organism.id)" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 border-0 bg-transparent cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        Xóa Symbol
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-800 m-0 mb-2" x-text="organism.name"></h3>
                        <p class="text-sm text-slate-500 m-0">Atomic Component</p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                            Cập nhật: <span x-text="organism.updated_at"></span>
                        </span>
                        <a :href="getEditorUrl(organism.id)" class="text-pink-600 hover:text-pink-800 font-bold text-sm flex items-center gap-1 no-underline">
                            <span class="material-symbols-outlined text-[18px]">design_services</span>
                            Mở Editor
                        </a>
                    </div>
                </div>
            </template>
            
            <!-- Empty State -->
            <div x-show="organisms.length === 0" class="col-span-full py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">extension</span>
                <p class="text-slate-500 font-medium"><?php esc_html_e( 'There are no Components / Symbols yet.', 'ska-no-code-design' ); ?></p>
                <button @click="openCreateModal()" class="mt-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-lg shadow-sm transition-all border-0 cursor-pointer">
                    Tạo Symbol đầu tiên
                </button>
            </div>
        </div>

        <!-- Create Modal -->
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
                        <input type="text" x-model="currentName" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all" placeholder=__( 'Example: Primary button', 'ska-no-code-design' )>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
                    <button @click="closeModal()" class="px-5 py-2 rounded-xl text-slate-600 font-bold hover:bg-slate-200 bg-slate-100 border-0 cursor-pointer transition-all">
                        Hủy
                    </button>
                    <button @click="saveOrganism()" class="px-5 py-2 rounded-xl text-white font-bold bg-pink-600 hover:bg-pink-700 shadow-sm border-0 cursor-pointer transition-all flex items-center gap-2">
                        <span x-show="isLoading" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                        <span x-text=__( '\'Create New\'', 'ska-no-code-design' )></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('workspaceData', () => ({
        organisms: [],
        
        isModalOpen: false,
        isLoading: false,
        currentName: '',

        apiUrl: '<?php echo esc_url( rest_url( 'ska-design/v1/organisms' ) ); ?>',
        apiNonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',

        init() {
            this.loadOrganisms();
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

        openCreateModal() {
            this.currentName = '';
            this.isModalOpen = true;
        },

        async saveOrganism() {
            if (!this.currentName.trim()) {
                alert(__( 'Please enter a Symbol name.', 'ska-no-code-design' ));
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
                        block_json: {} // Empty object for new organism
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    await this.loadOrganisms();
                    this.closeModal();
                } else {
                    alert(result.message || __( 'An error occurred while creating Symbol.', 'ska-no-code-design' ));
                }
            } catch (error) {
                console.error(error);
                alert(__( 'Connection error.', 'ska-no-code-design' ));
            } finally {
                this.isLoading = false;
            }
        },

        async deleteOrganism(id) {
            if (confirm(__( 'Are you sure you want to delete this Symbol? ', 'ska-no-code-design' ))) {
                try {
                    const response = await fetch(`${this.apiUrl}/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-WP-Nonce': this.apiNonce }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        this.organisms = this.organisms.filter(t => t.id !== id);
                    } else {
                        alert(result.message || __( 'Symbol cannot be deleted.', 'ska-no-code-design' ));
                    }
                } catch (error) {
                    console.error(error);
                    alert(__( 'Connection error.', 'ska-no-code-design' ));
                }
            }
        },

        closeModal() {
            this.isModalOpen = false;
        }
    }));
});
</script>
