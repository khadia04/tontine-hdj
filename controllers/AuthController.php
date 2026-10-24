<?php
/**
 * TONTINE HDJ — Contrôleur d'authentification
 */

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $this->view('auth/login', [
            'title' => 'Connexion',
        ], 'auth');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            flash('error', 'Session expirée. Veuillez réessayer.');
            redirect('login');
        }

        $username = (string) input('username');
        $password = (string) input('password');

        if ($username === '' || $password === '') {
            flash('error', 'Veuillez renseigner vos identifiants.');
            redirect('login');
        }

        if (Auth::attempt($username, $password)) {
            flash('success', 'Bienvenue, ' . (Auth::user()['name'] ?? '') . ' !');
            redirect('dashboard');
        }

        flash('error', 'Identifiants incorrects.');
        redirect('login');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }
}
