<?php
/**
 * TONTINE HDJ — Contrôleur du profil (changement de mot de passe)
 */

class ProfilController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('profil/index', ['title' => 'Mon profil']);
    }

    public function password(): void
    {
        Auth::require();
        $this->requirePost();

        $actuel   = (string) input('actuel');
        $nouveau  = (string) input('nouveau');
        $confirm  = (string) input('confirm');

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([Auth::id()]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($actuel, $user['password'])) {
            flash('error', 'Mot de passe actuel incorrect.');
            redirect('profil');
        }
        if (strlen($nouveau) < 6) {
            flash('error', 'Le nouveau mot de passe doit comporter au moins 6 caractères.');
            redirect('profil');
        }
        if ($nouveau !== $confirm) {
            flash('error', 'La confirmation ne correspond pas.');
            redirect('profil');
        }

        $this->db->prepare("UPDATE users SET password = ? WHERE id = ?")
                 ->execute([password_hash($nouveau, PASSWORD_DEFAULT), Auth::id()]);

        flash('success', 'Mot de passe modifié avec succès.');
        redirect('profil');
    }
}
