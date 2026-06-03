document.addEventListener('DOMContentLoaded', () => {
    console.log("Système de gestion initialisé");

    // 1. Gestion du menu actif (Sidebar)
    const currentUrl = new URLSearchParams(window.location.search);
    const page = currentUrl.get('page') || 'dashboard';
    
    const navLinks = document.querySelectorAll('.sidebar-link');
    navLinks.forEach(link => {
        if (link.getAttribute('href').includes(`page=${page}`)) {
            link.classList.add('active');
        }
    });

    // 2. Gestion des boutons de suppression (Confirmation)
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-danger') || e.target.closest('.btn-danger')) {
            if (!confirm("Voulez-vous vraiment supprimer cet élément ?")) {
                e.preventDefault();
            }
        }
    });
});