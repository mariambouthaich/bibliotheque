<?php if (isset($_GET['error']) && $_GET['error'] === 'max_limit_or_stock'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; background-color: #f8d7da; color: #842029;">
        <i class="bi bi-x-circle-fill fs-4 me-3 text-danger"></i>
        <div>
            <strong class="d-block mb-1">Enregistrement impossible ! 🛑</strong>
            <span>Cet étudiant a atteint la limite maximale (3 emprunts en cours), ou le livre sélectionné est épuisé.</span>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'max_limit'): ?>
    <div class="alert alert-danger d-flex align-items-center fw-bold mb-4" role="alert">
        <span class="me-2">⚠️</span>
        Cet emprunteur a déjà 3 livres en sa possession ! Impossible d'ajouter un nouveau prêt tant qu'il n'en a pas rendu un.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'missing_fields'): ?>
    <div class="alert alert-warning fw-bold mb-4" role="alert">
        ⚠️ Veuillez remplir tous les champs obligatoires (Nom, Livre et Date de retour).
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/index.php?page=emprunts&action=save" method="POST" id="addEmpruntForm">
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-bold small">Nom de l'Emprunteur</label>
            <input type="text" name="nom_emprunteur" class="form-control" placeholder="Ex: Adam El Bouchtaoui" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold small">Livre à prêter</label>
            <input type="text" list="livres-list" id="livre_input" class="form-control" placeholder="Taper le titre du livre..." required autocomplete="off">
            <input type="hidden" name="livre_id" id="livre_id_hidden">

            <datalist id="livres-list">
                <?php if (!empty($allBooks)): ?>
                    <?php foreach ($allBooks as $book): 
                        $titre = is_object($book) ? ($book->titre ?? '') : ($book['titre'] ?? '');
                        if (!empty($titre)):
                    ?>
                        <option value="<?= htmlspecialchars(trim($titre)) ?>"></option>
                    <?php endif; endforeach; ?>
                <?php endif; ?>
            </datalist>
        </div>

        <div class="col-md-12">
            <label class="form-label fw-bold small">Catégorie du livre</label>
            <input type="hidden" name="categorie_id" id="cat_hidden">
            <select id="cat_select" class="form-select" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($allCategories as $cat): 
                    $cat_id = is_object($cat) ? ($cat->id ?? null) : ($cat['id'] ?? null);
                    $cat_nom = is_object($cat) ? ($cat->nom ?? null) : ($cat['nom'] ?? null);
                    if ($cat_id && $cat_nom):
                ?>
                    <option value="<?= $cat_id ?>"><?= htmlspecialchars($cat_nom) ?></option>
                <?php 
                    endif;
                endforeach; ?>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold small">Date de début</label>
            <input type="date" name="date_emprunt" id="date_debut" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold small">Date de retour prévue</label>
            <input type="date" name="date_retour_prevue" id="date_fin" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold small">Durée (Jours)</label>
            <div class="input-group">
                <input type="number" name="duree_jours" id="duree_input" class="form-control fw-bold text-primary" value="0" min="1" required>
                <span class="input-group-text">jours</span>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold small">Statut initial</label>
            <select name="statut" class="form-select">
                <option value="en_cours" selected>En cours</option>
                <option value="rendu">Rendu</option>
            </select>
        </div>

        <div class="col-12 mt-4">
            <button type="submit" id="btn-submit" class="btn btn-primary px-5 py-2 shadow-sm w-100 w-md-auto">
                Enregistrer le prêt
            </button>
        </div>
    </div>
</form>

<script>
const booksData = {
<?php if (!empty($allBooks)): ?>
    <?php foreach ($allBooks as $book): 
        $id = is_object($book) ? ($book->id ?? '') : ($book['id'] ?? '');
        $titre = is_object($book) ? ($book->titre ?? '') : ($book['titre'] ?? '');
        $cat = is_object($book) ? ($book->categorie_id ?? '') : ($book['categorie_id'] ?? '');
        if (!empty($titre)):
    ?>
        "<?= addslashes(strtolower(trim($titre))) ?>": {
            id: "<?= $id ?>",
            categorie: "<?= trim(strtolower($cat)) ?>"
        },
    <?php endif; endforeach; ?>
<?php endif; ?>
};
console.log("Données de livres chargées en JS :", booksData);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const livreInput = document.getElementById('livre_input');
    const hiddenLivreId = document.getElementById('livre_id_hidden');
    const catSelect = document.getElementById('cat_select');
    const catHidden = document.getElementById('cat_hidden');
    
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const dureeInput = document.getElementById('duree_input');

    function updateHiddenCategory() {
        catHidden.value = catSelect.value;
    }
    catSelect.addEventListener('change', updateHiddenCategory);

    function verifierLivre() {
        const valeurSaisie = livreInput.value.trim().toLowerCase();

        if (booksData[valeurSaisie]) {
            const infoLivre = booksData[valeurSaisie];
            hiddenLivreId.value = infoLivre.id;

            let optionTrouvee = false;
            
            for (let option of catSelect.options) {
                if (option.value === infoLivre.categorie || option.text.toLowerCase() === infoLivre.categorie) {
                    catSelect.value = option.value;
                    catHidden.value = option.value;
                    optionTrouvee = true;
                    break;
                }
            }

            if (optionTrouvee) {
                catSelect.disabled = true; 
            } else {
                catSelect.disabled = false;
            }
        } else {
            hiddenLivreId.value = '';
            catSelect.value = '';
            catHidden.value = '';
            catSelect.disabled = false;
        }
    }

    livreInput.addEventListener('input', verifierLivre);
    livreInput.addEventListener('change', verifierLivre);
    livreInput.addEventListener('blur', verifierLivre);

    document.getElementById('addEmpruntForm').addEventListener('submit', function() {
        catSelect.disabled = false;
    });

    function calculerDuree() {
        if (dateDebut.value && dateFin.value) {
            const d1 = new Date(dateDebut.value);
            const d2 = new Date(dateFin.value);
            d1.setHours(0,0,0,0);
            d2.setHours(0,0,0,0);

            const diffTime = d2 - d1;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 90) {
                alert("La durée maximale autorisée est 90 jours.");
                dateFin.value = '';
                dureeInput.value = 0;
                return;
            }
            dureeInput.value = diffDays >= 0 ? diffDays : 0;
        }
    }

    function calculerDateFin() {
        const jours = parseInt(dureeInput.value);
        if (jours > 90) {
            alert("Impossible de dépasser 90 jours.");
            dureeInput.value = 90;
            return;
        }

        if (dateDebut.value && !isNaN(jours) && jours > 0) {
            const d = new Date(dateDebut.value);
            d.setDate(d.getDate() + jours);

            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');

            dateFin.value = `${year}-${month}-${day}`;
        }
    }

    dateDebut.addEventListener('change', function() {
        if (dureeInput.value > 0) {
            calculerDateFin();
        } else {
            calculerDuree();
        }
    });
    
    dateFin.addEventListener('change', calculerDuree);
    dureeInput.addEventListener('input', function() {
        if (this.value !== "") calculerDateFin();
    });
    dureeInput.addEventListener('change', calculerDateFin);
});
</script>