
CREATE DATABASE IF NOT EXISTS bibliotheque
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bibliotheque;

-- ============================================================
-- Table : users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','user') NOT NULL DEFAULT 'admin',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table : categories
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table : livres
-- ============================================================
CREATE TABLE IF NOT EXISTS livres (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre            VARCHAR(255)   NOT NULL,
    auteur           VARCHAR(150)   NOT NULL,
    categorie_id     INT UNSIGNED   NOT NULL,
    isbn             VARCHAR(20)    DEFAULT NULL,
    description      TEXT           DEFAULT NULL,
    quantite         INT UNSIGNED   NOT NULL DEFAULT 1,
    image            VARCHAR(255)   DEFAULT 'default.jpg',
    date_publication DATE           DEFAULT NULL,
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- Table : emprunts
-- ==============================================================
CREATE TABLE IF NOT EXISTS emprunts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    livre_id INT UNSIGNED NOT NULL,
    nom_emprunteur VARCHAR(150) NOT NULL, -- Utilisateur
    date_emprunt DATE NOT NULL,            -- Période début
    date_retour_prevue DATE NOT NULL,     -- Période fin (Date de retour)
    date_retour_effective DATE DEFAULT NULL,
    statut ENUM('en_cours', 'rendu', 'en_retard') DEFAULT 'en_cours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Irtibat m3a table livres
    CONSTRAINT fk_livre_emprunt
        FOREIGN KEY (livre_id) REFERENCES livres(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- Données initiales
-- ============================================================

-- Administrateur par défaut (mot de passe : Admin@123)
INSERT INTO users (nom, email, password, role) VALUES
('Administrateur', 'admin@bibliotheque.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Catégories de base
INSERT INTO categories (nom) VALUES
('Roman'),
('Science-Fiction'),
('Histoire'),
('Philosophie'),
('Informatique'),
('Biographie'),
('Jeunesse'),
('Poésie');

-- Livres de démonstration
INSERT INTO livres (titre, auteur, categorie_id, isbn, description, quantite, date_publication) VALUES
('Les Misérables',       'Victor Hugo',          1, '978-2070409228', 'Chef-d\'oeuvre de la littérature française.', 5, '1862-04-03'),
('Le Petit Prince',      'Antoine de St-Exupéry',7, '978-2070612758', 'Conte philosophique et poétique.', 8, '1943-04-06'),
('1984',                 'George Orwell',         2, '978-2070368228', 'Roman dystopique majeur du XXe siècle.', 4, '1949-06-08'),
('Sapiens',              'Yuval Noah Harari',     3, '978-2226257017', 'Brève histoire de l\'humanité.', 6, '2011-01-01'),
('Le Monde de Sophie',   'Jostein Gaarder',       4, '978-2020241113', 'Roman sur l\'histoire de la philosophie.', 3, '1991-01-01'),
('Clean Code',           'Robert C. Martin',      5, '978-0132350884', 'Guide de bonnes pratiques en développement.', 7, '2008-08-11'),
('Steve Jobs',           'Walter Isaacson',       6, '978-2709638326', 'Biographie du fondateur d\'Apple.', 4, '2011-10-24'),
('Dune',                 'Frank Herbert',         2, '978-2266320566', 'Épopée de science-fiction classique.', 6, '1965-08-01');


-- Bach n-testiw ga3 l-7alat (en cours, rendu, en retard)
INSERT INTO emprunts (livre_id, nom_emprunteur, date_emprunt, date_retour_prevue, date_retour_effective, statut) 
VALUES 
(1, 'Ahmed El Mansouri', '2026-05-10', '2026-05-24', NULL, 'en_cours'),
(2, 'Sara Bennani', '2026-04-15', '2026-04-29', '2026-04-28', 'rendu'),
(3, 'Yassine Toumi', '2026-04-01', '2026-04-15', NULL, 'en_retard'),
(4, 'Houda El Bouchtaoui', '2026-05-12', '2026-05-26', NULL, 'en_cours'),
(5, 'Karim Alami', '2026-03-20', '2026-04-03', '2026-04-10', 'rendu');