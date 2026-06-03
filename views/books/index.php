<?php
defined('BASE_PATH') or die('Accès interdit');
require_once BASE_PATH . '/views/layouts/header.php';
?>

<div class="page-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Livres</h1>
            <p class="page-subtitle">Catalogue complet de la bibliothèque</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addBookModal" id="addBookBtn">
                <i class="bi bi-plus-lg me-2"></i> Nouveau livre
            </button>
        </div>
    </div>

    <div class="filter-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control search-input" placeholder="Rechercher...">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select id="categoryFilter" class="form-select custom-select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select id="perPageSelect" class="form-select custom-select">
                    <option value="10">10 par page</option>
                    <option value="25">25 par page</option>
                    <option value="50">50 par page</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button id="resetFilters" class="btn btn-outline-custom w-100">Réinitialiser</button>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <div>
                <h5 class="data-card-title">Liste des livres</h5>
                <p class="data-card-sub" id="resultsCount">Chargement...</p>
            </div>
        </div>
        <div class="data-card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table mb-0 w-100" id="booksTable">
                   <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Catégorie</th>
                            <th>Quantité</th>
                            <th width="120">Date ajout</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex justify-content-center align-items-center">
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

<div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #1a1d21; color: #fff; border: 1px solid #333;">
            <div class="modal-header" style="border-bottom: 1px solid #333;">
                <h5 class="modal-title"><i class="bi bi-book me-2"></i>Nouveau Livre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addBookForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Titre *</label>
                            <input type="text" name="titre" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Auteur *</label>
                            <input type="text" name="auteur" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Catégorie *</label>
                            <select name="categorie_id" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Quantité *</label>
                            <input type="number" name="quantite" class="form-control bg-dark text-white border-secondary" min="0" value="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary">Description (Optionnel)</label>
                            <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #333;">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #1a1d21; color: #fff; border: 1px solid #333;">
            <div class="modal-header" style="border-bottom: 1px solid #333;">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier le Livre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBookForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_book_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Titre *</label>
                            <input type="text" name="titre" id="edit_titre" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Auteur *</label>
                            <input type="text" name="auteur" id="edit_auteur" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Catégorie *</label>
                            <select name="categorie_id" id="edit_id_categorie" class="form-select bg-dark text-white border-secondary" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Quantité *</label>
                            <input type="number" name="quantite" id="edit_quantite" class="form-control bg-dark text-white border-secondary" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary">Description (Optionnel)</label>
                            <textarea name="description" id="edit_description" class="form-control bg-dark text-white border-secondary" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #333;">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="background-color: #1a1d21; color: #fff; border: 1px solid #333;">
            <div class="modal-body text-center p-4">
                <h6 class="mb-3">Confirmer la suppression ?</h6>
                <input type="hidden" id="deleteBookId">
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-light btn-sm px-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger btn-sm px-3" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/books.js?v=1.4"></script>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>