<?php
defined('BASE_PATH') or die('Accès interdit');
require_once BASE_PATH . '/views/layouts/header.php';
?>

<div class="page-content">

    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Gestion des Catégories 📂</h1>
            <p class="page-subtitle text-muted">Organisez votre catalogue</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" id="addCategoryBtn" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="bi bi-plus-lg me-2"></i> Nouvelle catégorie
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card data-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">Liste des catégories</h5>
                        <p class="small text-muted mb-0" id="catCount">Chargement...</p>
                    </div>
                    <div class="search-input-wrapper" style="max-width:300px;">
                        <input type="text" id="catSearch" class="form-control" placeholder="Rechercher une catégorie...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table custom-table mb-0 w-100" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Nom de la catégorie</th>
                                    <th class="text-center">Nombre de Livres</th>
                                    <th>Date de création</th>
                                    <th width="200" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex justify-content-center w-100">
                                            <div class="spinner-border text-primary" role="status"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="categoryModalLabel">Ajouter une catégorie</h5>
                <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom de la catégorie</label>
                        <input type="text" class="form-control" id="categoryNom" name="nom" placeholder="Ex: Roman, Science-Fiction..." required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">
                        <i class="bi bi-check-lg me-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content custom-modal">
            <div class="modal-body text-center py-4">
                <h5 class="fw-bold">Supprimer la catégorie ?</h5>
                <p class="text-muted small">Les livres associés ne seront pas supprimés.</p>
                <input type="hidden" id="deleteCatId">
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCatBtn">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allCategories = [];
    loadCategories();

    // 1. جلب البيانات من الـ API
    function loadCategories() {
        const tbody = document.getElementById('categoriesTableBody');
        const catCount = document.getElementById('catCount');

        fetch('index.php?page=api-categories')
            .then(response => response.json())
            .then(data => {
                allCategories = Array.isArray(data) ? data : (data.data || []);
                renderTable(allCategories);
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger fw-bold">⚠️ Erreur de chargement.</td></tr>`;
                catCount.textContent = "Erreur";
            });
    }

    // 2. رسم الجدول
    function renderTable(categories) {
        const tbody = document.getElementById('categoriesTableBody');
        const catCount = document.getElementById('catCount');
        tbody.innerHTML = '';
        
        catCount.textContent = `${categories.length} catégorie(s) au total`;

        if (categories.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Aucune catégorie trouvée.</td></tr>`;
            return;
        }

        categories.forEach(cat => {
            const tr = document.createElement('tr');
            const nbLivres = cat.nb_livres !== undefined ? cat.nb_livres : (cat.livres_count || 0);
            const dateCrea = cat.date_creation || cat.created_at || '---';

            tr.innerHTML = `
                <td class="fw-bold text-secondary">#${cat.id}</td>
                <td class="fw-semibold text-dark">${cat.nom}</td>
                <td class="text-center"><span class="badge bg-light text-dark px-3 py-2 border">${nbLivres} livres</span></td>
                <td class="text-muted">${dateCrea}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning me-1 btn-edit" data-id="${cat.id}" data-nom="${cat.nom}">
                        <i class="bi bi-pencil-square"></i> Modifier
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${cat.id}">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        initActions();
    }

    // 🚨 التعديل تدار هنا لداخل ف هاد الدالة للتحقق من الكتب
    function initActions() {
        // زر التعديل
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('categoryModalLabel').textContent = "Modifier la catégorie";
                document.getElementById('categoryId').value = this.dataset.id;
                document.getElementById('categoryNom').value = this.dataset.nom;
                new bootstrap.Modal(document.getElementById('categoryModal')).show();
            });
        });

        // زر الحذف الذكي والمعدل
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                
                // قلبو على الكاتيكوري ف اللائحة باش نعرفو شحال من كتاب فيها
                const currentCat = allCategories.find(c => c.id == id);
                const nbLivres = currentCat ? (currentCat.nb_livres !== undefined ? currentCat.nb_livres : (currentCat.livres_count || 0)) : 0;

                // 🛑 إيلا كان فيها كتر من 0 كتاب، طلع الـ Alert وحبس كلشي
                if (parseInt(nbLivres) > 0) {
                    alert(`Impossible de supprimer cette catégorie ! Elle contient encore ${nbLivres} livre(s). Vous devez d'abord supprimer ou changer la catégorie de ces livres.`);
                    return; // حبس هنا وماتخليش الـ Modal يتفتح
                }

                // إيلا كانت خاوية (0 كتب) دوز عادي للـ Modal ديال التأكيد
                document.getElementById('deleteCatId').value = id;
                new bootstrap.Modal(document.getElementById('deleteCatModal')).show();
            });
        });
    }

    // البحث
    document.getElementById('catSearch').addEventListener('input', function(e) {
        const value = e.target.value.toLowerCase().trim();
        const filtered = allCategories.filter(cat => cat.nom.toLowerCase().includes(value));
        renderTable(filtered);
    });

    // تأكيد الحذف
    document.getElementById('confirmDeleteCatBtn').addEventListener('click', function() {
        const id = document.getElementById('deleteCatId').value;
        
        fetch(`index.php?page=api-categories&action=delete&id=${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(res => {
                bootstrap.Modal.getInstance(document.getElementById('deleteCatModal')).hide();
                if (res.success || res.status === 'success') {
                    loadCategories();
                } else {
                    alert(res.message || "Erreur lors de la suppression.");
                }
            })
            .catch(() => alert('Erreur lors de la suppression.'));
    });

    // الإرسال والتحقق من التكرار وعرض الـ Alert
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('categoryId').value;
        const isEdit = id && id !== '';
        
        const url = isEdit ? 'index.php?page=api-categories&action=update' : 'index.php?page=api-categories';
        const formData = new FormData(this);

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success === true || res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                document.getElementById('categoryForm').reset();
                document.getElementById('categoryId').value = '';
                loadCategories();
            } else {
                alert(res.message || "Cette catégorie existe déjà.");
            }
        })
        .catch(() => {
            alert("Une erreur est survenue lors de l'enregistrement.");
        });
    });

    document.getElementById('addCategoryBtn').addEventListener('click', function() {
        document.getElementById('categoryModalLabel').textContent = "Ajouter une catégorie";
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
    });
});
</script>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>