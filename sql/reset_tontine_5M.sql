-- =====================================================================
-- TONTINE HDJ — Réinitialisation + tontine de TEST (pot mensuel 5 000 000)
-- Hôpital Dalal Jamm
--
-- Efface toutes les tontines existantes (et par cascade leurs membres,
-- cotisations et tirages) puis crée une tontine de test de 10 mois.
--
-- 60 membres répartis ainsi (cotisation -> gain = cotisation × 10) :
--   10 × 200 000  (gain 2 000 000)
--   20 × 100 000  (gain 1 000 000)
--   10 ×  50 000  (gain   500 000)
--   20 ×  25 000  (gain   250 000)
--   Cotisations totales / mois = 5000000  (= pot mensuel)
--
-- Chaque lot mensuel = {200000, 100000, 100000, 50000, 25000, 25000} = 5 000 000,
-- et contient TOUTES les catégories. Sur 10 mois => 10 lots.
--
-- À exécuter dans phpMyAdmin (onglet SQL ou Importer) sur la base tontine_hdj.
-- =====================================================================

USE `tontine_hdj`;

-- S'assure que la colonne propriétaire existe (bases créées avant cette version)
ALTER TABLE tontines ADD COLUMN IF NOT EXISTS user_id INT NULL;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM tirage_details;
DELETE FROM tirages;
DELETE FROM cotisations;
DELETE FROM membres;
DELETE FROM tontines;
ALTER TABLE tontines       AUTO_INCREMENT = 1;
ALTER TABLE membres        AUTO_INCREMENT = 1;
ALTER TABLE cotisations    AUTO_INCREMENT = 1;
ALTER TABLE tirages        AUTO_INCREMENT = 1;
ALTER TABLE tirage_details AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO tontines (id, user_id, nom, description, montant_mensuel, duree, date_debut, date_fin, statut)
VALUES (1, (SELECT id FROM users WHERE role='super_admin' ORDER BY id LIMIT 1), 'Tontine de Test 5M',
        'Tontine de test : 10 mois, pot mensuel 5 000 000. Chaque lot contient toutes les catégories ; chacun reçoit sa cotisation × 10.',
        5000000, 10, '2026-06-01', '2027-03-01', 'active');

INSERT INTO membres
    (tontine_id, nom_complet, telephone, adresse, cotisation_mensuelle, gain, a_gagne, date_inscription)
VALUES
    (1, 'Awa Diop', '+221 70 100 10 10', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Mamadou Ndiaye', '+221 71 107 11 13', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Fatou Fall', '+221 72 114 12 16', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Ibrahima Sow', '+221 73 121 13 19', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Aïssatou Ba', '+221 74 128 14 22', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Cheikh Gueye', '+221 75 135 15 25', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Ndèye Sarr', '+221 76 142 16 28', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Modou Diallo', '+221 77 149 17 31', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Khadija Sy', '+221 78 156 18 34', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Ousmane Faye', '+221 79 163 19 37', 'Dakar, Sénégal', 200000, 2000000, 0, '2026-06-01'),
    (1, 'Maimouna Mbaye', '+221 70 170 20 40', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Abdoulaye Kane', '+221 71 177 21 43', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Sokhna Cissé', '+221 72 184 22 46', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Moussa Niang', '+221 73 191 23 49', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Bineta Thiam', '+221 74 198 24 52', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Aliou Dieng', '+221 75 205 25 55', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Coumba Wade', '+221 76 212 26 58', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Pape Seck', '+221 77 219 27 61', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Adama Camara', '+221 78 226 28 64', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Seynabou Touré', '+221 79 233 29 67', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Babacar Sané', '+221 70 240 30 70', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Rokhaya Diatta', '+221 71 247 31 73', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Idrissa Lô', '+221 72 254 32 76', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Mariama Mané', '+221 73 261 33 79', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Serigne Gaye', '+221 74 268 34 82', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Fatima Diouf', '+221 75 275 35 85', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Alioune Ndour', '+221 76 282 36 88', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Astou Samb', '+221 77 289 37 91', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Mansour Badji', '+221 78 296 38 94', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Dieynaba Coly', '+221 79 303 39 97', 'Dakar, Sénégal', 100000, 1000000, 0, '2026-06-01'),
    (1, 'Saliou Sonko', '+221 70 310 40 10', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Ramatoulaye Tine', '+221 71 317 41 13', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Cheikhouna Dème', '+221 72 324 42 16', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Khady Goudiaby', '+221 73 331 43 19', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Mor Diédhiou', '+221 74 338 44 22', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Yacine Bâ', '+221 75 345 45 25', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Lamine Diop', '+221 76 352 46 28', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Nafissatou Ndiaye', '+221 77 359 47 31', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Tidiane Fall', '+221 78 366 48 34', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Marème Sow', '+221 79 373 49 37', 'Dakar, Sénégal', 50000, 500000, 0, '2026-06-01'),
    (1, 'Awa Ba', '+221 70 380 50 40', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Mamadou Gueye', '+221 71 387 51 43', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Fatou Sarr', '+221 72 394 52 46', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Ibrahima Diallo', '+221 73 401 53 49', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Aïssatou Sy', '+221 74 408 54 52', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Cheikh Faye', '+221 75 415 55 55', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Ndèye Mbaye', '+221 76 422 56 58', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Modou Kane', '+221 77 429 57 61', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Khadija Cissé', '+221 78 436 58 64', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Ousmane Niang', '+221 79 443 59 67', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Maimouna Thiam', '+221 70 450 60 70', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Abdoulaye Dieng', '+221 71 457 61 73', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Sokhna Wade', '+221 72 464 62 76', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Moussa Seck', '+221 73 471 63 79', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Bineta Camara', '+221 74 478 64 82', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Aliou Touré', '+221 75 485 65 85', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Coumba Sané', '+221 76 492 66 88', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Pape Diatta', '+221 77 499 67 91', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Adama Lô', '+221 78 506 68 94', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01'),
    (1, 'Seynabou Mané', '+221 79 513 69 97', 'Dakar, Sénégal', 25000, 250000, 0, '2026-06-01');

-- Vérification : SELECT cotisation_mensuelle, COUNT(*) FROM membres GROUP BY cotisation_mensuelle;
