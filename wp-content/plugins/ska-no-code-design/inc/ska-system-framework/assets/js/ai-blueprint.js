/**
 * Ska AI Blueprint Generator
 */

function skaOpenAiModal() {
    document.getElementById('aiModal').classList.add('active');
    document.getElementById('aiPrompt').focus();
}

function skaCloseAiModal() {
    document.getElementById('aiModal').classList.remove('active');
    setTimeout(() => {
        document.getElementById('aiLoading').classList.add('hidden');
        document.getElementById('aiLoading').style.display = 'none';
        
        document.getElementById('aiResultArea').classList.add('hidden');
        document.getElementById('aiError').classList.add('hidden');
        document.getElementById('aiPrompt').disabled = false;
        document.getElementById('aiSubmitBtn').disabled = false;
        document.getElementById('aiSubmitBtn').innerHTML = '<span class="material-symbols-outlined text-[18px] mr-1">magic_button</span> Tiến hành tạo ✨';
    }, 300);
}

function skaCopyBlueprint() {
    const content = document.getElementById('aiJsonOutput').innerText;
    navigator.clipboard.writeText(content).then(() => {
        const btn = document.querySelector('button[onclick="skaCopyBlueprint()"]');
        if (btn) {
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">check</span> Copied!';
            btn.classList.add('text-emerald-400');
            setTimeout(() => {
                btn.innerHTML = origHtml;
                btn.classList.remove('text-emerald-400');
            }, 2000);
        }
    });
}

function skaGenerateBlueprint() {
    const promptText = document.getElementById('aiPrompt').value.trim();
    if (!promptText) return;

    document.getElementById('aiResultArea').classList.add('hidden');
    document.getElementById('aiError').classList.add('hidden');
    
    const loadingEl = document.getElementById('aiLoading');
    loadingEl.classList.remove('hidden');
    loadingEl.style.display = 'flex';
    
    document.getElementById('aiPrompt').disabled = true;
    document.getElementById('aiSubmitBtn').disabled = true;

    // Call WordPress AJAX Action `ska_generate_blueprint`
    wp.ajax.post('ska_generate_blueprint', {
        nonce: skaSystemObj.nonce,
        prompt: promptText
    }).done(function(response) {
        document.getElementById('aiJsonOutput').textContent = JSON.stringify(response.blueprint, null, 2);
        
        loadingEl.classList.add('hidden');
        loadingEl.style.display = 'none';
        
        document.getElementById('aiResultArea').classList.remove('hidden');
        document.getElementById('aiSubmitBtn').innerHTML = '<span class="material-symbols-outlined text-[18px] mr-1">refresh</span> Tạo lại ✨';
    }).fail(function(error) {
        console.error("Gemini API Error:", error);
        loadingEl.classList.add('hidden');
        loadingEl.style.display = 'none';
        
        document.getElementById('aiError').classList.remove('hidden');
        const errorMsg = error && error.message ? error.message : "Không thể kết nối đến máy chủ Ska AI Overseer.";
        document.getElementById('aiErrorMessage').innerText = errorMsg;
        
        document.getElementById('aiSubmitBtn').innerHTML = '<span class="material-symbols-outlined text-[18px] mr-1">magic_button</span> Thử lại ✨';
    }).always(function() {
        document.getElementById('aiPrompt').disabled = false;
        document.getElementById('aiSubmitBtn').disabled = false;
    });
}
