// main.js — Global JavaScript for Ashesi Lost & Found

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Auto-dismiss flash/alert messages after 4 seconds ──
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });

    // ── 2. Image upload preview ──
    // Used on report.php — shows a thumbnail before form submit
    const imageInput = document.getElementById('item_image');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── 3. Notification bell dropdown toggle ──
    const bell = document.getElementById('notif-bell');
    const notifDropdown = document.getElementById('notif-dropdown');
    if (bell && notifDropdown) {
        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('open');
        });
        // Close dropdown when clicking anywhere else
        document.addEventListener('click', function () {
            notifDropdown.classList.remove('open');
        });
    }

    // ── 4. Lost/Found toggle on report form ──
    const toggleBtns = document.querySelectorAll('.type-toggle-btn');
    const typeInput = document.getElementById('item_type');
    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (typeInput) typeInput.value = this.dataset.type;
        });
    });

    // ── 5. Confirm before deleting (admin) ──
    const deleteForms = document.querySelectorAll('.delete-confirm-form');
    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

});
