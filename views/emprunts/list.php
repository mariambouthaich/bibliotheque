<div class="container-fluid px-4 py-4">

    <?php 
    // تصفية الإعارات المتأخرة: الحالة "en_cours" وتاريخ اليوم فات تاريخ الإرجاع المتوقع
    $lesRetards = array_filter($emprunts, function($e) {
        return $e['statut'] === 'en_cours' && strtotime($e['date_retour_prevue']) < time();
    });

    if (!empty($lesRetards)): 
    ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <h5 class="alert-heading fw-bold d-flex align-items-center text-danger mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                Attention : <?= count($lesRetards) ?> Emprunt(s) en retard !
            </h5>
            <p class="small mb-2">Les étudiants suivants ont dépassé la date limite de retour sans rendre leurs livres :</p>
            <hr class="my-2 bg-danger opacity-25">
            <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0 text-danger small fw-bold">
                    <thead>
                        <tr>
                            <th>Emprunteur</th>
                            <th>Livre</th>
                            <th>Date Limite</th>
                            <th>Retard</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lesRetards as $retard): 
                            // حساب عدد أيام التأخير
                            $diffJours = ceil((time() - strtotime($retard['date_retour_prevue'])) / (60 * 60 * 24));
                        ?>
                            <tr>
                                <td>👤 <?= htmlspecialchars($retard['nom_emprunteur']) ?></td>
                                <td>📚 <?= htmlspecialchars($retard['livre_titre']) ?></td>
                                <td>📅 <?= date('d/m/Y', strtotime($retard['date_retour_prevue'])) ?></td>
                                <td><span class="badge bg-danger rounded-pill text-white">+<?= $diffJours ?> jours</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="bi bi-person-video3 me-2 text-primary"></i> Suivi des Emprunts
            </h2>
        </div>
        
        <div class="position-relative" style="max-width: 320px; width: 100%;">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="searchUser" class="form-control ps-5 py-2 rounded-pill shadow-sm border" 
                   placeholder="Rechercher un utilisateur (Nom)..." style="font-size: 0.85rem;">
        </div>

        <div>
            <a href="<?= BASE_URL ?>/index.php?page=emprunts&action=add" class="btn btn-primary shadow-sm px-4 rounded-pill">
                <i class="bi bi-plus-lg me-2"></i> Ajouter un emprunt
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase text-muted small fw-bold">Utilisateur (Emprunteur)</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold">Livre (ID & Titre)</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold">Période & Retour</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold">Durée</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold">Statut</th>
                            <th class="pe-4 py-3 text-uppercase text-muted small fw-bold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($emprunts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Aucun emprunt enregistré.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($emprunts as $e): ?>
                            <tr>
                                <td class="ps-4">
                                    <strong class="text-dark d-block"><?= htmlspecialchars($e['nom_emprunteur']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary mb-1">#<?= $e['livre_id'] ?></span><br>
                                    <small class="text-muted fw-medium"><?= htmlspecialchars($e['livre_titre']) ?></small>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-success fw-medium">Début: <?= $e['date_emprunt'] ?></span><br>
                                        <span class="text-danger fw-medium">Retour: <?= $e['date_retour_prevue'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-secondary"><?= $e['duree_jours'] ?> jours</span>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-warning text-dark';
                                        $statutTexte = $e['statut'];
                                        
                                        if($e['statut'] == 'en_cours' && strtotime($e['date_retour_prevue']) < time()) {
                                            $badgeClass = 'bg-danger text-white';
                                            $statutTexte = 'en retard';
                                        } elseif($e['statut'] == 'rendu') {
                                            $badgeClass = 'bg-success text-white';
                                            $statutTexte = 'rendu';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill text-capitalize px-3 py-1.5 small"><?= $statutTexte ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if ($e['statut'] === 'en_cours'): ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=emprunts&action=rendre&id=<?= $e['emprunt_id'] ?>" 
                                           class="btn btn-sm btn-outline-success fw-bold py-1 px-3 rounded-pill transition-all"
                                           onclick="return confirm('Confirmer que ce livre a été retourné ?');">
                                            <i class="bi bi-check2-circle me-1"></i> Rendre
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small fw-bold">
                                            <i class="bi bi-check-all text-success fs-5 align-middle"></i> Terminé
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
</div>