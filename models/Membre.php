<?php
/**
 * TONTINE HDJ — Modèle Membre
 *
 * Règle métier : gain = cotisation_mensuelle × durée de la tontine.
 */

class Membre
{
    private static function db(): PDO { return Database::connection(); }

    public static function byTontine(int $tontineId, ?string $search = null): array
    {
        $sql = "SELECT * FROM membres WHERE tontine_id = :t";
        $params = [':t' => $tontineId];
        if ($search !== null && $search !== '') {
            $sql .= " AND (nom_complet LIKE :s OR telephone LIKE :s)";
            $params[':s'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY nom_complet ASC";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM membres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Membres n'ayant pas encore gagné (éligibles au tirage). */
    public static function eligibles(int $tontineId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM membres WHERE tontine_id = ? AND a_gagne = 0 ORDER BY nom_complet"
        );
        $stmt->execute([$tontineId]);
        return $stmt->fetchAll();
    }

    public static function create(array $d, int $duree): int
    {
        $cotis = (int) $d['cotisation_mensuelle'];
        $gain  = $cotis * $duree;
        $stmt = self::db()->prepare(
            "INSERT INTO membres
             (tontine_id, nom_complet, telephone, adresse, photo, cotisation_mensuelle, gain, date_inscription)
             VALUES (:t,:nom,:tel,:adr,:photo,:cotis,:gain,:date)"
        );
        $stmt->execute([
            ':t'     => (int) $d['tontine_id'],
            ':nom'   => $d['nom_complet'],
            ':tel'   => $d['telephone'] ?? null,
            ':adr'   => $d['adresse'] ?? null,
            ':photo' => $d['photo'] ?? null,
            ':cotis' => $cotis,
            ':gain'  => $gain,
            ':date'  => ($d['date_inscription'] ?? '') ?: date('Y-m-d'),
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d, int $duree): void
    {
        $cotis = (int) $d['cotisation_mensuelle'];
        $gain  = $cotis * $duree;

        $fields = "nom_complet=:nom, telephone=:tel, adresse=:adr,
                   cotisation_mensuelle=:cotis, gain=:gain, date_inscription=:date";
        $params = [
            ':nom'   => $d['nom_complet'],
            ':tel'   => $d['telephone'] ?? null,
            ':adr'   => $d['adresse'] ?? null,
            ':cotis' => $cotis,
            ':gain'  => $gain,
            ':date'  => ($d['date_inscription'] ?? '') ?: date('Y-m-d'),
            ':id'    => $id,
        ];
        if (!empty($d['photo'])) {
            $fields .= ", photo=:photo";
            $params[':photo'] = $d['photo'];
        }
        $stmt = self::db()->prepare("UPDATE membres SET $fields WHERE id=:id");
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare("DELETE FROM membres WHERE id = ?")->execute([$id]);
    }

    public static function marquerGagnant(int $id): void
    {
        self::db()->prepare("UPDATE membres SET a_gagne = 1 WHERE id = ?")->execute([$id]);
    }
}
