<?php
// On utilise la session par défaut, c'est plus stable
session_name('BIBLIO_APP_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/bibliotheque');
// ... le reste de vos requires

// 3. Chargement des ressources (Models & Controllers)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/BookController.php';
require_once __DIR__ . '/controllers/CategoryController.php';
require_once __DIR__ . '/controllers/EmpruntController.php'; 
require_once __DIR__ . '/models/Book.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/User.php';

// 4. Nettoyage des paramètres d'URL
$page   = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['page'] ?? 'home'); 
$action = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['action'] ?? '');

// 5. Instanciation des contrôleurs
$authCtrl     = new AuthController();
$bookCtrl     = new BookController();
$categoryCtrl = new CategoryController();
$empruntCtrl  = new EmpruntController(); 

// 6. Router Principal
switch ($page) {

    // ── Page d'Accueil (Landing Page) ──
    case 'home':
        $view = 'home/index.php'; 
        $title = 'Bienvenue - BiblioAdmin';
        break;

    // ── Authentification ──
    case 'login':
        $authCtrl->showLogin(); 
        exit; 
    
    case 'login-post':
        $authCtrl->login();
        break;
    
    case 'register':
    require_once BASE_PATH . '/views/auth/register.php';
    exit;

    case 'register-post':
    $authCtrl->register();
    break;
    
    case 'logout':
        $authCtrl->logout();
        break;

    // ── Espace Étudiant (Catalogue) ──
    case 'user-home':
        AuthController::requireAuth();
        $books = $bookCtrl->userIndex(); 
        $view = 'user/home.php'; 
        $title = 'Catalogue Étudiant';
        break;

    // ── Espace Étudiant - Mes Emprunts ──
    case 'my-loans':
        AuthController::requireAuth();
        if (isset($_GET['action']) && $_GET['action'] === 'rendre') {
            $empruntCtrl->rendre(); 
        } else {
            $empruntCtrl->mesEmprunts(); 
        }
        break;

    // ── Espace Étudiant - Mon Profil ──
    case 'profile':
        AuthController::requireAuth();
        $empruntCtrl->monProfil(); 
        break;

    // ── Espace Étudiant - Demande Emprunt ──
    case 'demander-emprunt':
        AuthController::requireAuth(); 
        $empruntCtrl->demander();      
        break;

    // ── Dashboard (Admin) ──
    case 'dashboard':
        AuthController::requireAuth();
        if ($_SESSION['user_role'] !== 'admin') { 
            header('Location: index.php?page=user-home'); 
            exit; 
        }

        $bookModel     = new Book();
        $categoryModel = new Category();
        $userModel     = new User();

        $stats = [
            'total_livres'      => $bookModel->count(),
            'total_categories'  => $categoryModel->count(),
            'total_users'       => $userModel->count(),
            'total_disponibles' => $bookModel->totalAvailable(),
            'recent_books'      => $bookModel->getRecent(6),
            'stats_category'    => $bookModel->statsByCategory(),
            'stats_monthly'     => $bookModel->statsMonthly(),
            'top_livre'         => $bookModel->getTopEmprunte(),
        ];

        $view = 'dashboard/index.php';
        break;

    // ── Gestion des Catégories (Vue HTML) ──
    case 'categories':
        AuthController::requireAuth();
        if ($_SESSION['user_role'] !== 'admin') { 
            header('Location: index.php?page=user-home'); 
            exit; 
        }
        $categoryModel = new Category();
        $categories = $categoryModel->getAll(); 
        $view = 'categories/index.php';
        $title = 'Liste des Catégories';
        break;

    // ── API Catégories (✨ التعديل السحري هنا) ──
   case 'api-categories':
        header('Content-Type: application/json');
        
        // إيلا كان الطلب POST (سواء للإضافة أو فاش كيعيط عليه الـ JS لي عندك)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // إيلا كان الـ JS صيفط بيانات جديدة كيكرييها، وإيلا صيفط طلب عادي كيعرض اللائحة
            if (isset($_POST['nom'])) {
                $categoryCtrl->apiCreate();
            } else {
                $categoryCtrl->apiList();
            }
        } else {
            // إيلا كان طلب GET عادي
            $categoryCtrl->apiList();
        }
        exit;

    // ── Gestion des Livres (Vue HTML) ──
    case 'books':
        AuthController::requireAuth();
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=user-home');
            exit;
        }
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        $view = 'books/index.php';
        $title = 'Gestion des Livres';
        break;

  // ── API Livres ──
// ── API Livres (التصحيح هنا) ──
    case 'api-books':
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'update') {
                $bookCtrl->apiUpdate();
            } elseif ($action === 'delete') {
                $bookCtrl->apiDelete();
            } else {
                $bookCtrl->apiCreate();
            }
        } else {
            // هذا الجزء هو الذي كان مفقوداً!
            // عندما يرسل الـ JS طلب GET لجلب البيانات:
            $bookCtrl->apiList(); 
        }
        exit; // الخروج مهم جداً
    // ... باقي الكود (GET, etc.)

    // ✨ تم إضافة هذا الـ Case الجديد هنا لمعالجة تحديث الكتب ومنع التكرار
    case 'api-books-update':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookCtrl->apiUpdate(); // هاد الدالة هي اللي غتكلف بالتعديل ف الـ Controller
        } else {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        exit;

    // ── Emprunts (Admin - Gestion Globale) ──
    case 'emprunts':
        AuthController::requireAuth(); 
        if ($_SESSION['user_role'] !== 'admin') { 
            header('Location: index.php?page=user-home'); 
            exit; 
        }

        switch ($action) {
            case 'add':
                $empruntCtrl->add(); 
                break;
            case 'save':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $empruntCtrl->save();
                }
                break;
            case 'rendre': 
                $empruntCtrl->rendre();
                break;
            default:
                $empruntCtrl->index(); 
                break;
        }
        break;

        
    // ── Route par défaut ──
    default:
        header('Location: ' . BASE_URL . '/index.php?page=home');
        exit;
} 

// 7. Affichage final (Layout Logic)
if (isset($view)) {
    // إذا كانت الصفحة هي الرئيسية أو صفحات فضاء الطالب التي تستدعي الهيدر داخلياً
    if ($page === 'home' || $page === 'my-loans' || $page === 'profile') {
        require_once BASE_PATH . '/views/' . $view;
    } 
    else {
        // باقي الصفحات الإدارية للـ Admin التي تعتمد على الـ Layout التلقائي
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/' . $view;
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
}