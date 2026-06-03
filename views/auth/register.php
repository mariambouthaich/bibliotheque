<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow border-0">

                <div class="card-body p-4">

                    <h2 class="text-center mb-4 text-success">
                        Créer un compte
                    </h2>

                    <form action="<?= BASE_URL ?>/index.php?page=register-post" method="POST">
                    <div class="mb-3">
                    <label>Nom complet</label>
                    <input type="text" name="nom" class="form-control" placeholder="Entrez votre nom complet" required>
                    </div>

                    <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="prenom@student.com" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                    <label>Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success">Créer le compte</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="index.php?page=login&role=user">
                            Déjà un compte ? Se connecter
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

</body>
</html>