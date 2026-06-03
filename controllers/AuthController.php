<?php
require_once BASE_PATH . '/models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin() {
        // Assurez-vous que ce chemin est le bon pour votre fichier login.php
        require_once BASE_PATH . '/views/auth/login.php'; 
    }
    /** Affiche la page login */
   public function login(): void
{
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $requestedRole = $_POST['role'] ?? '';

    $user = $this->userModel->findByEmail($email);

    // Vérification complète
    if (
        $user &&
        password_verify($password, $user['password']) &&
        $user['role'] === $requestedRole
    ) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // Redirection selon le rôle
        $url = ($user['role'] === 'admin') ? 'dashboard' : 'user-home';

        header('Location: ' . BASE_URL . '/index.php?page=' . $url);
        exit;

    } else {

        // Retour vers la bonne page login
        header('Location: ' . BASE_URL . '/index.php?page=login&role=' . $requestedRole . '&error=1');
        exit;
    }
}
    /** Déconnexion */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }

    /** Vérifie si l'utilisateur est connecté */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /** Redirige si non connecté */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?page=login');
            exit;
        }
    }
 // تأكدي أن هذا الكود داخل الـ Controller الخاص بك
    public function register(): void
{
    // استقبال البيانات
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 1. التحقق من أن جميع الحقول مملوءة
    if (empty($nom) || empty($email) || empty($password)) {
        // توجيه لصفحة التسجيل مع رسالة خطأ
        header('Location: ' . BASE_URL . '/index.php?page=register&error=champs_obligatoires');
        exit;
    }

    // 2. التحقق من صيغة الإيميل (@student.com)
    if (!str_ends_with($email, '@student.com')) {
        header('Location: ' . BASE_URL . '/index.php?page=register&error=email_invalid');
        exit;
    }

    // إذا كان كل شيء صحيح، نقوم بعملية التخزين
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $this->userModel->create($nom, $email, $hashedPassword);

    header('Location: ' . BASE_URL . '/index.php?page=login&success=1');
}
    
}


