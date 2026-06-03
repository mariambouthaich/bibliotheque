<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/Category.php';

$catModel = new Category();

try {
    // On récupère simplement toutes les catégories
    $categories = $catModel->getAllSimple();
    echo json_encode($categories);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>