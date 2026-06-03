<?php

class EmpruntController {
    private Emprunt $empruntModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Emprunt.php';
        $this->empruntModel = new Emprunt();
    }

    /**
     * Affiche la liste des emprunts (Admin)
     */
    public function index() {
        global $view, $emprunts; 
        $emprunts = $this->empruntModel->getAllDetailed();
        $view = 'emprunts/list.php'; 
    }

    /**
     * Déclenché par action=add
     */
    public function add() {
        global $view, $allBooks, $allCategories;
        
        @include_once __DIR__ . '/../models/Book.php';
        @include_once __DIR__ . '/../models/Livre.php';
        @include_once __DIR__ . '/../models/Category.php';
        @include_once __DIR__ . '/../models/Categorie.php';

        $bookModel = class_exists('Book') ? new Book() : (class_exists('Livre') ? new Livre() : null);
        $categoryModel = class_exists('Category') ? new Category() : (class_exists('Categorie') ? new Categorie() : null);

        if ($bookModel) {
            if (method_exists($bookModel, 'getAllBooks')) {
                $allBooks = $bookModel->getAllBooks();
            } else {
                $result = $bookModel->getAll();
                $allBooks = $result['data'] ?? [];
            }
        } else {
            $allBooks = [];
        }

        if ($categoryModel) {
            if (method_exists($categoryModel, 'getAllCategories')) {
                $allCategories = $categoryModel->getAllCategories();
            } else {
                $allCategories = $categoryModel->getAll();
            }
        } else {
            $allCategories = [];
        }

        $view = 'emprunts/add.php';
    }

    /**
     * Traitement du formulaire par l'Admin
     */
    public function save(): void {
        $nom_emprunteur     = trim($_POST['nom_emprunteur'] ?? '');
        $livre_id           = !empty($_POST['livre_id']) ? (int)$_POST['livre_id'] : null;
        $date_emprunt       = $_POST['date_emprunt'] ?? date('Y-m-d');
        $date_retour_prevue = $_POST['date_retour_prevue'] ?? '';
        $statut             = $_POST['statut'] ?? 'en_cours';

        if (empty($nom_emprunteur) || empty($livre_id) || empty($date_retour_prevue)) {
            header("Location: " . BASE_URL . "/index.php?page=emprunts&action=add&error=missing_fields");
            exit;
        }

        $data = [
            'livre_id'           => $livre_id,
            'nom_emprunteur'     => $nom_emprunteur,
            'date_emprunt'       => $date_emprunt,
            'date_retour_prevue' => $date_retour_prevue,
            'statut'             => $statut
        ];

        $success = $this->empruntModel->ajouter($data);

        if ($success) {
            header("Location: " . BASE_URL . "/index.php?page=emprunts&action=index");
            exit;
        } else {
            header("Location: " . BASE_URL . "/index.php?page=emprunts&action=add&error=max_limit_or_stock");
            exit;
        }
    }

    /**
     * طلب الإعارة من طرف الطالب مباشرة
     */
    public function demander() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $livre_id = (int)$_POST['livre_id'];
            $duree_jours = (int)($_POST['duree'] ?? 14); 
            
            $nom_etudiant = $_SESSION['user_nom'] ?? $_SESSION['user_name'] ?? 'Houda'; 

            $date_emprunt = date('Y-m-d');
            $date_retour_prevue = date('Y-m-d', strtotime("+$duree_jours days"));

            $success = $this->empruntModel->ajouter([
                'livre_id'           => $livre_id,
                'nom_emprunteur'     => $nom_etudiant, 
                'date_emprunt'       => $date_emprunt,
                'date_retour_prevue' => $date_retour_prevue,
                'statut'             => 'en_cours'
            ]);

            if ($success) {
                header('Location: index.php?page=my-loans&success=1');
                exit();
            } else {
                header('Location: index.php?page=user-home&error=max_loans_reached');
                exit();
            }
        }
    }

    /**
     * Espace Étudiant : الصفحة الخاصة بعرض جدول الإعارات بالتفصيل
     */
    public function mesEmprunts() {
        global $view, $mesEmprunts, $title;
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
        $userId = $_SESSION['user_id'];

        $mesEmprunts = $this->empruntModel->getEmpruntsByUser($userId); 

        $title = 'Mes Emprunts';
        $view = 'user/emprunts.php'; 
    }
  
    /**
     * Espace Étudiant : Voir son profil personnel
     */
    public function monProfil() {
        global $view, $user, $title, $statsEmprunts;
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
        $userId = $_SESSION['user_id'];

        @include_once __DIR__ . '/../models/User.php';
        if (class_exists('User')) {
            $userModel = new User();
            $user = $userModel->getById($userId); 
        } else {
            $user = [
                'nom' => $_SESSION['user_nom'] ?? 'Houda',
                'email' => $_SESSION['user_email'] ?? 'houda@student.com',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        $statsEmprunts = $this->empruntModel->getEmpruntsStatsByUser($userId);

        $title = 'Mon Profil';
        $view = 'user/profil.php';
    }

    /**
     * Action pour marquer un livre comme rendu
     */
    public function rendre() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $success = $this->empruntModel->marquerRendu((int)$id);
            if ($success) {
                header("Location: index.php?page=my-loans&status=returned");
            } else {
                header("Location: index.php?page=my-loans&error=update_failed");
            }
            exit();
        }
    }
}