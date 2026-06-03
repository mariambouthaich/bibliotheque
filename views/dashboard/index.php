<?php
// T-akdi ana had l-fichier smitou: views/dashboard/index.php
defined('BASE_PATH') or die('Accès interdit');
require_once BASE_PATH . '/views/layouts/header.php';

// Data for Chart.js
$chartCategories = json_encode(array_column($stats['stats_category'], 'nom'));
$chartNbLivres   = json_encode(array_column($stats['stats_category'], 'nb_livres'));
$chartMonths     = json_encode(array_column($stats['stats_monthly'], 'mois'));
$chartMonthlyNb  = json_encode(array_column($stats['stats_monthly'], 'nb'));
?>

<div class="page-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">Tableau de bord</h1>
            <p class="page-subtitle">Vue d'ensemble de votre bibliothèque</p>
        </div>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/index.php?page=books" class="btn btn-primary-custom">
                <i class="bi bi-plus-lg me-2"></i> Ajouter un livre
            </a>
        </div>
    </div>

<div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-2.4" style="flex: 0 0 auto; width: 20%;">
    <div class="stat-card" style="background-color: #fff3cd; color: #856404; border-left: 5px solid #ffc107; min-height: 100px; padding: 12px; display: flex; align-items: center;">
        <div class="stat-icon" style="color: #ffc107;"><i class="bi bi-fire"></i></div>
        <div class="stat-content">
            <?php 
                // هنا كنشوفو كاع الإحتمالات لي يقدر الكنترولر يكون صيفط بيهم الداتا
                $bookData = $stats['top_livre'] ?? $stats['le_plus_emprunte'] ?? null;
                
                if (is_array($bookData)) {
                    $topTitle = $bookData['titre'] ?? $bookData['livre_titre'] ?? 'Aucun';
                    $topCount = $bookData['total'] ?? $bookData['nb_emprunts'] ?? 0;
                } else {
                    $topTitle = $stats['top_livre_titre'] ?? 'Aucun';
                    $topCount = $stats['top_livre_count'] ?? 0;
                }
            ?>
           <div class="fw-bold mb-1" style="font-size: 1rem; line-height: 1.2; color: #533f03;" title="<?= htmlspecialchars($topTitle) ?>">
    <?= htmlspecialchars($topTitle) ?>
</div>
<div class="stat-label" style="font-size: 0.75rem; color: #664d03; font-weight: 500;">
    Le plus emprunté (<span class="badge bg-warning text-dark rounded-pill px-1.5"><?= $topCount ?> fois</span>)
</div>
        </div>
    </div>
</div>
        <div class="col-12 col-sm-6 col-xl-2.4" style="flex: 0 0 auto; width: 20%;">
            <div class="stat-card stat-card--blue">
                <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_livres']) ?></div>
                    <div class="stat-label">Total des Livres</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2.4" style="flex: 0 0 auto; width: 20%;">
            <div class="stat-card stat-card--emerald">
                <div class="stat-icon"><i class="bi bi-tags-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_categories']) ?></div>
                    <div class="stat-label">Catégories</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2.4" style="flex: 0 0 auto; width: 20%;">
            <div class="stat-card stat-card--violet">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                    <div class="stat-label">Utilisateurs</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2.4" style="flex: 0 0 auto; width: 20%;">
            <div class="stat-card stat-card--amber">
                <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_disponibles']) ?></div>
                    <div class="stat-label">Exemplaires dispo.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="data-card">
                <div class="data-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="data-card-title">Derniers livres ajoutés</h5>
                        <p class="data-card-sub">Les <?= count($stats['recent_books']) ?> plus récents</p>
                    </div>
                    <a href="<?= BASE_URL ?>/index.php?page=books" class="btn btn-sm btn-outline-custom border">
                        Voir tout <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="data-card-body">
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>TITRE</th>
                                    <th>AUTEUR</th>
                                    <th>CATÉGORIE</th>
                                    <th>QUANTITÉ</th>
                                    <th>DATE D'AJOUT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats['recent_books'])): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Aucun livre trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stats['recent_books'] as $i => $book): ?>
                                        <tr>
                                            <td><span class="row-number"><?= $i + 1 ?></span></td>
                                            <td><span class="fw-600"><?= htmlspecialchars($book['titre']) ?></span></td>
                                            <td><?= htmlspecialchars($book['auteur']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($book['categorie_nom'] ?? 'N/A') ?></span></td>
                                            <td><?= $book['quantite'] ?> ex.</td>
                                            <td class="text-muted"><?= date('d/m/Y', strtotime($book['created_at'])) ?></td>
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
</div>

<script>
const chartCategories = <?= $chartCategories ?>;
const chartNbLivres   = <?= $chartNbLivres ?>;
const chartMonths     = <?= $chartMonths ?>;
const chartMonthlyNb  = <?= $chartMonthlyNb ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>

<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>