<?php
/**
 * TONTINE HDJ — Contrôleur des cotisations
 */

class CotisationController extends Controller
{
    public function index(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $mois  = (int) input('mois', date('n'));
        $annee = (int) input('annee', date('Y'));
        if ($mois < 1 || $mois > 12) $mois = (int) date('n');

        $lignes     = Cotisation::forPeriod($tontineId, $mois, $annee);
        $totalPaye  = Cotisation::totalPaye($tontineId, $mois, $annee);
        $totalAttendu = array_sum(array_map(fn($l) => (int) $l['cotisation_mensuelle'], $lignes));

        $this->view('cotisations/index', [
            'title'        => 'Cotisations — ' . $tontine['nom'],
            'tontine'      => $tontine,
            'lignes'       => $lignes,
            'mois'         => $mois,
            'annee'        => $annee,
            'totalPaye'    => $totalPaye,
            'totalAttendu' => $totalAttendu,
        ]);
    }

    /** Bascule le statut d'une cotisation (appel AJAX). */
    public function toggle(): void
    {
        Auth::require();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            $this->json(['ok' => false, 'message' => 'Requête invalide.'], 419);
        }

        $membreId  = (int) input('membre_id');
        $tontineId = (int) input('tontine_id');
        $mois      = (int) input('mois');
        $annee     = (int) input('annee');
        $statut    = (string) input('statut');

        if (!Tontine::find($tontineId)) {
            $this->json(['ok' => false, 'message' => 'Accès non autorisé.'], 403);
        }
        if (!in_array($statut, ['paye', 'non_paye'], true)) {
            $this->json(['ok' => false, 'message' => 'Statut invalide.'], 422);
        }
        if (!Membre::find($membreId)) {
            $this->json(['ok' => false, 'message' => 'Membre introuvable.'], 404);
        }

        $res = Cotisation::setStatut($membreId, $tontineId, $mois, $annee, $statut);
        $totalPaye = Cotisation::totalPaye($tontineId, $mois, $annee);

        $this->json([
            'ok'            => true,
            'statut'        => $res['statut'],
            'date_paiement' => $res['date_paiement'] ? dateFr($res['date_paiement'], true) : null,
            'total_paye'    => fcfa($totalPaye),
            'total_paye_raw'=> $totalPaye,
        ]);
    }

    /** Marque plusieurs membres comme « payé » en une fois (appel AJAX). */
    public function bulkPay(): void
    {
        Auth::require();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            $this->json(['ok' => false, 'message' => 'Requête invalide.'], 419);
        }

        $tontineId = (int) input('tontine_id');
        $mois      = (int) input('mois');
        $annee     = (int) input('annee');
        $statut    = (string) input('statut', 'paye');
        if (!in_array($statut, ['paye', 'non_paye'], true)) $statut = 'paye';

        if (!Tontine::find($tontineId)) {
            $this->json(['ok' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $ids = $_POST['membre_ids'] ?? [];
        if (!is_array($ids)) $ids = [$ids];
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            $this->json(['ok' => false, 'message' => 'Aucun membre sélectionné.'], 422);
        }

        $maj = [];
        foreach ($ids as $membreId) {
            if (!Membre::find($membreId)) continue;
            $res = Cotisation::setStatut($membreId, $tontineId, $mois, $annee, $statut);
            $maj[] = [
                'membre_id'     => $membreId,
                'statut'        => $res['statut'],
                'date_paiement' => $res['date_paiement'] ? dateFr($res['date_paiement'], true) : null,
            ];
        }

        $totalPaye = Cotisation::totalPaye($tontineId, $mois, $annee);
        $this->json([
            'ok'             => true,
            'maj'            => $maj,
            'nb'             => count($maj),
            'total_paye'     => fcfa($totalPaye),
            'total_paye_raw' => $totalPaye,
        ]);
    }
}
