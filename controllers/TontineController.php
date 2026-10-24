<?php
/**
 * TONTINE HDJ — Contrôleur des tontines (CRUD)
 */

class TontineController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $this->view('tontines/index', [
            'title'    => 'Tontines',
            'tontines' => Tontine::all(),
        ]);
    }

    public function create(): void
    {
        Auth::require();
        $this->view('tontines/form', [
            'title'   => 'Nouvelle tontine',
            'tontine' => null,
        ]);
    }

    public function store(): void
    {
        Auth::require();
        $this->requirePost();

        $data = $this->validate();
        if ($data === null) {
            set_old($_POST);
            redirect('tontines/create');
        }

        Tontine::create($data);
        flash('success', 'Tontine créée avec succès.');
        clear_old();
        redirect('tontines');
    }

    public function edit(string $id = '0'): void
    {
        Auth::require();
        $tontine = Tontine::find((int) $id);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $this->view('tontines/form', [
            'title'   => 'Modifier la tontine',
            'tontine' => $tontine,
        ]);
    }

    public function update(): void
    {
        Auth::require();
        $this->requirePost();

        $id = (int) input('id');
        if (!Tontine::find($id)) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $data = $this->validate();
        if ($data === null) {
            set_old($_POST);
            redirect('tontines/edit/' . $id);
        }

        Tontine::update($id, $data);
        flash('success', 'Tontine mise à jour.');
        clear_old();
        redirect('tontines/show/' . $id);
    }

    public function delete(): void
    {
        Auth::require();
        $this->requirePost();
        $id = (int) input('id');
        $tontine = Tontine::find($id);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }
        if ($tontine['statut'] === 'active') {
            flash('error', 'Impossible de supprimer une tontine active. Suspendez-la ou marquez-la « Terminée » avant de la supprimer.');
            redirect('tontines');
        }
        Tontine::delete($id);
        flash('success', 'Tontine supprimée.');
        redirect('tontines');
    }

    public function show(string $id = '0'): void
    {
        Auth::require();
        $id = (int) $id;
        $tontine = Tontine::find($id);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $this->view('tontines/show', [
            'title'    => $tontine['nom'],
            'tontine'  => $tontine,
            'stats'    => Tontine::stats($id),
            'membres'  => Membre::byTontine($id),
            'tirages'  => Tirage::byTontine($id),
        ]);
    }

    /** Validation des champs ; renvoie les données nettoyées ou null. */
    private function validate(): ?array
    {
        $nom    = (string) input('nom');
        $duree  = (int) input('duree');
        $statut = (string) input('statut', 'active');

        if ($nom === '') { flash('error', 'Le nom de la tontine est obligatoire.'); return null; }
        if ($duree <= 0) { flash('error', 'La durée doit être supérieure à 0.'); return null; }
        if (!in_array($statut, ['active', 'suspendue', 'terminee'], true)) $statut = 'active';

        $dateDebut = (string) input('date_debut');
        // La date de fin est déduite automatiquement de la date de début + durée.
        $dateFin = Tontine::calculerDateFin($dateDebut, $duree) ?? (string) input('date_fin');

        // Le pot mensuel n'est plus saisi : il est recalculé à partir des
        // cotisations des membres (voir Tontine::recalculerPot). On conserve
        // la valeur existante éventuelle ; 0 pour une nouvelle tontine.
        $potActuel = (int) input('montant_mensuel');

        return [
            'nom'             => $nom,
            'description'     => (string) input('description'),
            'montant_mensuel' => $potActuel,
            'duree'           => $duree,
            'date_debut'      => $dateDebut,
            'date_fin'        => $dateFin,
            'statut'          => $statut,
        ];
    }
}
