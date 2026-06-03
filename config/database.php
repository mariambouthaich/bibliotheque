<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = "localhost";
        $db_name = "bibliotheque";
        $username = "root";
        $password = "";

        try {
            $this->pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=$db_name;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
// Msa7na $pdo = Database::getInstance(); bch ma-y-koun-ch conflict
?>