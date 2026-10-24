<?php
/**
 * TONTINE HDJ — Fonctions utilitaires globales
 */

/** Échappement HTML sûr. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Construit une URL interne à partir de la racine de l'app. */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/** URL d'un asset (css/js/img). */
function asset(string $path): string
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

/** Redirection HTTP puis arrêt. */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Formatage monétaire FCFA (séparateur d'espace). */
function fcfa($montant): string
{
    return number_format((float) $montant, 0, ',', ' ') . ' FCFA';
}

/** Formatage numérique simple (sans devise). */
function nb($n): string
{
    return number_format((float) $n, 0, ',', ' ');
}

/** Nom du mois en français. */
function moisFr(int $m): string
{
    $mois = [1=>'Janvier','Février','Mars','Avril','Mai','Juin',
             'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    return $mois[$m] ?? (string) $m;
}

/** Date lisible en français. */
function dateFr(?string $date, bool $withTime = false): string
{
    if (!$date) return '—';
    $ts = strtotime($date);
    if ($ts === false) return '—';
    $fmt = $withTime ? 'd/m/Y à H\hi' : 'd/m/Y';
    return date($fmt, $ts);
}

/** Jeton CSRF (génération / récupération). */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Champ caché CSRF prêt à insérer dans un formulaire. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Vérifie le jeton CSRF d'une requête POST. */
function csrf_verify(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
}

/** Message flash (stocké en session, affiché une fois). */
function flash(string $type, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['_flash'][$type] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$type] ?? null;
    unset($_SESSION['_flash'][$type]);
    return $msg;
}

/** Vieille valeur d'un champ (après erreur de validation). */
function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/** Récupère un paramètre GET/POST nettoyé. */
function input(string $key, $default = null)
{
    $val = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($val) ? trim($val) : $val;
}

/** Icône SVG inline (trait fin, style fintech). */
function ic(string $name): string
{
    static $p = [
        'grid'    => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
        'layers'  => '<path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
        'users'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'wallet'  => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2"/><path d="M21 12v-2a1 1 0 0 0-1-1h-5a3 3 0 0 0 0 6h5a1 1 0 0 0 1-1Z"/>',
        'trophy'  => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
        'user'    => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
        'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu'    => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'plus'    => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'edit'    => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'trash'   => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'eye'     => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'back'    => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'download'=> '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'print'   => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'cash'    => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
        'calendar'=> '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'key'     => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 8.8-8.8"/><path d="m17 6 3 3"/><path d="m15 8 2 2"/>',
    ];
    $body = $p[$name] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

/* --- Polyfills mbstring (sécurité si l'extension est absente) --- */
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($s, $enc = null) { return strtoupper((string) $s); }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($s, $enc = null) { return strtolower((string) $s); }
}
if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $length = null, $enc = null) {
        return $length === null ? substr((string) $s, $start) : substr((string) $s, $start, $length);
    }
}
if (!function_exists('mb_strwidth')) {
    function mb_strwidth($s, $enc = null) { return strlen((string) $s); }
}
if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($s, $start, $width, $trim = '', $enc = null) {
        $s = (string) $s;
        if (strlen($s) <= $width) return substr($s, $start);
        return substr($s, $start, max(0, $width - strlen($trim))) . $trim;
    }
}
