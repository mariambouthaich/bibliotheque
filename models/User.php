<?php
require_once BASE_PATH . '/config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

   public function getById(int $id): array|false
{
    // Zdna "password" hna f l-SELECT bach n-jibouh mn l-base de données
    $stmt = $this->db->prepare(
        'SELECT id, nom, email, password, role, created_at FROM users WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    /**
     * Trouve un utilisateur par email (pour l'authentification)
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, email, password, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre total d'utilisateurs
     */
    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM users');
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère tous les utilisateurs
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nom, email, role, created_at FROM users ORDER BY created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nom, string $email, string $password): bool
{
    $stmt = $this->db->prepare(
        'INSERT INTO users(nom, email, password, role)
         VALUES(:nom, :email, :password, :role)'
    );

    return $stmt->execute([
        ':nom' => $nom,
        ':email' => $email,
        ':password' => $password,
        ':role' => 'user'
    ]);
}
}