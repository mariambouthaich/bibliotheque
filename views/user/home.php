<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">Catalogue des Livres 📚</h2>
            <p class="text-muted small mb-0">Parcourez la collection et effectuez vos emprunts en un clic.</p>
        </div>
        <div class="text-end bg-white p-3 rounded shadow-sm border" style="border-radius: 12px !important;">
            <span class="d-block text-muted small">Bienvenue,</span>
            <strong class="text-primary"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'User') ?></strong>
        </div>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'max_loans_reached'): ?>
        <div class="mx-3 mb-4 alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" style="border-radius: 12px; background-color: #fff3cd; color: #664d03;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
            <div>
                <strong class="d-block mb-1">Limite atteinte ! 🛑</strong>
                <span>Vous avez déjà 3 emprunts en cours. Vous devez retourner un livre avant de pouvoir en demander un autre.</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mx-3" style="border-radius: 15px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase text-muted small fw-bold" style="letter-spacing: 0.5px;">Livre</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold" style="letter-spacing: 0.5px;">Auteur</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold" style="letter-spacing: 0.5px;">Catégorie</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold text-center" style="letter-spacing: 0.5px;">Quantité</th>
                            <th class="pe-4 py-3 text-uppercase text-muted small fw-bold text-end" style="letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span class="fw-bold d-block text-dark"><?= htmlspecialchars($book['titre'] ?? 'Sans titre') ?></span>
                                                <span class="text-muted mb-0 small">#<?= $book['id'] ?? '0' ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3">
                                        <span class="text-secondary fw-medium"><?= htmlspecialchars($book['auteur'] ?? 'Inconnu') ?></span>
                                    </td>

                                    <td class="py-3">
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 border border-info border-opacity-10" style="border-radius: 8px;">
                                            <?= htmlspecialchars($book['categorie_nom'] ?? 'Général') ?>
                                        </span>
                                    </td>

                                    <td class="py-3 text-center">
                                        <?php $qte = (int)($book['quantite'] ?? 0); ?>
                                        <?php if ($qte > 0): ?>
                                            <div class="d-inline-flex align-items-center bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">
                                                <span class="small fw-bold"><?= $qte ?> en stock</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-inline-flex align-items-center bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill">
                                                <span class="small fw-bold">Épuisé</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="pe-4 py-3 text-end">
                                        <?php if ($qte > 0): ?>
                                            <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm transition-all hover-lift"
                                                    onclick="ouvrirFormulaireEmprunt(<?= $book['id'] ?>, '<?= htmlspecialchars($book['titre'], ENT_QUOTES) ?>')">
                                                <i class="bi bi-plus-circle me-1"></i> Emprunter
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm px-4 rounded-pill opacity-50 border-0" disabled>
                                                <i class="bi bi-x-circle me-1"></i> Plus de stock
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-journal-x display-4 text-muted opacity-25"></i>
                                        <p class="mt-3 text-muted">Aucun livre n'est disponible dans le catalogue pour le moment.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userEmpruntModal" tabindex="-1" aria-labelledby="userEmpruntModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 12px;">
      <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <h5 class="modal-title" id="userEmpruntModalLabel"><i class="bi bi-bookmark-plus me-2"></i>Demande d'emprunt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="index.php?page=demander-emprunt" method="POST">
        <div class="modal-body p-4">
            <div class="mb-3 bg-light p-3 rounded border">
                <small class="text-muted d-block">Livre sélectionné :</small>
                <strong id="livreSelectionne" class="text-dark fs-5"></strong>
            </div>
            
            <input type="hidden" name="livre_id" id="hiddenLivreId">

            <div class="mb-3">
                <label for="duree" class="form-label fw-bold text-secondary">Durée de l'emprunt :</label>
                <select name="duree" id="duree" class="form-select shadow-sm" required>
                    <option value="7">7 Jours (1 Semaine)</option>
                    <option value="14" selected>14 Jours (2 Semaines)</option>
                    <option value="21">21 Jours (3 Semaines)</option>
                    <option value="30">30 Jours (1 Mois)</option>
                    <option value="35">35 Jours (5 Semaines)</option>
                    <option value="42">42 Jours (6 Semaines)</option>
                    <option value="49">49 Jours (7 Semaines)</option>
                    <option value="56">56 Jours (8 Semaines)</option>
                    <option value="63">63 Jours (9 Semaines)</option>
                    <option value="70">70 Jours (10 Semaines)</option>
                    <option value="77">77 Jours (11 Semaines)</option>
                    <option value="84">84 Jours (12 Semaines)</option>
                    <option value="90">90 Jours (3 Mois)</option>
                </select>
            </div>
            <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i> Votre nom et la date d'emprunt sont générés automatiquement.</small>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary px-4 rounded-pill">Confirmer l'emprunt</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function ouvrirFormulaireEmprunt(id, titre) {
    document.getElementById('hiddenLivreId').value = id;
    document.getElementById('livreSelectionne').textContent = titre;
    
    var myModal = new bootstrap.Modal(document.getElementById('userEmpruntModal'));
    myModal.show();
}
</script>

<style>
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
.table thead th {
    border-top: none;
    background-color: #f8f9fa;
}
.table tbody tr:hover {
    background-color: #fcfdfe;
}
</style>