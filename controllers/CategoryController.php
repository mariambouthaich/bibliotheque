<?php
require_once BASE_PATH . '/models/Category.php';

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        AuthController::requireAuth();
        require_once BASE_PATH . '/views/categories/index.php';
    }

    /**
     * ── 1. عرض اللائحة (مصلحة ومطابقة للـ JS) ──
     */
    public function apiList(): void
    {
        AuthController::requireAuth();
        
        // تنظيف الـ Buffer باش ما يخرج حتى فراغ خاسر
        if (ob_get_length()) ob_clean();
        $this->setJsonHeaders();

        $categories = $this->categoryModel->getAll();
        
        // إيلا كانت خاوية نرجعو Array خاوي، وإيلا عامرة نرجعوها نيشان بلا 'data'
        $output = $categories ? $categories : [];
        
        echo json_encode($output, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function apiGet(): void
    {
        AuthController::requireAuth();
        if (ob_get_length()) ob_clean();
        $this->setJsonHeaders();

        $id  = (int) ($_GET['id'] ?? 0);
        $cat = $this->categoryModel->findById($id);

        if (!$cat) {
            echo json_encode(['success' => false, 'message' => 'Catégorie introuvable.']);
            exit;
        }

        echo json_encode($cat, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * ── 2. إضافة كاتيكوري (تقبل الـ POST العادي والـ JSON) ──
     */
    public function apiCreate(): void
    {
        AuthController::requireAuth();
        if (ob_get_length()) ob_clean();
        $this->setJsonHeaders();

        // كنجربو نقرأو بـ $_POST أولاً، إيلا لقيناها خاوية كنمشيو لـ php://input
        $nom = $_POST['nom'] ?? '';
        
        if (empty($nom)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $nom = $input['nom'] ?? '';
        }

        $nom = trim($nom);

        if (empty($nom)) {
            echo json_encode(['success' => false, 'message' => 'Le nom est obligatoire.']);
            exit;
        }

        if (strlen($nom) > 100) {
            echo json_encode(['success' => false, 'message' => 'Le nom ne doit pas dépasser 100 caractères.']);
            exit;
        }

        if ($this->categoryModel->nameExists($nom)) {
            echo json_encode(['success' => false, 'message' => 'Cette catégorie existe déjà.']);
            exit;
        }

        $result = $this->categoryModel->create($nom);
        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? 'Catégorie ajoutée avec succès.' : 'Erreur lors de l\'ajout.'
        ]);
        exit;
    }

    public function apiUpdate(): void
    {
        AuthController::requireAuth();
        if (ob_get_length()) ob_clean();
        $this->setJsonHeaders();

        $input = json_decode(file_get_contents('php://input'), true);
        $id    = (int) ($input['id']  ?? ($_POST['id'] ?? 0));
        $nom   = trim($input['nom']   ?? ($_POST['nom'] ?? ''));

        if (!$id || empty($nom)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit;
        }

        if ($this->categoryModel->nameExists($nom, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ce nom de catégorie existe déjà.']);
            exit;
        }

        $result = $this->categoryModel->update($id, $nom);
        echo json_encode([
            'success' => (bool)$result,
            'message' => $result ? 'Catégorie mise à jour avec succès.' : 'Erreur lors de la mise à jour.'
        ]);
        exit;
    }

    /**
     * ── 3. حذف كاتيكوري ──
     */
    public function apiDelete(): void
    {
        AuthController::requireAuth();
        if (ob_get_length()) ob_clean();
        $this->setJsonHeaders();

        $id = $_GET['id'] ?? $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $success = $this->categoryModel->delete($id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Impossible de supprimer : cette catégorie contient encore des livres !'
            ]);
        }
        exit;
    }

    private function setJsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
}