<?php
/**
 * TONTINE HDJ — Modèle Utilisateur (super admin & présidents de tontine)
 */

class User
{
    private static function db(): PDO { return Database::connection(); }

    /** Liste tous les utilisateurs avec le nombre de tontines et de membres créés. */
    public static function all(): array
    {
        return self::db()->query(
            "SELECT u.id, u.name, u.username, u.role, u.created_at,
                    (SELECT COUNT(*) FROM tontines t WHERE t.user_id = u.id) AS nb_tontines,
                    (SELECT COUNT(*) FROM membres m
                       WHERE m.tontine_id IN (SELECT id FROM tontines WHERE user_id = u.id)) AS nb_membres
             FROM users u
             ORDER BY (u.role = 'super_admin') DESC, u.name ASC"
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $s = self::db()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $params = [$username];
        if ($exceptId !== null) { $sql .= " AND id <> ?"; $params[] = $exceptId; }
        $s = self::db()->prepare($sql);
        $s->execute($params);
        return (int) $s->fetchColumn() > 0;
    }

    /** Crée un utilisateur (mot de passe haché en bcrypt). */
    public static function create(array $d): int
    {
        $s = self::db()->prepare(
            "INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)"
        );
        $s->execute([
            $d['name'],
            $d['username'],
            password_hash($d['password'], PASSWORD_DEFAULT),
            $d['role'] ?? 'president',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        self::db()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }

    public static function countTontines(): int
    {
        return (int) self::db()->query("SELECT COUNT(*) FROM tontines")->fetchColumn();
    }
}
