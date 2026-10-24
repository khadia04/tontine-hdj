<?php
/**
 * TONTINE HDJ — Gestion des présidents de tontine (réservé au super admin)
 */

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireSuperAdmin();
        $this->view('users/index', [
            'title' => 'Présidents de tontine',
            'users' => User::all(),
        ]);
    }

    public function store(): void
    {
        Auth::requireSuperAdmin();
        $this->requirePost();

        $name     = trim((string) input('name'));
        $username = trim((string) input('username'));
        $password = (string) input('password');

        if ($name === '' || $username === '') {
            flash('error', "Le nom et l'identifiant sont obligatoires.");
            redirect('utilisateurs');
        }
        if (strlen($password) < 6) {
            flash('error', "Le mot de passe doit contenir au moins 6 caractères.");
            redirect('utilisateurs');
        }
        if (User::usernameExists($username)) {
            flash('error', "Cet identifiant est déjà utilisé.");
            redirect('utilisateurs');
        }

        User::create([
            'name'     => $name,
            'username' => $username,
            'password' => $password,
            'role'     => 'president',
        ]);
        flash('success', "Président « {$name} » créé avec succès.");
        redirect('utilisateurs');
    }

    public function delete(): void
    {
        Auth::requireSuperAdmin();
        $this->requirePost();

        $id = (int) input('id');
        $u  = User::find($id);
        if (!$u) {
            flash('error', 'Utilisateur introuvable.');
            redirect('utilisateurs');
        }
        if ($u['role'] === 'super_admin') {
            flash('error', 'Impossible de supprimer un super administrateur.');
            redirect('utilisateurs');
        }
        if ($id === Auth::id()) {
            flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            redirect('utilisateurs');
        }
        User::delete($id);
        flash('success', 'Président supprimé.');
        redirect('utilisateurs');
    }
}
