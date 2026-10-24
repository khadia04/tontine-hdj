<?php
/**
 * TONTINE HDJ — Modèle Tontine
 */

class Tontine
{
    private static function db(): PDO { return Database::connection(); }

    /** Identifiant du propriétaire courant (utilisateur connecté). */
    private static function ownerId(): int
    {
        return (int) (Auth::id() ?? 0);
    }

    /** Liste des tontines DU propriétaire connecté uniquement. */
    public static function all(): array
    {
        $sql = "SELECT t.*,
                       (SELECT COUNT(*) FROM membres m WHERE m.tontine_id = t.id) AS nb_membres,
                       (SELECT COUNT(*) FROM membres m WHERE m.tontine_id = t.id AND m.a_gagne = 1) AS nb_gagnants
                FROM tontines t
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([self::ownerId()]);
        return $stmt->fetchAll();
    }

    /**
     * Renvoie une tontine UNIQUEMENT si elle appartient à l'utilisateur connecté.
     * (Le filtrage par propriétaire assure le cloisonnement entre comptes.)
     */
    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM tontines WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, self::ownerId()]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO tontines (user_id, nom, description, montant_mensuel, duree, date_debut, date_fin, statut)
             VALUES (:user_id, :nom, :description, :montant_mensuel, :duree, :date_debut, :date_fin, :statut)"
        );
        $stmt->execute([
            ':user_id'         => $d['user_id'] ?? self::ownerId(),
            ':nom'             => $d['nom'],
            ':description'     => $d['description'] ?? null,
            ':montant_mensuel' => (int) $d['montant_mensuel'],
            ':duree'           => (int) $d['duree'],
            ':date_debut'      => $d['date_debut'] ?: null,
            ':date_fin'        => $d['date_fin'] ?: null,
            ':statut'          => $d['statut'] ?? 'active',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tontines SET nom=:nom, description=:description, montant_mensuel=:montant_mensuel,
                    duree=:duree, date_debut=:date_debut, date_fin=:date_fin, statut=:statut
             WHERE id=:id"
        );
        $stmt->execute([
            ':nom'             => $d['nom'],
            ':description'     => $d['description'] ?? null,
            ':montant_mensuel' => (int) $d['montant_mensuel'],
            ':duree'           => (int) $d['duree'],
            ':date_debut'      => $d['date_debut'] ?: null,
            ':date_fin'        => $d['date_fin'] ?: null,
            ':statut'          => $d['statut'] ?? 'active',
            ':id'              => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare("DELETE FROM tontines WHERE id = ?")->execute([$id]);
    }

    /** Statistiques détaillées d'une tontine. */
    public static function stats(int $id): array
    {
        $db = self::db();
        $nbMembres = (int) $db->query("SELECT COUNT(*) FROM membres WHERE tontine_id=$id")->fetchColumn();
        $nbGagnants = (int) $db->query("SELECT COUNT(*) FROM membres WHERE tontine_id=$id AND a_gagne=1")->fetchColumn();
        $sommeCotis = (int) $db->query("SELECT COALESCE(SUM(cotisation_mensuelle),0) FROM membres WHERE tontine_id=$id")->fetchColumn();
        $totalDistribue = (int) $db->query("SELECT COALESCE(SUM(montant_distribue),0) FROM tirages WHERE tontine_id=$id")->fetchColumn();

        return [
            'nb_membres'      => $nbMembres,
            'nb_gagnants'     => $nbGagnants,
            'nb_restants'     => $nbMembres - $nbGagnants,
            'somme_cotis'     => $sommeCotis,
            'total_distribue' => $totalDistribue,
        ];
    }

    /**
     * Recalcule et enregistre le pot mensuel d'une tontine.
     * Le pot mensuel = somme des cotisations mensuelles de tous ses membres
     * (ce qui est réellement collecté chaque mois). À appeler après tout
     * ajout / modification / suppression de membre.
     */
    public static function recalculerPot(int $tontineId): int
    {
        $db = self::db();
        $somme = (int) $db->query(
            "SELECT COALESCE(SUM(cotisation_mensuelle),0) FROM membres WHERE tontine_id=" . (int) $tontineId
        )->fetchColumn();
        $db->prepare("UPDATE tontines SET montant_mensuel = ? WHERE id = ?")
           ->execute([$somme, $tontineId]);
        return $somme;
    }

    /**
     * Déduit la date de fin à partir d'une date de début et d'une durée (en mois).
     * Convention : une tontine de N mois qui démarre au mois M se termine
     * au mois M + (N-1). Renvoie null si la date de début est vide/invalide.
     */
    public static function calculerDateFin(?string $dateDebut, int $duree): ?string
    {
        if (empty($dateDebut) || $duree < 1) return null;
        $ts = strtotime($dateDebut);
        if ($ts === false) return null;
        return date('Y-m-d', strtotime('+' . ($duree - 1) . ' months', $ts));
    }

    public static function statutBadge(string $statut): array
    {
        return match ($statut) {
            'active'    => ['Active', 'success'],
            'suspendue' => ['Suspendue', 'warning'],
            'terminee'  => ['Terminée', 'secondary'],
            default     => [ucfirst($statut), 'secondary'],
        };
    }
}
