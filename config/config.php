<?php
/**
 * TONTINE HDJ — Configuration générale
 * Hôpital Dalal Jamm (HDJ)
 */

// Affichage des erreurs (mettre à false en production)
define('APP_DEBUG', true);

// Identité de l'application
define('APP_NAME', 'TONTINE HDJ');
define('APP_DESC', 'Plateforme de gestion des tontines — Hôpital Dalal Jamm');

// --- Base de données ---
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'tontine_hdj');
define('DB_USER', 'root');      // utilisateur XAMPP par défaut
define('DB_PASS', '');          // mot de passe XAMPP par défaut (vide)
define('DB_CHARSET', 'utf8mb4');

// --- URL de base ---
// Détection automatique du dossier d'installation (ex: /tontine-hdj)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', $scriptDir === '' ? '/' : $scriptDir . '/');

// --- Chemins ---
define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/views');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// --- Sécurité session ---
define('SESSION_NAME', 'TONTINE_HDJ_SESSID');
define('SESSION_LIFETIME', 60 * 60 * 4); // 4 heures

// --- Compte administrateur par défaut (créé au 1er lancement) ---
define('DEFAULT_ADMIN_NAME', 'Super Administrateur');
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'Admin@2026');

// --- Fuseau horaire ---
date_default_timezone_set('Africa/Dakar');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
