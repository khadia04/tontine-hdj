<?php
/**
 * TONTINE HDJ — Modèle Cotisation
 */

class Cotisation
{
    private static function db(): PDO { return Database::connection(); }

    /**
     * Liste des membres avec l'état de leur cotisation pour une période donnée.
     * Retourne une ligne par membre, qu'il ait payé ou non.
     */
    public static function forPeriod(int $tontineId, int $mois, int $annee): array
    {
        $sql = "SELECT m.id AS membre_id, m.nom_complet, m.telephone, m.photo,
                       m.cotisation_mensuelle, m.a_gagne,
                       c.id AS cotisation_id, c.statut, c.date_paiement, c.montant
                FROM membres m
                LEFT JOIN cotisations c
                       ON c.membre_id = m.id AND c.mois = :mois AND c.annee = :annee
                WHERE m.tontine_id = :t
                ORDER BY m.nom_complet ASC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([':t' => $tontineId, ':mois' => $mois, ':annee' => $annee]);
        return $stmt->fetchAll();
    }

    /** Définit le statut de cotisation d'un membre pour une période (upsert). */
    public static function setStatut(int $membreId, int $tontineId, int $mois, int $annee, string $statut): array
    {
        $db = self::db();
        $membre = Membre::find($membreId);
        $montant = $membre ? (int) $membre['cotisation_mensuelle'] : 0;

        $datePaiement = $statut === 'paye' ? date('Y-m-d H:i:s') : null;

        $sql = "INSERT INTO cotisations (membre_id, tontine_id, mois, annee, montant, statut, date_paiement)
                VALUES (:m,:t,:mois,:annee,:montant,:statut,:date)
                ON DUPLICATE KEY UPDATE
                    statut = VALUES(statut),
                    montant = VALUES(montant),
                    date_paiement = VALUES(date_paiement)";
        $db->prepare($sql)->execute([
            ':m'       => $membreId,
            ':t'       => $tontineId,
            ':mois'    => $mois,
            ':annee'   => $annee,
            ':montant' => $montant,
            ':statut'  => $statut,
            ':date'    => $datePaiement,
        ]);

        return [
            'statut'        => $statut,
            'date_paiement' => $datePaiement,
            'montant'       => $montant,
        ];
    }

    /** Total payé pour une période. */
    public static function totalPaye(int $tontineId, int $mois, int $annee): int
    {
        $stmt = self::db()->prepare(
            "SELECT COALESCE(SUM(montant),0) FROM cotisations
             WHERE tontine_id=? AND mois=? AND annee=? AND statut='paye'"
        );
        $stmt->execute([$tontineId, $mois, $annee]);
        return (int) $stmt->fetchColumn();
    }

    /** Totaux mensuels payés (pour graphique) sur une année. */
    public static function totauxMensuels(int $tontineId, int $annee): array
    {
        $stmt = self::db()->prepare(
            "SELECT mois, COALESCE(SUM(montant),0) AS total
             FROM cotisations
             WHERE tontine_id=? AND annee=? AND statut='paye'
             GROUP BY mois"
        );
        $stmt->execute([$tontineId, $annee]);
        $data = array_fill(1, 12, 0);
        foreach ($stmt->fetchAll() as $row) {
            $data[(int) $row['mois']] = (int) $row['total'];
        }
        return $data;
    }

    /** Dernières cotisations payées (dashboard). */
    public static function dernieres(int $limit = 6, ?int $userId = null): array
    {
        $sql = "SELECT c.*, m.nom_complet, m.photo, t.nom AS tontine_nom
                FROM cotisations c
                JOIN membres m ON m.id = c.membre_id
                JOIN tontines t ON t.id = c.tontine_id
                WHERE c.statut = 'paye'";
        $params = [];
        if ($userId !== null) { $sql .= " AND t.user_id = ?"; $params[] = $userId; }
        $sql .= " ORDER BY c.date_paiement DESC LIMIT " . (int) $limit;
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
