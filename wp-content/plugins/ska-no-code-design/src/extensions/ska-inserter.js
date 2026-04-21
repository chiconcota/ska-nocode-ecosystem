import { registerBlockVariation } from '@wordpress/blocks';

/**
 * Tự động tìm nạp (Fetch) dữ liệu Organisms mượt mà từ JSON Cache,
 * Đăng ký chúng dưới dạng các Variation của khối ska-builder/organism-ref.
 * Như vậy, người dùng có thể nhặt ra từ dấu (+) của Gutenberg.
 */
function loadSkaSymbolsIntoInserter() {
    try {
        // Dữ liệu đã được nạp sẵn từ PHP qua wp_localize_script (skaOrganismsCache)
        const organismsCache = window.skaOrganismsCache || {};
        const { dispatch } = wp.data;
        
        if (organismsCache && typeof organismsCache === 'object') {
            const variations = [];
            Object.values(organismsCache).forEach((org) => {
                if (org && org.id && org.name) {
                    variations.push({
                        name: org.id,
                        title: org.name,
                        icon: 'superhero',
                        attributes: { organismId: org.id },
                        isActive: (blockAttributes, variationAttributes) => {
                            return blockAttributes.organismId === variationAttributes.organismId;
                        },
                        scope: ['inserter']
                    });
                }
            });

            if (variations.length > 0) {
                dispatch('core/blocks').addBlockVariations('ska-builder/organism-ref', variations);
            }
        }
    } catch (error) {
        console.error('Lỗi khi nạp Ska Symbols vào Inserter: ', error);
    }
}

// Kích hoạt nạp dữ liệu khi Editor khởi động
wp.domReady(() => {
    loadSkaSymbolsIntoInserter();
});
