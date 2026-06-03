<?php
require_once BASE_PATH . '/config/database.php';

class Book
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les livres pour la datalist (Emprunts)
     */
    public function getAllBooks(): array 
    {
        $stmt = $this->db->query(
            "SELECT id, titre, categorie_id 
             FROM livres 
             ORDER BY titre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Récupère les livres avec pagination et filtres
     */
    public function getAll(
        int    $page     = 1,
        int    $perPage  = 10,
        string $search   = '',
        int    $catId    = 0
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];

        $where = 'WHERE 1=1';

        if (!empty($search)) {
            $where .= ' AND (l.titre LIKE :search OR l.auteur LIKE :search)';
            $params[':search'] = "%{$search}%";
        }

        if ($catId > 0) {
            $where .= ' AND l.categorie_id = :cat_id';
            $params[':cat_id'] = $catId;
        }

        // 1. Comptage total pour la pagination
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM livres l {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // 2. Données paginées
        $params[':limit']  = $perPage;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare(
            "SELECT l.*, c.nom AS categorie_nom
             FROM livres l
             LEFT JOIN categories c ON c.id = l.categorie_id
             {$where}
             ORDER BY l.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $val) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }

        $stmt->execute();

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'pages'      => (int) ceil($total / $perPage),
            'current'    => $page,
            'per_page'   => $perPage,
        ];
    }

    /** Trouve un livre par son ID */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, c.nom AS categorie_nom
             FROM livres l
             LEFT JOIN categories c ON c.id = l.categorie_id
             WHERE l.id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Crée un nouveau livre */
    public function create($data) 
    {
        $sql = "INSERT INTO livres (titre, auteur, categorie_id, quantite, description, created_at) 
                VALUES (:titre, :auteur, :categorie_id, :quantite, :description, NOW())";
                
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            ':titre'        => $data['titre'],
            ':auteur'       => $data['auteur'],
            ':categorie_id' => isset($data['categorie_id']) ? (int)$data['categorie_id'] : 1,
            ':quantite'     => (int)$data['quantite'],
            ':description'  => $data['description'] ?? null
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    /** Met à jour un livre existant */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE livres SET 
                    titre = :titre, 
                    auteur = :auteur, 
                    categorie_id = :categorie_id, 
                    quantite = :quantite, 
                    description = :description 
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'           => $id,
            ':titre'        => $data['titre'],
            ':auteur'       => $data['auteur'],
            ':categorie_id' => isset($data['categorie_id']) ? (int)$data['categorie_id'] : 1,
            ':quantite'     => (int)$data['quantite'],
            ':description'  => $data['description'] ?? null
        ]);
    }

    /** Supprime un livre de la table livres */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM livres WHERE id = :id');    
        return $stmt->execute([':id' => $id]);
    }

    /** Fonctions pour le Dashboard */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM livres');
        return (int) $stmt->fetchColumn();
    }

    public function totalAvailable(): int
    {
        $stmt = $this->db->query('SELECT SUM(quantite) FROM livres');
        return (int) ( $stmt->fetchColumn() ?? 0 );
    }

    public function statsByCategory(): array
    {
        $stmt = $this->db->query(
            'SELECT c.nom, COUNT(l.id) AS nb_livres, SUM(l.quantite) AS total_exemplaires
             FROM categories c
             LEFT JOIN livres l ON l.categorie_id = c.id
             GROUP BY c.id, c.nom
             ORDER BY nb_livres DESC'
         );
         return $stmt->fetchAll();
    }

    /** Statistiques mensuelles */
    public function statsMonthly(): array
    {
        $stmt = $this->db->query(
            'SELECT DATE_FORMAT(created_at, "%Y-%m") AS mois,
                    COUNT(*) AS nb
             FROM livres
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mois
             ORDER BY mois ASC'
        );
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, c.nom AS categorie_nom
             FROM livres l
             LEFT JOIN categories c ON c.id = l.categorie_id
             ORDER BY l.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Décrémenter la quantité d'un livre lors d'un emprunt */
    public function decrementQuantity($id): bool
    {
        $sql = "UPDATE livres
                SET quantite = quantite - 1
                WHERE id = :id AND quantite > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Récupère le livre le plus emprunté pour le Dashboard */
    public function getTopEmprunte(): array
    {
        $stmt = $this->db->query(
            "SELECT l.titre, COUNT(e.id) AS total
             FROM emprunts e
             JOIN livres l ON e.livre_id = l.id
             GROUP BY e.livre_id
             ORDER BY total DESC
             LIMIT 1"
        );
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : ['titre' => 'Aucun', 'total' => 0];
    }

}