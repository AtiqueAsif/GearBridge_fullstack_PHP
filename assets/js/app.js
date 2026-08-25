document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            const open = mainNav.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    const dashboardToggle = document.querySelector('.dashboard-menu-toggle');
    const dashboardSidebar = document.querySelector('.dashboard-sidebar');
    const dashboardShell = document.querySelector('.dashboard-shell');
    const dashboardOverlay = document.querySelector('.dashboard-overlay');

    const closeSidebar = () => {
        if (!dashboardSidebar || !dashboardShell) return;
        dashboardSidebar.classList.remove('is-open');
        dashboardShell.classList.remove('sidebar-open');
        if (dashboardToggle) dashboardToggle.setAttribute('aria-expanded', 'false');
    };

    if (dashboardToggle && dashboardSidebar && dashboardShell) {
        dashboardToggle.addEventListener('click', () => {
            const open = dashboardSidebar.classList.toggle('is-open');
            dashboardShell.classList.toggle('sidebar-open', open);
            dashboardToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    if (dashboardOverlay) {
        dashboardOverlay.addEventListener('click', closeSidebar);
    }

    document.querySelectorAll('.confirm-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            const root = input.closest('form');
            const wrap = root && root.querySelector('[data-image-preview-wrap]');
            const preview = root && root.querySelector('[data-image-preview]');

            if (!wrap || !preview) return;

            if (!file) {
                wrap.hidden = true;
                preview.removeAttribute('src');
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            wrap.hidden = false;
            preview.onload = () => URL.revokeObjectURL(objectUrl);
        });
    });

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select, button');
        if (firstInput) setTimeout(() => firstInput.focus(), 0);
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    };

    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.modalOpen);
            openModal(modal);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.closest('.modal')));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const modal = document.querySelector('.modal.is-open');
            if (modal) closeModal(modal);
            closeSidebar();
        }
    });

    const borrowFrom = document.querySelector('#borrow_from');
    const borrowUntil = document.querySelector('#borrow_until');
    if (borrowFrom && borrowUntil) {
        borrowFrom.addEventListener('change', () => {
            if (borrowFrom.value) {
                borrowUntil.min = borrowFrom.value;
                if (borrowUntil.value && borrowUntil.value < borrowFrom.value) {
                    borrowUntil.value = borrowFrom.value;
                }
            }
        });
    }
});
