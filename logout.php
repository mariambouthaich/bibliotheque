<?php
/**
 * Fichier de déconnexion (logout.php)
 */

// 1. N-bdaw l-session bach n-9drou n-ms7ouha
session_start();

// 2. N-ms7ou ga3 l-variables de session (Titre, Nom, Role...)
$_SESSION = array();

// 3. N-ms7ou l-cookie dyal l-session ila kan f l-browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. N-d-mrou l-session tmamane f l-serveur
session_destroy();

// 5. Redirection l-page d'accueil (Home / Login)
header("Location: index.php");
exit();
?>