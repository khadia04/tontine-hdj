<?php
/**
 * TONTINE HDJ — Routeur MVC léger
 *
 * Format des routes : controleur/action/param1/param2...
 * Exemple : tontines/edit/4  ->  TontineController::edit(4)
 *
 * Fonctionne avec des URLs propres (via .htaccess) ou en repli ?r=route.
 */

class App
{
    /** Contrôleurs autorisés (segment URL => classe). */
    private array $controllers = [
        'auth'        => 'AuthController',
        'dashboard'   => 'DashboardController',
        'tontines'    => 'TontineController',
        'membres'     => 'MembreController',
        'cotisations' => 'CotisationController',
        'tirages'     => 'TirageController',
        'exports'     => 'ExportController',
        'utilisateurs'=> 'UserController',
        'profil'      => 'ProfilController',
    ];

    public function run(): void
    {
        $route = $this->resolveRoute();
        $segments = $route === '' ? [] : explode('/', $route);

        // Routes spéciales (raccourcis)
        $first = $segments[0] ?? '';
        if ($first === '' || $first === 'login') {
            (new AuthController())->showLogin();
            return;
        }
        if ($first === 'logout') {
            (new AuthController())->logout();
            return;
        }

        // Détermination contrôleur / action / params
        $ctrlKey = $segments[0] ?? 'dashboard';
        $action  = $segments[1] ?? 'index';
        $params  = array_slice($segments, 2);

        if (!isset($this->controllers[$ctrlKey])) {
            $this->notFound();
            return;
        }

        $class = $this->controllers[$ctrlKey];
        if (!class_exists($class) || !method_exists($class, $action)) {
            $this->notFound();
            return;
        }

        $controller = new $class();
        call_user_func_array([$controller, $action], $params);
    }

    /** Détecte la route demandée. */
    private function resolveRoute(): string
    {
        // Repli explicite
        if (!empty($_GET['r'])) {
            return trim((string) $_GET['r'], '/');
        }

        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = rtrim(BASE_URL, '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = trim($uri, '/');

        // Ignore le nom de script éventuel (index.php)
        if (str_starts_with($uri, 'index.php')) {
            $uri = ltrim(substr($uri, strlen('index.php')), '/');
        }
        return $uri;
    }

    private function notFound(): void
    {
        http_response_code(404);
        if (is_file(VIEW_PATH . '/errors/404.php')) {
            require VIEW_PATH . '/errors/404.php';
        } else {
            echo '404 — Page introuvable';
        }
    }
}
