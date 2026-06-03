<?php
require_once BASE_PATH . '/config/database.php';

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Toutes les catégories avec comptage de livres */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id, c.nom, c.created_at, 
                    COUNT(l.id) AS nb_livres
             FROM categories c
             LEFT JOIN livres l ON l.categorie_id = c.id
             GROUP BY c.id, c.nom, c.created_at
             ORDER BY c.nom ASC'
        );
        return $stmt->fetchAll();
    }

    /** Toutes les catégories (simple, pour les selects) */
    public function getAllSimple(): array
    {
        $stmt = $this->db->query('SELECT id, nom FROM categories ORDER BY nom ASC');
        return $stmt->fetchAll();
    }

    /** Trouve par ID */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Crée une catégorie avec vérification d'unicité */
    public function create(string $nom): array
    {
        $nom = trim($nom);
        if ($this->nameExists($nom)) {
            return ['success' => false, 'message' => "La catégorie '{$nom}' existe déjà."];
        }

        $stmt = $this->db->prepare('INSERT INTO categories (nom) VALUES (:nom)');
        $result = $stmt->execute([':nom' => $nom]);

        return $result 
            ? ['success' => true, 'message' => "Catégorie ajoutée avec succès."] 
            : ['success' => false, 'message' => "Erreur lors de l'ajout."];
    }

    /** Met à jour une catégorie avec vérification d'unicité */
    public function update(int $id, string $nom): array
    {
        $nom = trim($nom);
        if ($this->nameExists($nom, $id)) {
            return ['success' => false, 'message' => "Ce nom est déjà utilisé."];
        }

        $stmt = $this->db->prepare('UPDATE categories SET nom = :nom WHERE id = :id');
        $result = $stmt->execute([':nom' => $nom, ':id' => $id]);

        return $result 
            ? ['success' => true, 'message' => "Catégorie mise à jour."] 
            : ['success' => false, 'message' => "Erreur lors de la mise à jour."];
    }

    /** Supprime une catégorie (Vérification des livres liés) */
   public function delete($id) {
    // 1. تشيك واش كاين شي كتاب واخد هاد الكاتيكوري
    $sqlCheck = "SELECT COUNT(*) FROM livres WHERE categorie_id = :id";
    $stmtCheck = $this->db->prepare($sqlCheck);
    $stmtCheck->execute([':id' => $id]);
    $count = (int)$stmtCheck->fetchColumn();

    // إيلا كان كاين على الأقل كتاب واحد، كنرفضو الحذف ونرجعو false
    if ($count > 0) {
        return false;
    }

    // 2. إيلا كانت فارغة، كنمسحوها عادي
    $sql = "DELETE FROM categories WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

    /** Compte le nombre de catégories (Pour le Dashboard) */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM categories');
        return (int) $stmt->fetchColumn();
    }

    /** Vérifie l'unicité du nom */
    public function nameExists(string $nom, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categories WHERE nom = :nom AND id != :id');
        $stmt->execute([':nom' => $nom, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}