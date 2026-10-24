<?php
/**
 * TONTINE HDJ — Gestion de l'authentification (sessions sécurisées)
 */

class Auth
{
    /** Tente de connecter un utilisateur. */
    public static function attempt(string $username, string $password): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Régénère l'ID de session contre la fixation
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => (int) $user['id'],
                'name'     => $user['name'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];
            $_SESSION['_last_activity'] = time();
            return true;
        }
        return false;
    }

    public static function check(): bool
    {
        if (empty($_SESSION['user'])) {
            return false;
        }
        // Expiration de session par inactivité
        if (isset($_SESSION['_last_activity'])
            && (time() - $_SESSION['_last_activity']) > SESSION_LIFETIME) {
            self::logout();
            return false;
        }
        $_SESSION['_last_activity'] = time();
        return true;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function roleLabel(): string
    {
        $role = $_SESSION['user']['role'] ?? '';
        return $role === 'super_admin' ? 'Super Administrateur' : 'Président';
    }

    /** Indique si l'utilisateur connecté est super administrateur. */
    public static function isSuperAdmin(): bool
    {
        return ($_SESSION['user']['role'] ?? '') === 'super_admin';
    }

    /** Exige une session super administrateur, sinon redirige. */
    public static function requireSuperAdmin(): void
    {
        self::require();
        if (!self::isSuperAdmin()) {
            flash('error', "Accès réservé au super administrateur.");
            redirect('dashboard');
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Exige une session active, sinon redirige vers le login. */
    public static function require(): void
    {
        if (!self::check()) {
            flash('error', 'Veuillez vous connecter pour continuer.');
            redirect('login');
        }
    }
}
