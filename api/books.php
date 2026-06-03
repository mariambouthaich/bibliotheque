<?php
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Book.php';

$bookModel = new Book();
$action = $_GET['action'] ?? 'list';

try {
    // Hada huwa l-sir li kiy-nqi l-ktaba l-zayda (bhal Impression automatique)
    if (ob_get_length()) ob_clean(); 

    if ($action === 'list') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = $_GET['search'] ?? '';
        $catId = isset($_GET['catId']) ? (int)$_GET['catId'] : 0;
        
        $books = $bookModel->getAll($page, 10, $search, $catId);
        echo json_encode(["success" => true, "data" => $books]);
    } 
    elseif ($action === 'stats') {
        echo json_encode([
            'success' => true,
            'total' => $bookModel->count(),
            'available' => $bookModel->totalAvailable(),
            'by_category' => $bookModel->statsByCategory()
        ]);
    }
    elseif ($action === 'delete') {
        $id = $_GET['id'] ?? null;
        if ($id && $bookModel->delete($id)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la suppression"]);
        }
    }
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();
exit;