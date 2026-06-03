<?php
defined('BASE_PATH') or die('Accès interdit');

// On récupère le rôle via l'URL (ex: ?page=login&role=user)
$requestedRole = $_GET['role'] ?? 'admin'; 

$errors      = $_SESSION['login_errors'] ?? [];
$savedEmail  = $_SESSION['login_email']  ?? '';
unset($_SESSION['login_errors'], $_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioAdmin — Connexion</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body class="login-page">

<div class="login-wrapper">

    <div class="login-illustration">
        <div class="illustration-content">
            <div class="ill-brand">
                <div class="ill-icon"><i class="bi bi-book-half"></i></div>
                <h1 class="ill-title"><?= ($requestedRole === 'user') ? 'BiblioUser' : 'BiblioAdmin' ?></h1>
            </div>
            <p class="ill-tagline">
            <?= ($requestedRole === 'user') 
            ? 'Explorez des milliers d\'ouvrages et gérez vos emprunts facilement.' 
            : 'Gérez votre bibliothèque avec élégance et efficacité.' ?>
            </p>
<div class="ill-features">
    <?php if ($requestedRole === 'user'): ?>
        <div class="feat-item">
            <div class="feat-icon"><i class="bi bi-search"></i></div>
            <div>
                <strong>Recherche Intelligente</strong>
                <p>Trouvez vos livres par titre ou auteur</p>
            </div>
        </div>
        <div class="feat-item">
            <div class="feat-icon"><i class="bi bi-clock-history"></i></div>
            <div>
                <strong>Suivi des Emprunts</strong>
                <p>Consultez vos dates de retour en direct</p>
            </div>
        </div>
    <?php else: ?>
        <div class="feat-item">
            <div class="feat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div><strong>Gestion CRUD complète</strong><p>Livres, catégories, utilisateurs</p></div>
        </div>
        <div class="feat-item">
            <div class="feat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div><strong>Statistiques en temps réel</strong><p>Tableaux de bord interactifs</p></div>
        </div>
    <?php endif; ?>
</div>

            <div class="ill-circles">
                <div class="circle c1"></div>
                <div class="circle c2"></div>
                <div class="circle c3"></div>
            </div>
        </div>
    </div>

    <div class="login-form-panel">
        <div class="login-form-wrapper">

            <div class="login-header">
                <div class="login-logo d-lg-none">
                    <i class="bi bi-book-half"></i>
                </div>
                <h2 class="login-title">Bienvenue</h2>
                <p class="login-subtitle">
                    Connectez-vous à votre espace <strong><?= ($requestedRole === 'user') ? 'Étudiant' : 'Administrateur' ?></strong>
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show login-alert" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/index.php?page=login-post" method="POST"
                  class="login-form" id="loginForm" novalidate>

                <?php
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                ?>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <input type="hidden" name="role" value="<?= htmlspecialchars($requestedRole) ?>">

                <div class="form-floating mb-4">
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="admin@bibliotheque.com"
                        value="<?= htmlspecialchars($savedEmail) ?>"
                        required
                        autocomplete="email">
                    <label for="email"><i class="bi bi-envelope me-2"></i>Adresse email</label>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Mot de passe"
                        required
                        autocomplete="current-password">
                    <label for="password"><i class="bi bi-lock me-2"></i>Mot de passe</label>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                    </div>
                </div>

                <button type="submit" class="btn <?= ($requestedRole === 'user') ? 'btn-success' : 'btn-primary' ?> btn-login w-100" id="loginBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Se connecter
                    </span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Connexion...
                    </span>
                </button>

            </form>
            <?php if ($requestedRole === 'user'): ?>
                <div class="text-center mt-3">
                    <a href="index.php?page=register" class="text-decoration-none">
                        Créer un compte
                    </a>
                </div>
            <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    toggleBtn?.addEventListener('click', () => {
        const isText = passwordInput.type === 'text';
        passwordInput.type = isText ? 'password' : 'text';
        eyeIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    form?.addEventListener('submit', (e) => {
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email || !password) {
            e.preventDefault();
            return;
        }

        loginBtn.querySelector('.btn-text').classList.add('d-none');
        loginBtn.querySelector('.btn-spinner').classList.remove('d-none');
        loginBtn.disabled = true;
    });
});
</script>
</body>
</html>