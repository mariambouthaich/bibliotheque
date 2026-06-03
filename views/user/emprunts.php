<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>

<div class="page-content">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title d-flex align-items-center gap-2">
                Mes Emprunts Historique <span class="fs-4">🕒</span>
            </h1>
            <p class="text-muted small mb-0">La liste de tous vos livres empruntés</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0 w-100 bg-white">
                <thead class="bg-light text-uppercase fs-7 text-muted fw-bold border-bottom">
                    <tr>
                        <th class="ps-4 py-3">Livre</th>
                        <th class="py-3">Auteur</th>
                        <th class="py-3">Catégorie</th>
                        <th class="py-3">Date Emprunt</th>
                        <th class="py-3">Date Retour Prévue</th>
                        <th class="text-center pe-4 py-3">Statut / Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mesEmprunts)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Vous n'avez aucun emprunt pour le moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mesEmprunts as $emp): ?>
                            <tr class="border-bottom">
                                <td class="ps-4 fw-bold text-primary py-3">
                                    <?php echo htmlspecialchars($emp['livre_titre'] ?? 'Livre inconnu'); ?>
                                </td>
                                
                                <td class="text-secondary">
                                    <?php echo htmlspecialchars($emp['livre_auteur'] ?? 'Non spécifié'); ?>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-2 small fw-normal">
                                        <?php echo htmlspecialchars($emp['categorie_nom'] ?? 'Général'); ?>
                                    </span>
                                </td>
                                
                                <td class="text-muted small">📅 <?php echo htmlspecialchars($emp['date_emprunt'] ?? 'Non définie'); ?></td>
                                <td class="text-muted small">📅 <?php echo htmlspecialchars($emp['date_retour_prevue'] ?? 'Non définie'); ?></td>
                                
                                <td class="text-center pe-4">
                                    <?php 
                                    $statut = strtolower(trim($emp['statut'] ?? 'en_cours'));
                                    if ($statut === 'en_cours' || $statut === 'en cours'): 
                                    ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1 fw-bold shadow-sm" style="font-size: 0.85rem;">
                                             <i class="bi bi-clock-history"></i> En cours
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success text-white px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1 fw-semibold shadow-sm" style="font-size: 0.85rem;">
                                             <i class="bi bi-check-circle-fill"></i> Rendu
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>