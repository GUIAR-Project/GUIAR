(function () {
    'use strict';

    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggleSidebar');
    const closeButton = document.getElementById('closeSidebar');
    const overlay = document.getElementById('overlay');

    if (!sidebar || !toggleButton || !overlay) return;

    function setSidebarOpen(isOpen) {
        sidebar.classList.toggle('mobile-open', isOpen);
        overlay.classList.toggle('active', isOpen);
        toggleButton.setAttribute('aria-expanded', String(isOpen));
    }

    toggleButton.addEventListener('click', function () {
        setSidebarOpen(!sidebar.classList.contains('mobile-open'));
    });

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            setSidebarOpen(false);
        });
    }

    overlay.addEventListener('click', function () {
        setSidebarOpen(false);
    });

    sidebar.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
                setSidebarOpen(false);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });
})();
