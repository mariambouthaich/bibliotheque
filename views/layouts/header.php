<?php
// Sécurité : accès direct interdit
defined('BASE_PATH') or die('Accès interdit');

// On récupère la page actuelle
$currentPage = $_GET['page'] ?? 'home';

// 1. SI on est sur la page d'accueil (Bienvenue), on n'affiche RIEN
if ($currentPage === 'home') {
    return; 
}

// 2. Déterminer si on est dans l'espace utilisateur ou admin
// Modifié pour correspondre exactement aux routes réelles de l'index
$isUserSpace = ($currentPage === 'user-home' || $currentPage === 'my-loans' || $currentPage === 'profile');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isUserSpace ? 'BiblioUser' : 'BiblioAdmin' ?> — <?= htmlspecialchars(ucfirst(str_replace(['-','_'], ' ', $currentPage))) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body>
<div class="app-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="brand-text">
                <span class="brand-name"><?= $isUserSpace ? 'BiblioUser' : 'BiblioAdmin' ?></span>
                <span class="brand-sub">Gestion de Bibliothèque</span>
            </div>
            <button class="sidebar-toggle-btn d-lg-none" id="sidebarClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_nom'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></span>
                <span class="user-role badge"><?= $isUserSpace ? 'Étudiant' : 'Administrateur' ?></span>
            </div>
        </div>
          
        <nav class="sidebar-nav">
            <?php if (!$isUserSpace): ?>
                <div class="nav-section-title">Navigation</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=dashboard">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>

                    <div class="nav-section-title mt-3">Catalogue</div>

                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'books' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=books">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <span>Livres</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=categories">
                            <i class="bi bi-tags-fill"></i>
                            <span>Catégories</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'emprunts' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=emprunts">
                            <i class="bi bi-person-video3"></i>
                            <span>Emprunts</span>
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <div class="nav-section-title">Espace Personnel</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'user-home' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=user-home">
                            <i class="bi bi-house-door-fill"></i>
                            <span>Accueil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'my-loans' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=my-loans">
                            <i class="bi bi-clock-history"></i>
                            <span>Mes Emprunts</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>/index.php?page=profile">
                            <i class="bi bi-person-circle"></i>
                            <span>Mon Profil</span>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <div class="nav-section-title mt-3">Compte</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link logout-link"
                       href="<?= BASE_URL ?>/logout.php">
                        <i class="bi bi-box-arrow-left"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <span class="version-badge">v2.0.0</span>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-content">

        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger-btn" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-flex">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/index.php?page=<?= $isUserSpace ? 'user-home' : 'dashboard' ?>">Accueil</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= ucfirst(str_replace(['-','_'], ' ', $currentPage)) ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="topbar-right">
                <div class="topbar-date d-none d-sm-block">
                    <i class="bi bi-calendar3"></i>
                    <span id="currentDate"></span>
                </div>
                <div class="topbar-user-info">
                    <div class="topbar-avatar">
                        <?= strtoupper(substr($_SESSION['user_nom'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline">
                        <?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?>
                    </span>
                </div>
            </div>
        </header>

        <main class="main-content-inner p-4">