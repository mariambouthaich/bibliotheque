document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialisation des Charts (Graphiques)
    // Had l-partie kat-khdem b-l-data li jaya mn PHP f-index.php
    const ctxMonthly = document.getElementById('monthlyChart');
    const ctxCategory = document.getElementById('categoryChart');

    if (ctxMonthly && typeof chartMonths !== 'undefined') {
        new Chart(ctxMonthly.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartMonths,
                datasets: [{
                    label: 'Livres ajoutés',
                    data: chartMonthlyNb,
                    backgroundColor: '#4e73df',
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    if (ctxCategory && typeof chartCategories !== 'undefined') {
        new Chart(ctxCategory.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartCategories,
                datasets: [{
                    data: chartNbLivres,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    /**
     * 2. Gestion de la liste des livres
     * REMARQUE : On a supprimé l'appel à loadRecentBooks() pour éviter 
     * le clignotement et garder l'affichage propre du PHP (Image 1).
     */
    
    // Ila bghiti t-rechargi l-data bla ma t-refreshi l-page kamla moustaqbalan:
    function loadRecentBooks() {
        const tableBody = document.getElementById('recent-books-table');
        if (!tableBody) return;

        fetch('api/books.php?action=list&per_page=5')
            .then(response => response.json())
            .then(res => {
                if (res.success && res.data && res.data.data) {
                    tableBody.innerHTML = ''; // Nqi l-tableau
                    res.data.data.forEach((book, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><span class="row-number">${index + 1}</span></td>
                            <td><span class="fw-600">${book.titre}</span></td>
                            <td>${book.auteur}</td>
                            <td><span class="badge bg-light text-dark border">${book.categorie_nom || 'N/A'}</span></td>
                            <td>${book.quantite} ex.</td>
                            <td class="text-muted">${new Date(book.created_at).toLocaleDateString()}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(err => console.error('Erreur API Dashboard:', err));
    }

    // Li'annahu PHP déjà kiy-affichi l-ktoub f-l-lowel, 
    // khallina had l-ligne m-commenti bch l-page t-bqa stable o nqiya.
    // loadRecentBooks(); 
});