<?php
/**
 * TONTINE HDJ — Contrôleur des tirages
 */

class TirageController extends Controller
{
    public function index(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $eligibles = Membre::eligibles($tontineId);
        $tirages   = Tirage::byTontine($tontineId);
        $stats     = Tontine::stats($tontineId);

        // Détails de chaque tirage (pour l'historique)
        $historique = [];
        foreach ($tirages as $tr) {
            $historique[] = ['tirage' => $tr, 'details' => Tirage::details((int) $tr['id'])];
        }

        $this->view('tirages/index', [
            'title'        => 'Tirage — ' . $tontine['nom'],
            'tontine'      => $tontine,
            'eligibles'    => $eligibles,
            'historique'   => $historique,
            'stats'        => $stats,
            'comboExiste'  => Tirage::trouverCombinaison($eligibles, (int) $tontine['montant_mensuel']) !== null,
        ]);
    }

    /**
     * Exécute le tirage (AJAX) : constitue un LOT de gagnants dont les gains
     * totalisent le pot mensuel, l'enregistre, marque les gagnants, renvoie le JSON.
     */
    public function run(): void
    {
        Auth::require();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            $this->json(['ok' => false, 'message' => 'Requête invalide.'], 419);
        }

        $tontineId = (int) input('tontine_id');
        $tontine = Tontine::find($tontineId);
        if (!$tontine) {
            $this->json(['ok' => false, 'message' => 'Tontine introuvable.'], 404);
        }
        if ($tontine['statut'] !== 'active') {
            $this->json(['ok' => false, 'message' => 'Cette tontine n\'est pas active.'], 422);
        }

        $mois  = (int) input('mois', date('n'));
        $annee = (int) input('annee', date('Y'));
        $cible = (int) $tontine['montant_mensuel'];

        $eligibles = Membre::eligibles($tontineId);
        if (empty($eligibles)) {
            $this->json(['ok' => false, 'message' => 'Aucun membre éligible : tous ont déjà gagné.'], 422);
        }

        $combo = Tirage::trouverCombinaison($eligibles, $cible);
        if ($combo === null) {
            $this->json([
                'ok'      => false,
                'message' => 'Impossible de constituer un lot dont la somme des gains '
                           . 'égale exactement ' . fcfa($cible) . ' avec les membres restants.',
            ], 422);
        }

        try {
            $tirageId = Tirage::enregistrer($tontineId, $mois, $annee, $combo);
        } catch (Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Erreur lors de l\'enregistrement du tirage.'], 500);
        }

        $gagnants = array_map(static fn($g) => [
            'id'     => (int) $g['id'],
            'nom'    => $g['nom_complet'],
            'gain'   => (int) $g['gain'],
            'gain_f' => fcfa((int) $g['gain']),
            'photo'  => $g['photo'] ?? null,
        ], $combo);

        $this->json([
            'ok'         => true,
            'tirage_id'  => $tirageId,
            'total'      => fcfa($cible),
            'total_raw'  => $cible,
            'nb'         => count($gagnants),
            'periode'    => moisFr($mois) . ' ' . $annee,
            'date_label' => dateFr(date('Y-m-d H:i:s'), true),
            'gagnants'   => $gagnants,
        ]);
    }

    public function show(string $id = '0'): void
    {
        Auth::require();
        $tirage = Tirage::find((int) $id);
        if (!$tirage) { flash('error', 'Tirage introuvable.'); redirect('tontines'); }
        $tontine = Tontine::find((int) $tirage['tontine_id']);
        if (!$tontine) { flash('error', 'Accès non autorisé.'); redirect('tontines'); }

        $this->view('tirages/show', [
            'title'   => 'Procès-verbal du tirage',
            'tirage'  => $tirage,
            'tontine' => $tontine,
            'details' => Tirage::details((int) $id),
        ], null); // pas de layout : page imprimable
    }
}
