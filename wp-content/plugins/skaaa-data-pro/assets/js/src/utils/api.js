import { __ } from '@wordpress/i18n';
// Lấy cấu hình từ biến toàn cục do PHP đẩy xuống
export const getConfig = () => window.skaaaDataConfig || {};

export const apiFetch = async (action, data = {}) => {
    const config = getConfig();
    if (!config.ajaxurl || !config.nonce) {
        throw new Error('Skaaa Data Config is missing.');
    }

    const formData = new URLSearchParams();
    formData.append('action', action);
    formData.append('security', config.nonce);
    
    // Nếu không có tableId ở data truyền vào, tự động lấy
    if (!data.table && config.tableId) {
        formData.append('table', config.tableId);
    }

    Object.entries(data).forEach(([key, value]) => {
        formData.append(key, value);
    });

    try {
        const response = await fetch(config.ajaxurl, { 
            method: 'POST', 
            body: formData 
        });
        const resJson = await response.json();
        return resJson;
    } catch (e) {
        console.error('API Error:', e);
        return { success: false, data: { message: __( 'Network error or server not responding.', 'skaaa-data-pro' ) } };
    }
};
