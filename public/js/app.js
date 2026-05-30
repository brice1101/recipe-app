/* app.js — Recipe Book */

// ── Image upload preview ──────────────────────────────────────────────────────
const imageInput   = document.getElementById('image');
const imagePreview = document.getElementById('image-preview');

if (imageInput && imagePreview) {
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}

// ── Toggle cooked (AJAX) ──────────────────────────────────────────────────────
const cookedBtn = document.getElementById('cooked-btn');

if (cookedBtn) {
    cookedBtn.addEventListener('click', async () => {
        const id = cookedBtn.dataset.id;

        try {
            const res  = await fetch(`/recipes/${id}/toggle-cooked`, { method: 'POST' });
            const data = await res.json();

            const isCooked = data.cooked;
            cookedBtn.dataset.cooked = isCooked ? '1' : '0';

            // Swap button style and label
            cookedBtn.textContent = isCooked ? '✓ Cooked' : 'Mark as Cooked';
            cookedBtn.classList.toggle('btn--cooked',    isCooked);
            cookedBtn.classList.toggle('is-cooked',      isCooked);
            cookedBtn.classList.toggle('btn--secondary', !isCooked);

            // Also update the sidebar status text if present
            const statusEl = document.getElementById('cooked-status');
            if (statusEl) {
                statusEl.textContent = isCooked ? '✓ Cooked' : 'Not yet cooked';
                statusEl.style.color = isCooked ? 'var(--sage)' : 'var(--text-muted)';
            }
        } catch (err) {
            console.error('Toggle cooked failed:', err);
        }
    });
}
