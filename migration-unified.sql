-- Script de création de base de données pour la version unifiée de l'application

-- Suppression de la base de données si elle existe déjà
DROP DATABASE IF EXISTS prospection_unified;

-- Création de la base de données
CREATE DATABASE prospection_unified CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE prospection_unified;

-- Table role
CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    acces_rapports BOOLEAN NOT NULL DEFAULT FALSE,
    modification_donnees BOOLEAN NOT NULL DEFAULT FALSE,
    administration_systeme BOOLEAN NOT NULL DEFAULT FALSE,
    type_acces_fiches VARCHAR(50) NOT NULL DEFAULT 'Lecture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table permission
CREATE TABLE permission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_permission VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de relation permission_role
CREATE TABLE permission_role (
    permission_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    CONSTRAINT FK_6A711CA5D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE,
    CONSTRAINT FK_6A711CA5FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table utilisateur
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    date_creation DATETIME NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    token_expiration DATETIME DEFAULT NULL,
    type_interface VARCHAR(50) NOT NULL DEFAULT 'utilisateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de relation user_role
CREATE TABLE user_role (
    utilisateur_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (utilisateur_id, role_id),
    CONSTRAINT FK_2DE8C6A3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE,
    CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table entreprise
CREATE TABLE entreprise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) DEFAULT NULL,
    code_postal VARCHAR(20) DEFAULT NULL,
    ville VARCHAR(100) DEFAULT NULL,
    pays VARCHAR(100) DEFAULT NULL,
    telephone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(180) DEFAULT NULL,
    site_web VARCHAR(255) DEFAULT NULL,
    secteur_activite VARCHAR(100) DEFAULT NULL,
    taille VARCHAR(50) DEFAULT NULL,
    date_creation DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table fiche_entreprise
CREATE TABLE fiche_entreprise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entreprise_id INT NOT NULL,
    cree_par_id INT NOT NULL,
    date_creation DATETIME NOT NULL,
    date_derniere_modification DATETIME DEFAULT NULL,
    statut VARCHAR(50) NOT NULL,
    commentaire TEXT DEFAULT NULL,
    CONSTRAINT FK_C5887DA0A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id),
    CONSTRAINT FK_C5887DA05DEDC57F FOREIGN KEY (cree_par_id) REFERENCES utilisateur (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table historique_modification
CREATE TABLE historique_modification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    fiche_entreprise_id INT DEFAULT NULL,
    date_modification DATETIME NOT NULL,
    type_modification VARCHAR(50) NOT NULL,
    details TEXT DEFAULT NULL,
    CONSTRAINT FK_7068BCEFFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id),
    CONSTRAINT FK_7068BCEF7E9E4C8C FOREIGN KEY (fiche_entreprise_id) REFERENCES fiche_entreprise (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table session
CREATE TABLE session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    CONSTRAINT FK_9516BA95FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table notification
CREATE TABLE notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_creation DATETIME NOT NULL,
    lu BOOLEAN NOT NULL DEFAULT FALSE,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type VARCHAR(50) DEFAULT NULL,
    CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table message
CREATE TABLE messenger_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    body LONGTEXT NOT NULL,
    headers LONGTEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL,
    available_at DATETIME NOT NULL,
    delivered_at DATETIME DEFAULT NULL,
    INDEX IDX_75EA56E0FB7336F0 (queue_name),
    INDEX IDX_75EA56E0E3BD61CE (available_at),
    INDEX IDX_75EA56E016BA31DB (delivered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des rôles prédéfinis
INSERT INTO role (nom, description, acces_rapports, modification_donnees, administration_systeme, type_acces_fiches) VALUES 
('administrateur', 'Administrateur du système avec accès complet', TRUE, TRUE, TRUE, 'Lecture/Écriture'),
('prospection', 'Utilisateur en charge de la prospection', TRUE, TRUE, FALSE, 'Lecture/Écriture'),
('responsable', 'Responsable avec accès aux rapports', TRUE, FALSE, FALSE, 'Lecture'),
('academique', 'Utilisateur académique avec accès aux rapports', TRUE, FALSE, FALSE, 'Lecture'),
('secretariat', 'Secrétariat avec accès de lecture/écriture aux fiches', FALSE, FALSE, FALSE, 'Lecture/Écriture'),
('orientation', 'Service orientation avec accès aux rapports', TRUE, FALSE, FALSE, 'Lecture'),
('enseignant', 'Enseignant avec accès aux rapports et modification des données', TRUE, TRUE, FALSE, 'Lecture');

-- Insertion des permissions
INSERT INTO permission (nom_permission, description) VALUES
('Lecture', 'Permission de lecture des données'),
('Ecriture', 'Permission de modification des données');

-- Association des permissions aux rôles
INSERT INTO permission_role (permission_id, role_id) 
SELECT p.id, r.id FROM permission p, role r 
WHERE (p.nom_permission = 'Lecture' AND r.nom IN ('administrateur', 'prospection', 'responsable', 'academique', 'secretariat', 'orientation', 'enseignant'))
   OR (p.nom_permission = 'Ecriture' AND r.nom IN ('administrateur', 'prospection', 'secretariat'));

-- Création d'un administrateur par défaut
INSERT INTO utilisateur (nom, prenom, email, password, actif, date_creation, type_interface) VALUES
('Admin', 'System', 'admin@example.com', '$2y$13$AZGZvkHpKp12dr.RVGXareV2jBEd8iUj.tsmhYOzB0.AbE.U8yJnS', TRUE, NOW(), 'administrateur');

-- Association de l'administrateur au rôle administrateur
INSERT INTO user_role (utilisateur_id, role_id)
SELECT u.id, r.id FROM utilisateur u, role r
WHERE u.email = 'admin@example.com' AND r.nom = 'administrateur'; 