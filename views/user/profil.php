<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>

<div class="page-content">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Mon Profil Personnel 👤</h1>
            <p class="page-subtitle">Gérez vos informations et consultez l'historique de vos lectures</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="data-card text-center p-4 h-100">
                <div class="avatar-container mb-3 d-inline-block">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 90px; height: 90px; font-size: 2.5rem; font-weight: bold;">
                        <?= strtoupper(substr(htmlspecialchars($user['nom'] ?? 'H'), 0, 1)) ?>
                    </div>
                </div>
                <h3 class="fw-bold mb-1"><?= htmlspecialchars($user['nom'] ?? 'Houda') ?></h3>
                <span class="badge bg-info text-white mb-4">Étudiant</span>

                <div class="text-start border-top pt-3">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Adresse Email</label>
                        <div class="p-2 bg-light rounded border fw-semibold text-dark">
                            <?= htmlspecialchars($user['email'] ?? 'houda@student.com') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Date d'inscription</label>
                        <div class="p-2 bg-light rounded border text-secondary small">
                            📅 <?= htmlspecialchars($user['created_at'] ?? 'Non définie') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="data-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0 text-dark">📊 Mes Habitudes de Lecture</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($statsEmprunts) ?> Livre(s) différent(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table custom-table mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Catégorie</th>
                                <th class="text-center">Fois Emprunté</th>
                                <th>Dernier Emprunt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($statsEmprunts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-book mb-2 d-block fs-3"></i>
                                        Vous n'avez encore emprunté aucun livre.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($statsEmprunts as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['livre_titre']) ?></div>
                                            <small class="text-muted">Par <?= htmlspecialchars($row['livre_auteur']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($row['categorie_nom'] ?? 'Général') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6 rounded-circle px-2 py-1">
                                                <?= htmlspecialchars($row['nbr_emprunts']) ?>
                                            </span>
                                        </td>
                                        <td class="text-secondary small fw-medium">
                                            📅 <?= htmlspecialchars($row['derniere_date']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>