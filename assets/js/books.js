document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('booksTableBody');
    const resultsCount = document.getElementById('resultsCount');
    
    // تأكد من وجود العناصر قبل تعريف المودال لتجنب أخطاء Bootstrap
    const editModalEl = document.getElementById('editBookModal');
    const deleteModalEl = document.getElementById('deleteModal');
    const editBookModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;

    // 1. Load Books
    function loadBooks() {
        const search = document.getElementById('searchInput')?.value || '';
        const categorie = document.getElementById('categoryFilter')?.value || '';
        const perPage = document.getElementById('perPageSelect')?.value || '10';

        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>`;

        const url = `${BASE_URL}/index.php?page=api-books&search=${encodeURIComponent(search)}&categorie=${categorie}&per_page=${perPage}`;

        fetch(url)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const booksData = res.data.data !== undefined ? res.data.data : res.data;
                    const totalBooks = res.data.total !== undefined ? res.data.total : (booksData ? booksData.length : 0);
                    renderTable(booksData); 
                    if(resultsCount) resultsCount.innerText = `${totalBooks} livre(s) trouvé(s)`;
                } else {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Erreur: ' + res.message + '</td></tr>';
                }
            })
            .catch(err => {
                console.error("Erreur:", err);
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Erreur de connexion serveur.</td></tr>';
            });
    }

    // 2. Render Table
    function renderTable(books) {
        tableBody.innerHTML = ''; 
        if (!books || books.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Aucun livre trouvé.</td></tr>';
            return;
        }
        books.forEach((book, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td> 
                <td class="fw-600">${book.titre || ''}</td>
                <td>${book.auteur || ''}</td>
                <td><span class="badge bg-light text-dark border">${book.categorie_nom || 'N/A'}</span></td>
                <td>${book.quantite} ex.</td>
                <td class="text-muted small">${book.date_ajout || book.created_at || '-'}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info text-white" onclick="editBook(${book.id}, '${(book.titre||'').replace(/'/g, "\\'")}', '${(book.auteur||'').replace(/'/g, "\\'")}', ${book.id_categorie || book.categorie_id || 0}, ${book.quantite}, '${(book.description||'').replace(/'/g, "\\'")}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="prepareDelete(${book.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // 3. Global Functions
    window.editBook = function(id, titre, auteur, catId, qte, desc) {
        document.getElementById('edit_book_id').value = id;
        document.getElementById('edit_titre').value = titre;
        document.getElementById('edit_auteur').value = auteur;
        const cat = document.getElementById('edit_id_categorie') || document.getElementById('edit_categorie_id');
        if(cat) cat.value = catId;
        document.getElementById('edit_quantite').value = qte;
        document.getElementById('edit_description').value = desc;
        editBookModal?.show();
    };

    window.prepareDelete = function(id) {
        document.getElementById('deleteBookId').value = id; 
        deleteModal?.show();
    };

    // 4. Events
    document.getElementById('searchInput')?.addEventListener('input', loadBooks);
    document.getElementById('categoryFilter')?.addEventListener('change', loadBooks);
    document.getElementById('perPageSelect')?.addEventListener('change', loadBooks);

    // 5. Forms
    const addForm = document.getElementById('addBookForm');
    addForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${BASE_URL}/index.php?page=api-books`, { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => { if(data.success) { bootstrap.Modal.getInstance(document.getElementById('addBookModal'))?.hide(); loadBooks(); } });
    });

    const editForm = document.getElementById('editBookForm');
    editForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append('action', 'update');
        fetch('index.php?page=api-books-update', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => { if(data.success) { editBookModal?.hide(); loadBooks(); } else { alert(data.message); } });
    });

    loadBooks();
});