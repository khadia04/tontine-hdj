<?php
/**
 * TONTINE HDJ — Contrôleur de base
 */

class Controller
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Rend une vue dans le layout principal.
     * @param string $view   ex: 'dashboard/index'
     * @param array  $data   variables passées à la vue
     * @param string $layout 'app' | 'auth' | null (aucun)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = VIEW_PATH . '/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            exit("Vue introuvable : " . e($view));
        }

        // Capture le contenu de la vue
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }
        require $layoutFile;
    }

    /** Réponse JSON (pour les appels AJAX). */
    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Valide la requête POST (méthode + CSRF). */
    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            http_response_code(419);
            flash('error', 'Session expirée ou requête invalide. Réessayez.');
            redirect('dashboard');
        }
    }
}
