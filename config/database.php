<?php
/**
 * TONTINE HDJ — Connexion PDO + création automatique de la base
 *
 * Au premier lancement, ce fichier :
 *   1. se connecte au serveur MySQL,
 *   2. crée la base `tontine_hdj` si elle n'existe pas,
 *   3. crée toutes les tables,
 *   4. insère le compte administrateur par défaut,
 *   5. insère un petit jeu de données de démonstration.
 */

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // 1) Connexion au serveur (sans base) pour pouvoir la créer si besoin
        try {
            $dsnServer = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
            $pdo = new PDO($dsnServer, DB_USER, DB_PASS, $options);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
                        CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");
        } catch (PDOException $e) {
            self::fail($e);
        }

        self::$instance = $pdo;
        self::migrate($pdo);
        self::seed($pdo);

        return self::$instance;
    }

    /** Création des tables si elles n'existent pas. */
    private static function migrate(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                name          VARCHAR(150) NOT NULL,
                username      VARCHAR(60)  NOT NULL UNIQUE,
                password      VARCHAR(255) NOT NULL,
                role          ENUM('super_admin','president') NOT NULL DEFAULT 'president',
                created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tontines (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                user_id         INT NULL,
                nom             VARCHAR(150) NOT NULL,
                description     TEXT NULL,
                montant_mensuel BIGINT NOT NULL DEFAULT 0,
                duree           INT NOT NULL DEFAULT 10,
                date_debut      DATE NULL,
                date_fin        DATE NULL,
                statut          ENUM('active','suspendue','terminee') NOT NULL DEFAULT 'active',
                created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tontine_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS membres (
                id                  INT AUTO_INCREMENT PRIMARY KEY,
                tontine_id          INT NOT NULL,
                nom_complet         VARCHAR(150) NOT NULL,
                telephone           VARCHAR(40) NULL,
                adresse             VARCHAR(255) NULL,
                photo               VARCHAR(255) NULL,
                cotisation_mensuelle BIGINT NOT NULL DEFAULT 0,
                gain                BIGINT NOT NULL DEFAULT 0,
                a_gagne             TINYINT(1) NOT NULL DEFAULT 0,
                date_inscription    DATE NULL,
                created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_membre_tontine FOREIGN KEY (tontine_id)
                    REFERENCES tontines(id) ON DELETE CASCADE,
                INDEX idx_membre_tontine (tontine_id),
                INDEX idx_membre_gagne (a_gagne)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
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
        ");

        $pdo->exec("
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
        ");

        $pdo->exec("
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
        ");

        // --- Migration : colonne user_id (propriétaire) sur les bases existantes ---
        try {
            $hasCol = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tontines' AND COLUMN_NAME = 'user_id'"
            )->fetchColumn();
            if ($hasCol === 0) {
                $pdo->exec("ALTER TABLE tontines ADD COLUMN user_id INT NULL AFTER id");
                $pdo->exec("ALTER TABLE tontines ADD INDEX idx_tontine_user (user_id)");
            }
            // Rattache les anciennes tontines sans propriétaire au 1er super admin.
            $pdo->exec(
                "UPDATE tontines SET user_id = (
                    SELECT id FROM (SELECT id FROM users WHERE role='super_admin' ORDER BY id LIMIT 1) AS sa
                 ) WHERE user_id IS NULL"
            );
        } catch (Throwable $e) {
            // Migration silencieuse : on n'interrompt pas l'application.
        }
    }

    /** Insertion du compte admin + données de démonstration au 1er lancement. */
    private static function seed(PDO $pdo): void
    {
        // Compte administrateur par défaut
        $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role)
                                   VALUES (?, ?, ?, 'super_admin')");
            $stmt->execute([
                DEFAULT_ADMIN_NAME,
                DEFAULT_ADMIN_USER,
                password_hash(DEFAULT_ADMIN_PASS, PASSWORD_DEFAULT),
            ]);
        }

        // Données de démonstration (uniquement si aucune tontine n'existe)
        $tontineCount = (int) $pdo->query("SELECT COUNT(*) FROM tontines")->fetchColumn();
        if ($tontineCount === 0) {
            self::seedDemo($pdo);
        }
    }

    private static function seedDemo(PDO $pdo): void
    {
        $duree = 10;
        $debut = date('Y-m-01');
        $fin   = date('Y-m-d', strtotime("$debut +" . ($duree - 1) . " months"));

        // Propriétaire = 1er super admin (la tontine de démo lui appartient).
        $ownerId = (int) $pdo->query("SELECT id FROM users WHERE role='super_admin' ORDER BY id LIMIT 1")->fetchColumn();

        // Tontine de démonstration : le pot mensuel = somme des cotisations
        // (renseigné après l'ajout des membres).
        $pdo->prepare("INSERT INTO tontines
            (user_id, nom, description, montant_mensuel, duree, date_debut, date_fin, statut)
            VALUES (?,?,?,?,?,?,?, 'active')")
            ->execute([
                $ownerId ?: null,
                'Tontine du Personnel HDJ',
                "Tontine solidaire du personnel de l'Hôpital Dalal Jamm. Chaque mois, un lot de gagnants se partage le pot ; chacun reçoit sa cotisation × la durée.",
                0, $duree, $debut, $fin,
            ]);
        $tontineId = (int) $pdo->lastInsertId();

        // 60 membres répartis en 4 catégories de cotisation.
        // Chaque lot mensuel = {200 000, 100 000, 100 000, 50 000, 25 000, 25 000}
        //   gains : 2 000 000 + 1 000 000 + 1 000 000 + 500 000 + 250 000 + 250 000 = 5 000 000
        // -> 10 lots possibles, chacun contenant TOUTES les catégories.
        //   10 × 200 000 + 20 × 100 000 + 10 × 50 000 + 20 × 25 000 = 5 000 000 / mois
        $prenoms = ['Awa','Mamadou','Fatou','Ibrahima','Aïssatou','Cheikh','Ndèye','Modou',
                    'Khadija','Ousmane','Maimouna','Abdoulaye','Sokhna','Moussa','Bineta','Aliou',
                    'Coumba','Pape','Adama','Seynabou','Babacar','Rokhaya','Idrissa','Mariama',
                    'Serigne','Fatima','Alioune','Astou','Mansour','Dieynaba','Saliou','Ramatoulaye',
                    'Cheikhouna','Khady','Mor','Yacine','Lamine','Nafissatou','Tidiane','Marème'];
        $noms = ['Diop','Ndiaye','Fall','Sow','Ba','Gueye','Sarr','Diallo','Sy','Faye','Mbaye','Kane',
                 'Cissé','Niang','Thiam','Dieng','Wade','Seck','Camara','Touré','Sané','Diatta','Lô','Mané',
                 'Gaye','Diouf','Ndour','Samb','Badji','Coly','Sonko','Tine','Dème','Goudiaby','Diédhiou','Bâ'];

        $repartition = array_merge(
            array_fill(0, 10, 200000),
            array_fill(0, 20, 100000),
            array_fill(0, 10, 50000),
            array_fill(0, 20, 25000)
        );

        $ins = $pdo->prepare("INSERT INTO membres
            (tontine_id, nom_complet, telephone, adresse, cotisation_mensuelle, gain, date_inscription)
            VALUES (?,?,?,?,?,?,?)");

        foreach ($repartition as $i => $cotis) {
            $nom = $prenoms[$i % count($prenoms)] . ' ' . $noms[$i % count($noms)];
            $tel = '+221 7' . random_int(0, 9) . ' ' . random_int(100, 999) . ' ' . random_int(10, 99) . ' ' . random_int(10, 99);
            $ins->execute([
                $tontineId,
                $nom,
                $tel,
                'Dakar, Sénégal',
                $cotis,
                $cotis * $duree,
                $debut,
            ]);
        }

        // Pot mensuel = somme des cotisations des membres (= 5 000 000 ici).
        $pdo->prepare("UPDATE tontines SET montant_mensuel =
                        (SELECT COALESCE(SUM(cotisation_mensuelle),0) FROM membres WHERE tontine_id = ?)
                       WHERE id = ?")
            ->execute([$tontineId, $tontineId]);
    }

    private static function fail(PDOException $e): void
    {
        http_response_code(500);
        $msg = APP_DEBUG ? $e->getMessage() : 'Erreur de connexion à la base de données.';
        echo "<!doctype html><html lang='fr'><meta charset='utf-8'>
              <title>Erreur base de données</title>
              <body style='font-family:system-ui;background:#114538;color:#fff;padding:60px;text-align:center'>
              <h1 style='color:#1CC7A5'>⚠️ Connexion MySQL impossible</h1>
              <p>Vérifiez que <b>MySQL est démarré dans XAMPP</b> et que les identifiants
              dans <code>config/config.php</code> sont corrects.</p>
              <pre style='display:inline-block;text-align:left;background:rgba(0,0,0,.3);padding:18px;border-radius:12px;max-width:680px;white-space:pre-wrap'>"
              . htmlspecialchars($msg) .
              "</pre></body></html>";
        exit;
    }
}
