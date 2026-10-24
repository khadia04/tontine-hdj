<?php
/**
 * TONTINE HDJ — Contrôleur des exports
 *
 * Exports sans dépendance externe (directement exploitables sous XAMPP) :
 *   - Excel : fichiers CSV (UTF-8 BOM) ouverts nativement par Excel/LibreOffice ;
 *   - PDF   : vues HTML optimisées pour l'impression (Imprimer > Enregistrer en PDF).
 *
 * (Possibilité d'évoluer vers DomPDF / PhpSpreadsheet via Composer — voir README.)
 */

class ExportController extends Controller
{
    /** Envoie un tableau en CSV téléchargeable. */
    private function streamCsv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
        fputcsv($out, $headers, ';');
        foreach ($rows as $r) {
            fputcsv($out, $r, ';');
        }
        fclose($out);
        exit;
    }

    /** Export Excel (CSV) des membres d'une tontine. */
    public function membres(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $membres = Membre::byTontine($tontineId);
        $rows = [];
        foreach ($membres as $m) {
            $rows[] = [
                $m['nom_complet'],
                $m['telephone'],
                $m['adresse'],
                nb($m['cotisation_mensuelle']),
                nb($m['gain']),
                $m['a_gagne'] ? 'Gagnant' : 'En attente',
                dateFr($m['date_inscription']),
            ];
        }
        $this->streamCsv(
            'membres_' . $this->slug($tontine['nom']) . '.csv',
            ['Nom complet', 'Téléphone', 'Adresse', 'Cotisation (FCFA)', 'Gain (FCFA)', 'Statut', 'Inscription'],
            $rows
        );
    }

    /** Export Excel (CSV) des cotisations d'une période. */
    public function cotisations(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $mois  = (int) input('mois', date('n'));
        $annee = (int) input('annee', date('Y'));
        $lignes = Cotisation::forPeriod($tontineId, $mois, $annee);

        $rows = [];
        foreach ($lignes as $l) {
            $rows[] = [
                $l['nom_complet'],
                $l['telephone'],
                nb($l['cotisation_mensuelle']),
                ($l['statut'] ?? 'non_paye') === 'paye' ? 'Payé' : 'Non payé',
                $l['date_paiement'] ? dateFr($l['date_paiement'], true) : '',
            ];
        }
        $this->streamCsv(
            'cotisations_' . $this->slug($tontine['nom']) . '_' . moisFr($mois) . '_' . $annee . '.csv',
            ['Membre', 'Téléphone', 'Cotisation (FCFA)', 'Statut', 'Date paiement'],
            $rows
        );
    }

    /** Export Excel (CSV) des tirages d'une tontine. */
    public function tirages(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $rows = [];
        foreach (Tirage::byTontine($tontineId) as $tr) {
            foreach (Tirage::details((int) $tr['id']) as $d) {
                $rows[] = [
                    '#' . $tr['id'],
                    dateFr($tr['date_tirage'], true),
                    moisFr((int) $tr['mois']) . ' ' . $tr['annee'],
                    $d['nom_complet'],
                    nb($d['gain']),
                ];
            }
        }
        $this->streamCsv(
            'tirages_' . $this->slug($tontine['nom']) . '.csv',
            ['Tirage', 'Date', 'Période', 'Gagnant', 'Gain (FCFA)'],
            $rows
        );
    }

    /** Rapport mensuel imprimable (PDF via impression navigateur). */
    public function rapport(string $tontineId = '0'): void
    {
        Auth::require();
        $tontineId = (int) $tontineId;
        $tontine = Tontine::find($tontineId);
        if (!$tontine) { flash('error', 'Tontine introuvable.'); redirect('tontines'); }

        $mois  = (int) input('mois', date('n'));
        $annee = (int) input('annee', date('Y'));

        $this->view('cotisations/rapport', [
            'tontine'      => $tontine,
            'lignes'       => Cotisation::forPeriod($tontineId, $mois, $annee),
            'mois'         => $mois,
            'annee'        => $annee,
            'totalPaye'    => Cotisation::totalPaye($tontineId, $mois, $annee),
        ], null);
    }

    private function slug(string $s): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/[^a-zA-Z0-9]+/', '_', $s);
        return trim(strtolower($s), '_');
    }
}
