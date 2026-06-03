<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - BiblioAdmin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .welcome-box {
            border: none;
            transition: transform 0.3s ease;
        }
        .welcome-box:hover {
            transform: translateY(-5px);
        }
        .hover-shadow:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container text-center py-5">
    <div class="welcome-box shadow p-5 rounded bg-white mx-auto" style="max-width: 900px;">
        <h1 class="display-4 text-primary fw-bold mb-4">Bienvenue  👋</h1>
        <p class="lead mb-5 text-secondary">dans notre système intelligent de gestion de votre bibliothèque.</p>
        
        <div class="row justify-content-center gap-4">
            <div class="col-md-5">
                <div class="p-4 border rounded hover-shadow h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="fw-semibold text-primary">Administration</h3>
                        <p class="text-muted small">Gérer les livres, les catégories et les prêts en toute simplicité.</p>
                    </div>
                    <a href="index.php?page=login&role=admin" class="btn btn-primary btn-lg w-100 mt-3">Se connecter</a>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="p-4 border rounded hover-shadow border-success h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="fw-semibold text-success">Espace Étudiant</h3>
                        <p class="text-muted small">Consulter la liste des livres disponibles et faire un emprunt.</p>
                    </div>
                    <a href="index.php?page=login&role=user" class="btn btn-success btn-lg w-100 mt-3">Accéder au catalogue</a>
                </div>
            </div>
        </div>
        
        <div class="mt-5 pt-4 border-top">
            <p class="text-muted mb-0 small">ENSA Khouribga — GI-1 Projet PHP</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>