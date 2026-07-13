import { __ } from '@wordpress/i18n';
/**
 * Skaaa AI Blueprint Generator
 */

function skaaaOpenAiModal() {
    document.getElementById('aiModal').classList.add('active');
    document.getElementById('aiPrompt').focus();
}

function skaaaCloseAiModal() {
    document.getElementById('aiModal').classList.remove('active');
    setTimeout(() => {
        document.getElementById('aiLoading').classList.add('hidden');
        document.getElementById('aiLoading').style.display = 'none';
        
        document.getElementById('aiResultArea').classList.add('hidden');
        document.getElementById('aiError').classList.add('hidden');
        document.getElementById('aiPrompt').disabled = false;
        document.getElementById('aiSubmitBtn').disabled = false;
        document.getElementById('aiSubmitBtn').innerHTML = __( '<span class=\"material-symbols-outlined text-[18px] mr-1\">magic_button</span> Proceed to create ✨', 'skaaa-no-code-design' );
    }, 300);
}

function skaaaCopyBlueprint() {
    const content = document.getElementById('aiJsonOutput').innerText;
    navigator.clipboard.writeText(content).then(() => {
        const btn = document.querySelector('button[onclick="skaaaCopyBlueprint()"]');
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

function skaaaGenerateBlueprint() {
    const promptText = document.getElementById('aiPrompt').value.trim();
    if (!promptText) return;

    document.getElementById('aiResultArea').classList.add('hidden');
    document.getElementById('aiError').classList.add('hidden');
    
    const loadingEl = document.getElementById('aiLoading');
    loadingEl.classList.remove('hidden');
    loadingEl.style.display = 'flex';
    
    document.getElementById('aiPrompt').disabled = true;
    document.getElementById('aiSubmitBtn').disabled = true;

    // Call WordPress AJAX Action `skaaa_generate_blueprint`
    wp.ajax.post('skaaa_generate_blueprint', {
        nonce: skaaaSystemObj.nonce,
        prompt: promptText
    }).done(function(response) {
        document.getElementById('aiJsonOutput').textContent = JSON.stringify(response.blueprint, null, 2);
        
        loadingEl.classList.add('hidden');
        loadingEl.style.display = 'none';
        
        document.getElementById('aiResultArea').classList.remove('hidden');
        document.getElementById('aiSubmitBtn').innerHTML = __( '<span class=\"material-symbols-outlined text-[18px] mr-1\">refresh</span> Regenerate ✨', 'skaaa-no-code-design' );
    }).fail(function(error) {
        console.error("Gemini API Error:", error);
        loadingEl.classList.add('hidden');
        loadingEl.style.display = 'none';
        
        document.getElementById('aiError').classList.remove('hidden');
        const errorMsg = error && error.message ? error.message : __( 'Unable to connect to Skaaa AI Overseer server.', 'skaaa-no-code-design' );
        document.getElementById('aiErrorMessage').innerText = errorMsg;
        
        document.getElementById('aiSubmitBtn').innerHTML = __( '<span class=\"material-symbols-outlined text-[18px] mr-1\">magic_button</span> Try again ✨', 'skaaa-no-code-design' );
    }).always(function() {
        document.getElementById('aiPrompt').disabled = false;
        document.getElementById('aiSubmitBtn').disabled = false;
    });
}
