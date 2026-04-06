document.addEventListener('DOMContentLoaded', () => {
    // Lắng nghe mọi sự kiện Submit Form vương vãi trên web
    document.body.addEventListener('submit', async (e) => {
        // Áp dụng Vô Vi: Nếu không phải Form của hệ sinh thái Ska thì tha cho nó
        const form = e.target.closest('form.ska-form-block');
        if (!form) return;

        // Bắt sống tín hiệu! Không cho Form nhảy trang
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]') || form.querySelector('a.wp-block-button__link');
        let originalText = '';
        if (submitBtn) {
            originalText = submitBtn.textContent || submitBtn.value;
            submitBtn.textContent = 'Đang xử lý...';
            submitBtn.style.opacity = '0.5';
            submitBtn.style.pointerEvents = 'none';
        }

        // Gom nhặt dữ liệu Input
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        // Gắn Tag Form_id dựa vào actionName thiết lập ở Block Editor
        data['ska_form_id'] = form.getAttribute('data-ska-action') || 'default_form_submit';

        try {
            // NÉM THẲNG VÀO TRONG "LỚP NHÂN" CỦA THE TRINITY
            const res = await fetch(skaLogicEnv.rest_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            
            if (result.success) {
                alert('✅ Vượt Ải Thành Công! Payload đã được đẩy sâu vào Data Pro Flat Tables!');
                form.reset(); // Dọn dẹp form
            } else {
                alert('❌ Lỗi Logic Engine: ' + (result.message || 'Unknown'));
            }
        } catch (err) {
            console.error(err);
            alert('💥 Lỗi Mất Nối Tới Logic Engine!');
        } finally {
            // Trả lại nguyên trạng nút Bấm
            if (submitBtn) {
                submitBtn.textContent = originalText;
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto';
            }
        }
    });
});
