<?php
/**
 * TONTINE HDJ — Contrôleur des membres (CRUD)
 */

class MembreController extends Controller
{
    public function index(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $search = (string) input('q', '');
        $this->view('membres/index', [
            'title'   => 'Membres — ' . $tontine['nom'],
            'tontine' => $tontine,
            'membres' => Membre::byTontine($tontineId, $search),
            'search'  => $search,
        ]);
    }

    public function create(string $tontineId = '0'): void
    {
        Auth::require();
        $tontine = Tontine::find((int) $tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $this->view('membres/form', [
            'title'   => 'Nouveau membre',
            'tontine' => $tontine,
            'membre'  => null,
        ]);
    }

    public function store(): void
    {
        Auth::require();
        $this->requirePost();

        $tontineId = (int) input('tontine_id');
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $data = $this->validate($tontineId);
        if ($data === null) { set_old($_POST); redirect('membres/create/' . $tontineId); }

        $photo = $this->handlePhoto();
        if ($photo !== null) $data['photo'] = $photo;

        Membre::create($data, (int) $tontine['duree']);
        Tontine::recalculerPot($tontineId);
        flash('success', 'Membre ajouté. Gain calculé automatiquement : '
            . fcfa((int) $data['cotisation_mensuelle'] * (int) $tontine['duree']) . '.');
        clear_old();
        redirect('membres/index/' . $tontineId);
    }

    public function edit(string $id = '0'): void
    {
        Auth::require();
        $membre = Membre::find((int) $id);
        if (!$membre) { flash('error', 'Membre introuvable.'); redirect('tontines'); }
        $tontine = Tontine::find((int) $membre['tontine_id']);
        if (!$tontine) { flash('error', 'Accès non autorisé.'); redirect('tontines'); }

        $this->view('membres/form', [
            'title'   => 'Modifier le membre',
            'tontine' => $tontine,
            'membre'  => $membre,
        ]);
    }

    public function update(): void
    {
        Auth::require();
        $this->requirePost();

        $id = (int) input('id');
        $membre = Membre::find($id);
        if (!$membre) { flash('error', 'Membre introuvable.'); redirect('tontines'); }
        $tontine = Tontine::find((int) $membre['tontine_id']);
        if (!$tontine) { flash('error', 'Accès non autorisé.'); redirect('tontines'); }

        $data = $this->validate((int) $membre['tontine_id']);
        if ($data === null) { set_old($_POST); redirect('membres/edit/' . $id); }

        $photo = $this->handlePhoto();
        if ($photo !== null) {
            $data['photo'] = $photo;
            if (!empty($membre['photo'])) @unlink(UPLOAD_PATH . '/' . $membre['photo']);
        }

        Membre::update($id, $data, (int) $tontine['duree']);
        Tontine::recalculerPot((int) $membre['tontine_id']);
        flash('success', 'Membre mis à jour.');
        clear_old();
        redirect('membres/index/' . $membre['tontine_id']);
    }

    public function delete(): void
    {
        Auth::require();
        $this->requirePost();
        $id = (int) input('id');
        $membre = Membre::find($id);
        if ($membre && Tontine::find((int) $membre['tontine_id'])) {
            if (!empty($membre['photo'])) @unlink(UPLOAD_PATH . '/' . $membre['photo']);
            Membre::delete($id);
            Tontine::recalculerPot((int) $membre['tontine_id']);
            flash('success', 'Membre supprimé.');
            redirect('membres/index/' . $membre['tontine_id']);
        }
        redirect('tontines');
    }

    private function validate(int $tontineId): ?array
    {
        $nom   = (string) input('nom_complet');
        $cotis = (int) input('cotisation_mensuelle');

        if ($nom === '') { flash('error', 'Le nom complet est obligatoire.'); return null; }
        if ($cotis <= 0) { flash('error', 'La cotisation mensuelle doit être positive.'); return null; }

        return [
            'tontine_id'           => $tontineId,
            'nom_complet'          => $nom,
            'telephone'            => (string) input('telephone'),
            'adresse'              => (string) input('adresse'),
            'cotisation_mensuelle' => $cotis,
            'date_inscription'     => (string) input('date_inscription'),
        ];
    }

    /** Gère l'upload d'une photo ; renvoie le nom de fichier ou null. */
    private function handlePhoto(): ?string
    {
        if (empty($_FILES['photo']['name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $tmp  = $_FILES['photo']['tmp_name'];
        $size = (int) $_FILES['photo']['size'];
        if ($size > 3 * 1024 * 1024) { flash('error', 'Photo trop lourde (max 3 Mo).'); return null; }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp);
        if (!isset($allowed[$mime])) { flash('error', 'Format de photo non supporté.'); return null; }

        if (!is_dir(UPLOAD_PATH)) @mkdir(UPLOAD_PATH, 0775, true);
        $name = 'membre_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        if (move_uploaded_file($tmp, UPLOAD_PATH . '/' . $name)) {
            return $name;
        }
        return null;
    }
}
