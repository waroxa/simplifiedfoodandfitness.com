document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('sff-menu-toggle');
    var menu = document.getElementById('sff-offcanvas-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('sff-menu-open');
        });
    }
});
