document.addEventListener('DOMContentLoaded', () => {

    // --- Lightbox ---
    let lb = document.getElementById('lightbox');
    if (!lb) {
        lb = document.createElement('div');
        lb.id = 'lightbox';
        const img = document.createElement('img');
        lb.appendChild(img);
        document.body.appendChild(lb);
    }

    const lbImg = lb.querySelector('img');
    const images = document.querySelectorAll('.research-img, .sidebar-book-raw');

    images.forEach(img => {
        img.addEventListener('click', () => {
            lbImg.src = img.src;
            lb.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    lb.addEventListener('click', () => {
        lb.style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    // Close lightbox on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lb.style.display === 'flex') {
            lb.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

});

// --- Contact Form Handler ---
function handleContactSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('.btn-submit');

    // Simple confirmation — replace with actual endpoint integration as needed
    btn.textContent = 'Inquiry Sent';
    btn.disabled = true;
    btn.style.background = '#555';

    const note = form.querySelector('.form-note');
    if (note) {
        note.textContent = 'Thank you. The Foundation will be in touch within five business days.';
        note.style.color = 'rgba(255,255,255,0.65)';
    }

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = true;
        el.style.opacity = '0.5';
    });
}
