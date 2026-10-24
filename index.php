<?php
/**
 * TONTINE HDJ — Point d'entrée unique (front controller)
 * Hôpital Dalal Jamm
 */

require_once __DIR__ . '/config/config.php';

// Démarrage de la session sécurisée
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => BASE_URL,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Chargement du noyau
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Controller.php';

// Autoload simple des modèles et contrôleurs
spl_autoload_register(function (string $class): void {
    foreach (['controllers', 'models', 'core'] as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/core/App.php';

// Lancement
(new App())->run();
