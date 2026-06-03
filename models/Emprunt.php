<?php
require_once BASE_PATH . '/config/database.php';

class Emprunt {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // 1. جلب إعارات الطالب الحالي اعتماداً على إسمه المسجل ف الـ Session
    public function getEmpruntsStatsByUser(int $userId): array {
        // أولا كنجيبو اسم المستخدم من الـ id حيت الجدول مسجل بالأسماء نصياً
        $userStmt = $this->db->prepare("SELECT nom FROM users WHERE id = :user_id");
        $userStmt->execute([':user_id' => $userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        $nomUser = $user['nom'] ?? '';

        $sql = "SELECT 
                    l.titre AS livre_titre,
                    l.auteur AS livre_auteur,
                    c.nom AS categorie_nom,
                    COUNT(e.id) AS nbr_emprunts,
                    MAX(e.date_emprunt) AS derniere_date
                FROM emprunts e
                JOIN livres l ON e.livre_id = l.id
                LEFT JOIN categories c ON l.categorie_id = c.id
                WHERE e.nom_emprunteur = :nom_user
                GROUP BY l.id, l.titre, l.auteur, c.nom
                ORDER BY nbr_emprunts DESC, derniere_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nom_user' => $nomUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. جلب جميع الإعارات (خاص بالـ Admin) 
    public function getAllDetailed(): array {
        $sql = "SELECT 
                    e.id AS emprunt_id,
                    e.nom_emprunteur,
                    l.id AS livre_id,
                    l.titre AS livre_titre,
                    e.date_emprunt,
                    e.date_retour_prevue,
                    e.statut,
                    DATEDIFF(e.date_retour_prevue, e.date_emprunt) AS duree_jours
                FROM emprunts e
                JOIN livres l ON e.livre_id = l.id
                ORDER BY e.date_emprunt DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 4. إرجاع الكتاب (وزيادة المخزون بـ 1)
    // 4. إرجاع الكتاب (وزيادة المخزون بـ 1) - النسخة المصححة والآمنة
    public function marquerRendu(int $id): bool {
        try {
            // 1. نجلب أولاً الـ livre_id المرتبط بالإعارة قبل إجراء أي تحديث
            $stmtGetBook = $this->db->prepare("SELECT livre_id, statut FROM emprunts WHERE id = :id");
            $stmtGetBook->execute([':id' => $id]);
            $emprunt = $stmtGetBook->fetch(PDO::FETCH_ASSOC);

            // إذا لم يتم العثور على الإعارة أو كانت مرجعة مسبقاً، نوقف العملية
            if (!$emprunt || $emprunt['statut'] === 'rendu') {
                return false;
            }

            $livreId = (int)$emprunt['livre_id'];

            // 2. نبدأ المعاملة الآمنة لحماية البيانات
            $this->db->beginTransaction();

            // 3. نحدث حالة الإعارة إلى مسترجع 'rendu'
            $stmt1 = $this->db->prepare("UPDATE emprunts SET statut = 'rendu', date_retour_effective = NOW() WHERE id = :id");
            $stmt1->execute([':id' => $id]);

            // 4. نزيد الكمية في جدول الكتب مباشرة وبشكل صريح باستخدام الـ livreId الذي جلبناه
            $stmt2 = $this->db->prepare("UPDATE livres SET quantite = quantite + 1 WHERE id = :livre_id");
            $stmt2->execute([':livre_id' => $livreId]);

            // 5. نحفظ التغييرات نهائياً ف قاعدة البيانات
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // في حالة حدوث أي خطأ نلغي جميع العمليات
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }


    // 5. جلب إعارات الطالب بالتفصيل (تاريخ الاستعارة، الإرجاع، والوضعية)
    public function getEmpruntsByUser(int $userId): array {
        $userStmt = $this->db->prepare("SELECT nom FROM users WHERE id = :user_id");
        $userStmt->execute([':user_id' => $userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        $nomUser = $user['nom'] ?? '';

        $sql = "SELECT 
                    e.id AS emprunt_id,
                    e.nom_emprunteur,
                    l.id AS livre_id,
                    l.titre AS livre_titre,
                    l.auteur AS livre_auteur,
                    c.nom AS categorie_nom,
                    e.date_emprunt,
                    e.date_retour_prevue,
                    e.statut,
                    DATEDIFF(e.date_retour_prevue, e.date_emprunt) AS duree_jours
                FROM emprunts e
                JOIN livres l ON e.livre_id = l.id
                LEFT JOIN categories c ON l.categorie_id = c.id
                WHERE e.nom_emprunteur = :nom_user
                ORDER BY e.date_emprunt DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nom_user' => $nomUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    // 3. إضافة إعارة جديدة مع فحص شرط 3 كتب كحد أقصى (Strictement bloqué à 3)
    // 3. إضافة إعارة جديدة مع فحص شرط 3 كتب كحد أقصى (Max 3 livres en cours)
    public function ajouter(array $data): bool {
        try {
            // تنظيف الاسم المستلم وتوحيده
            $nomEmprunteur = trim($data['nom_emprunteur']);

            // أ) حساب الإعارات الحالية النشطة ('en_cours') لهذا الشخص بالظبط
            $sqlCheck = "SELECT COUNT(*) FROM emprunts WHERE LOWER(TRIM(nom_emprunteur)) = LOWER(:nom) AND statut = 'en_cours'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([':nom' => $nomEmprunteur]);
            $nbEmprunts = (int) $stmtCheck->fetchColumn();

            // إيلا كان ديجا واخد 3 د الكتب أو أكثر، السيستم كيبلوclear مباشرة ويرفض الإعارة
            if ($nbEmprunts >= 3) {
                return false; 
            }

            // ب) التحقق من أن الكتاب متوفر ف السطوك أولاً قبل أي شيء
            $sqlCheckStock = "SELECT quantite FROM livres WHERE id = :livre_id";
            $stmtStockCheck = $this->db->prepare($sqlCheckStock);
            $stmtStockCheck->execute([':livre_id' => $data['livre_id']]);
            $quantite = (int) $stmtStockCheck->fetchColumn();

            if ($quantite <= 0) {
                return false; // السطوك سالا، نرفض العملية
            }

            // ج) البدء ف المعاملة (Transaction) لتأمين البيانات
            $this->db->beginTransaction();

            // د) تسجيل الإعارة ف الجدول
            $sql = "INSERT INTO emprunts (livre_id, nom_emprunteur, date_emprunt, date_retour_prevue, statut) 
                    VALUES (:livre_id, :nom_emprunteur, :date_emprunt, :date_retour_prevue, :statut)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':livre_id'           => $data['livre_id'],
                ':nom_emprunteur'     => $nomEmprunteur, // الاسم المنظف
                ':date_emprunt'       => $data['date_emprunt'],
                ':date_retour_prevue' => $data['date_retour_prevue'],
                ':statut'             => $data['statut'] ?? 'en_cours'
            ]);

            // هـ) إنقاص الـ Stock بـ 1
            $sqlStock = "UPDATE livres SET quantite = quantite - 1 WHERE id = :livre_id AND quantite > 0";
            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute([':livre_id' => $data['livre_id']]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}