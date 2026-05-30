import './bootstrap';
import '../css/app.css';
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()

document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const openBtn = document.getElementById("openSidebarBtn");
    const closeBtn = document.getElementById("closeSidebarBtn");

    if (!sidebar || !overlay || !openBtn || !closeBtn) return;

    let longPressTimer = null;
    const longPressDuration = 500;
    let miniMode = false;
    let firstOpen = true;
    let isOpen = false;

    function openSidebar() {
        sidebar.classList.remove("translate-x-full");
        overlay.classList.remove("hidden");

        openBtn.classList.add("hidden");
        closeBtn.classList.remove("hidden");

        isOpen = true;
    }

    function closeSidebar() {
        sidebar.classList.add("translate-x-full");
        overlay.classList.add("hidden");

        closeBtn.classList.add("hidden");
        openBtn.classList.remove("hidden");

        isOpen = false;
    }

    function toggleMiniMode() {
        miniMode = !miniMode;
        sidebar.classList.toggle("w-20", miniMode);
        sidebar.classList.toggle("w-64", !miniMode);
    }

    openBtn.addEventListener("mousedown", () => {
        longPressTimer = setTimeout(() => {
            if (!firstOpen) toggleMiniMode();
        }, longPressDuration);
    });

    openBtn.addEventListener("mouseup", () => {
        clearTimeout(longPressTimer);

        if (!isOpen) {
            openSidebar();
            firstOpen = false;
        }
    });

    closeBtn.addEventListener("click", closeSidebar);
    overlay.addEventListener("click", closeSidebar);
});
