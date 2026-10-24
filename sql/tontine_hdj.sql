-- =====================================================================
-- TONTINE HDJ — Base de données complète (schéma + données de démo)
-- Hôpital Dalal Jamm — Dakar, Sénégal
-- =====================================================================
--
-- DEUX FAÇONS D'OBTENIR LA BASE :
--
--   1) AUTOMATIQUE (recommandé) : ne rien faire. Au premier lancement de
--      l'application, la base, les tables et les données de démonstration
--      sont créées automatiquement (voir config/database.php).
--
--   2) MANUELLE : importer CE fichier dans phpMyAdmin
--      (onglet « Importer ») OU en ligne de commande :
--         mysql -u root < tontine_hdj.sql
--
-- Compte par défaut :  identifiant = admin   /   mot de passe = Admin@2026
-- (hash bcrypt ci-dessous vérifié et fonctionnel)
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `tontine_hdj`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tontine_hdj`;

-- Réinitialisation (décommenter pour repartir de zéro)
-- DROP TABLE IF EXISTS tirage_details, tirages, cotisations, membres, tontines, users;

-- ------------------------- Utilisateurs -------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('super_admin','president') NOT NULL DEFAULT 'president',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Tontines -------------------------
CREATE TABLE IF NOT EXISTS tontines (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    description     TEXT NULL,
    montant_mensuel BIGINT NOT NULL DEFAULT 0,
    duree           INT NOT NULL DEFAULT 10,
    date_debut      DATE NULL,
    date_fin        DATE NULL,
    statut          ENUM('active','suspendue','terminee') NOT NULL DEFAULT 'active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Membres -------------------------
CREATE TABLE IF NOT EXISTS membres (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    tontine_id           INT NOT NULL,
    nom_complet          VARCHAR(150) NOT NULL,
    telephone            VARCHAR(40) NULL,
    adresse              VARCHAR(255) NULL,
    photo                VARCHAR(255) NULL,
    cotisation_mensuelle BIGINT NOT NULL DEFAULT 0,
    gain                 BIGINT NOT NULL DEFAULT 0,
    a_gagne              TINYINT(1) NOT NULL DEFAULT 0,
    date_inscription     DATE NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_membre_tontine FOREIGN KEY (tontine_id)
        REFERENCES tontines(id) ON DELETE CASCADE,
    INDEX idx_membre_tontine (tontine_id),
    INDEX idx_membre_gagne (a_gagne)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Cotisations -------------------------
CREATE TABLE IF NOT EXISTS cotisations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    membre_id     INT NOT NULL,
    tontine_id    INT NOT NULL,
    mois          TINYINT NOT NULL,
    annee         SMALLINT NOT NULL,
    montant       BIGINT NOT NULL DEFAULT 0,
    statut        ENUM('paye','non_paye') NOT NULL DEFAULT 'non_paye',
    date_paiement DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cot_membre FOREIGN KEY (membre_id)
        REFERENCES membres(id) ON DELETE CASCADE,
    CONSTRAINT fk_cot_tontine FOREIGN KEY (tontine_id)
        REFERENCES tontines(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cot (membre_id, mois, annee),
    INDEX idx_cot_periode (tontine_id, annee, mois)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Tirages -------------------------
CREATE TABLE IF NOT EXISTS tirages (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    tontine_id        INT NOT NULL,
    date_tirage       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    mois              TINYINT NOT NULL,
    annee             SMALLINT NOT NULL,
    montant_distribue BIGINT NOT NULL DEFAULT 0,
    nb_gagnants       INT NOT NULL DEFAULT 0,
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tirage_tontine FOREIGN KEY (tontine_id)
        REFERENCES tontines(id) ON DELETE CASCADE,
    INDEX idx_tirage_tontine (tontine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Détails de tirage -------------------------
CREATE TABLE IF NOT EXISTS tirage_details (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    tirage_id   INT NOT NULL,
    membre_id   INT NOT NULL,
    gain        BIGINT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_td_tirage FOREIGN KEY (tirage_id)
        REFERENCES tirages(id) ON DELETE CASCADE,
    CONSTRAINT fk_td_membre FOREIGN KEY (membre_id)
        REFERENCES membres(id) ON DELETE CASCADE,
    INDEX idx_td_tirage (tirage_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DONNÉES
-- =====================================================================

-- Compte administrateur (mot de passe : Admin@2026)
INSERT INTO users (name, username, password, role)
SELECT 'Super Administrateur', 'admin', '$2y$10$cCwV/RTLRoiIEpDHSdBHhOWPmHYbZbnxp/OEFlFEhfvWpljL9YQlK', 'super_admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- Tontine de démonstration : pot mensuel = 5 000 000 FCFA, durée 10 mois
INSERT INTO tontines (id, nom, description, montant_mensuel, duree, date_debut, date_fin, statut)
SELECT 1, 'Tontine du Personnel HDJ',
       "Tontine solidaire du personnel de l'Hôpital Dalal Jamm. Pot mensuel distribué par tirage.",
       1300000, 10, '2026-06-01', '2027-03-01', 'active'
WHERE NOT EXISTS (SELECT 1 FROM tontines WHERE id = 1);

-- 24 membres (4×100 000 + 4×75 000 + 8×50 000 + 8×25 000 = pot de 5 000 000)
-- gain = cotisation_mensuelle × durée (10)
INSERT INTO membres
    (tontine_id, nom_complet, telephone, adresse, cotisation_mensuelle, gain, a_gagne, date_inscription)
SELECT * FROM (
    SELECT 1 AS tontine_id, 'Awa Diop' AS nom_complet, '+221 70 100 10 10' AS telephone, 'Dakar, Sénégal' AS adresse, 100000 AS cotisation_mensuelle, 1000000 AS gain, 0 AS a_gagne, CURDATE() AS date_inscription
    UNION ALL SELECT 1, 'Mamadou Ndiaye', '+221 71 107 11 13', 'Dakar, Sénégal', 100000, 1000000, 0, CURDATE()
    UNION ALL SELECT 1, 'Fatou Fall', '+221 72 114 12 16', 'Dakar, Sénégal', 100000, 1000000, 0, CURDATE()
    UNION ALL SELECT 1, 'Ibrahima Sow', '+221 73 121 13 19', 'Dakar, Sénégal', 100000, 1000000, 0, CURDATE()
    UNION ALL SELECT 1, 'Aïssatou Ba', '+221 74 128 14 22', 'Dakar, Sénégal', 75000, 750000, 0, CURDATE()
    UNION ALL SELECT 1, 'Cheikh Gueye', '+221 75 135 15 25', 'Dakar, Sénégal', 75000, 750000, 0, CURDATE()
    UNION ALL SELECT 1, 'Ndèye Sarr', '+221 76 142 16 28', 'Dakar, Sénégal', 75000, 750000, 0, CURDATE()
    UNION ALL SELECT 1, 'Modou Diallo', '+221 77 149 17 31', 'Dakar, Sénégal', 75000, 750000, 0, CURDATE()
    UNION ALL SELECT 1, 'Khadija Sy', '+221 78 156 18 34', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Ousmane Faye', '+221 79 163 19 37', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Maimouna Mbaye', '+221 70 170 20 40', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Abdoulaye Kane', '+221 71 177 21 43', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Sokhna Cissé', '+221 72 184 22 46', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Moussa Niang', '+221 73 191 23 49', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Bineta Thiam', '+221 74 198 24 52', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Aliou Dieng', '+221 75 205 25 55', 'Dakar, Sénégal', 50000, 500000, 0, CURDATE()
    UNION ALL SELECT 1, 'Coumba Wade', '+221 76 212 26 58', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Pape Seck', '+221 77 219 27 61', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Adama Camara', '+221 78 226 28 64', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Seynabou Touré', '+221 79 233 29 67', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Babacar Sané', '+221 70 240 30 70', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Rokhaya Diatta', '+221 71 247 31 73', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Idrissa Lô', '+221 72 254 32 76', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
    UNION ALL SELECT 1, 'Mariama Mané', '+221 73 261 33 79', 'Dakar, Sénégal', 25000, 250000, 0, CURDATE()
) AS demo
WHERE NOT EXISTS (SELECT 1 FROM membres WHERE tontine_id = 1);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- Fin du script. Connexion :  admin / Admin@2026
-- =====================================================================