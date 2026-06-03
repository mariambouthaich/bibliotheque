<?php
require_once BASE_PATH . '/models/Book.php';
require_once BASE_PATH . '/models/Category.php';

class BookController
{
    private Book     $bookModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->bookModel     = new Book();
        $this->categoryModel = new Category();
    }

    /** Affiche la vue principale des livres (ADMIN) */
    public function index(): void
    {
        AuthController::requireAuth();
        $categories = $this->categoryModel->getAllSimple();
        require_once BASE_PATH . '/views/books/index.php';
    }

    /** Affiche le catalogue pour les étudiants */
    public function userIndex(): array 
    {
        AuthController::requireAuth();
        
        $result = $this->bookModel->getAll(1, 100);
        $books = $result['data'] ?? [];

        foreach ($books as &$book) {
            $book['image_url'] = $this->getImageUrl($book['image'] ?? 'default.jpg');
        }
        unset($book);

        return $books;
    }

    /** Affiche le formulaire de modification d'un livre */
    public function showEdit(): void
    {
        AuthController::requireAuth();
        
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header('Location: index.php?page=books');
            exit;
        }

        $categories = $this->categoryModel->getAllSimple();
        require_once BASE_PATH . '/views/books/edit.php';
    }

    /** API : Liste des livres (JSON) */
    public function apiList(): void
    {
        AuthController::requireAuth();
        $this->setJsonHeaders();

        $page   = max(1, (int) ($_GET['page']   ?? 1));
        $per    = max(5, min(50, (int) ($_GET['per_page'] ?? 10)));
        $search = trim($_GET['search'] ?? '');
        $catId  = (int) ($_GET['categorie'] ?? 0);

        $result = $this->bookModel->getAll($page, $per, $search, $catId);

        foreach ($result['data'] as &$book) {
            $book['image_url'] = $this->getImageUrl($book['image'] ?? 'default.jpg');
        }
        unset($book);

        echo json_encode(['success' => true, 'data' => $result]);
    }

    /** API : Récupérer un livre */
    public function apiGet(): void
    {
        AuthController::requireAuth();
        $this->setJsonHeaders();

        $id   = (int) ($_GET['id'] ?? 0);
        $book = $this->bookModel->findById($id);

        if (!$book) {
            echo json_encode(['success' => false, 'message' => 'Livre introuvable.']);
            return;
        }

        $book['image_url'] = $this->getImageUrl($book['image'] ?? 'default.jpg');
        echo json_encode(['success' => true, 'book' => $book]);
    }

    /** API : Mettre à jour un livre (عبر توجيهه لدالة الحفظ الذكية) */
    /** API : Mettre à jour un livre (النسخة المصححة والمطابقة للـ Model) */
    public function apiUpdate(): void 
    {
        header('Content-Type: application/json; charset=utf-8');
        AuthController::requireAuth();

        try {
            // 1. جلب وتنظيف البيانات القادمة من الـ Form
            $id           = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $titre        = trim($_POST['titre'] ?? '');
            $auteur       = trim($_POST['auteur'] ?? '');
            $categorie_id = !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null;
            $quantite     = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 0;
            $description  = trim($_POST['description'] ?? '');
            $imageName    = 'default.jpg';

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID du livre manquant.']);
                exit;
            }

            // 2. الاحتفاظ بالصورة الحالية من قاعدة البيانات
            $currentBook = $this->bookModel->findById($id);
            if ($currentBook) {
                $imageName = $currentBook['image'] ?? 'default.jpg';
            }

            // 3. التعامل مع رفع صورة جديدة إن وجدت أثناء التعديل
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploaded = time() . '_' . preg_replace("/[^A-Za-z0-9.]/", "_", $_FILES['image']['name']);
                $target = BASE_PATH . '/public/uploads/' . $uploaded;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imageName = $uploaded;
                }
            }

            // التحقق الصارم من الحقول الأساسية
            if (empty($titre) || empty($auteur) || empty($categorie_id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Veuillez remplir tous les champs obligatoires (Titre, Auteur, Catégorie).'
                ]);
                exit;
            }

            // 4. تجميع البيانات في مصفوفة $data واحدة كما يتوقعها الـ Model تماماً
            $data = [
                'titre'        => $titre,
                'auteur'       => $auteur,
                'categorie_id' => $categorie_id,
                'quantite'     => $quantite,
                'description'  => $description,
                'image'        => $imageName
            ];

            // 5. إرسال الطلب الصحيح للـ Model
            $result = $this->bookModel->update($id, $data);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Livre modifié avec succès !']);
            } else {
                // إذا ضغط حفظ دون تغيير أي قيمة، نعتبرها نجاحاً أيضاً لتجنب حيرة المستخدم
                echo json_encode(['success' => true, 'message' => 'Livre enregistré (aucune modification apportée).']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }
   
   /** API : Sauvegarder (Création ou Modification intelligente) */
/** API : Ajouter un livre (مخصصة للإضافة فقط) */
    public function apiCreate(): void 
    {
        $this->setJsonHeaders();
        AuthController::requireAuth();

        try {
            $titre        = trim($_POST['titre'] ?? '');
            $auteur       = trim($_POST['auteur'] ?? '');
            $categorie_id = !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null;
            $quantite     = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 0;
            $description  = trim($_POST['description'] ?? '');
            $imageName    = 'default.jpg';

            // التعامل مع الصورة
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploaded = time() . '_' . preg_replace("/[^A-Za-z0-9.]/", "_", $_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], BASE_PATH . '/public/uploads/' . $uploaded)) {
                    $imageName = $uploaded;
                }
            }

            if (empty($titre) || empty($auteur) || empty($categorie_id)) {
                echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants.']);
                exit; // خروج إجباري
            }
            
            $data = [
                'titre'        => $titre,
                'auteur'       => $auteur,
                'categorie_id' => $categorie_id,
                'quantite'     => $quantite,
                'description'  => $description,
                'image'        => $imageName,
                'date_ajout'   => date('Y-m-d H:i:s')
            ];

            $result = $this->bookModel->create($data);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Livre ajouté avec succès !']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit; // تأمين خروج
    }

    /** API : Supprimer un livre */
    public function apiDelete(): void
    {
        AuthController::requireAuth();
        $this->setJsonHeaders();

        $input = json_decode(file_get_contents('php://input'), true);
        $id    = (int) ($input['id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID invalide.']);
            return;
        }

        $result = $this->bookModel->delete($id);
        echo json_encode(['success' => $result, 'message' => $result ? 'Livre supprimé.' : 'Erreur suppression.']);
        exit;
    }

    // ─── Méthodes privées utilitaires ───
    private function handleImageUpload(array $file): string|false
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 5 * 1024 * 1024;

        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes) || $file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = BASE_PATH . '/public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('book_', true) . '.' . strtolower($ext);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
            return $imageName;
        }

        return false;
    }

    private function getImageUrl($image): string
    {
        if ($image === 'default.jpg' || empty($image)) {
            return BASE_URL . '/assets/images/default.jpg';
        }
        return BASE_URL . '/public/uploads/' . $image;
    }

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
}