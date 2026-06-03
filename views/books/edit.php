<?php
// On récupère l'ID du livre depuis l'URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<?php include BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid pt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier le Livre #<?php echo $bookId; ?></h5>
                    <a href="index.php?page=books" class="btn btn-sm btn-light">Retour</a>
                </div>
                <div class="card-body">
                    <form id="editBookForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $bookId; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Titre</label>
                                <input type="text" name="titre" id="edit_titre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Auteur</label>
                                <input type="text" name="auteur" id="edit_auteur" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <select name="categorie_id" id="edit_categorie_id" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ISBN</label>
                                <input type="text" name="isbn" id="edit_isbn" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantite" id="edit_quantite" class="form-control" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nouvelle Image (optionnel)</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookId = <?php echo $bookId; ?>;

    // 1. Charger les infos actuelles du livre
    fetch(`index.php?page=api-books&action=get&id=${bookId}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const book = res.book;
                document.getElementById('edit_titre').value = book.titre;
                document.getElementById('edit_auteur').value = book.auteur;
                document.getElementById('edit_categorie_id').value = book.categorie_id;
                document.getElementById('edit_isbn').value = book.isbn;
                document.getElementById('edit_description').value = book.description;
                document.getElementById('edit_quantite').value = book.quantite;
            } else {
                alert("Erreur : " + res.message);
            }
        });

    // 2. Gérer la soumission du formulaire
    document.getElementById('editBookForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('index.php?page=api-books-update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                alert('Livre mis à jour !');
                window.location.href = 'index.php?page=books';
            } else {
                alert('Erreur : ' + (res.message || res.errors.join('\n')));
            }
        });
    });
});
</script>

<?php include BASE_PATH . '/views/layouts/footer.php'; ?>