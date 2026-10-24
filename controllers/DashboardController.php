<?php
/**
 * TONTINE HDJ — Contrôleur du tableau de bord
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::require();
        $db = $this->db;
        $uid = (int) Auth::id();
        // Sous-requête : identifiants des tontines de l'utilisateur connecté.
        $mesTontines = "SELECT id FROM tontines WHERE user_id = " . $uid;

        // --- Indicateurs (limités aux tontines de l'utilisateur) ---
        $nbTontines = (int) $db->query("SELECT COUNT(*) FROM tontines WHERE user_id=$uid")->fetchColumn();
        $nbActives  = (int) $db->query("SELECT COUNT(*) FROM tontines WHERE statut='active' AND user_id=$uid")->fetchColumn();
        $nbMembres  = (int) $db->query("SELECT COUNT(*) FROM membres WHERE tontine_id IN ($mesTontines)")->fetchColumn();
        $nbGagnants = (int) $db->query("SELECT COUNT(*) FROM membres WHERE a_gagne=1 AND tontine_id IN ($mesTontines)")->fetchColumn();
        $nbRestants = $nbMembres - $nbGagnants;

        $montantMois = (int) $db->query(
            "SELECT COALESCE(SUM(montant_mensuel),0) FROM tontines WHERE statut='active' AND user_id=$uid"
        )->fetchColumn();

        $totalDistribue = (int) $db->query(
            "SELECT COALESCE(SUM(montant_distribue),0) FROM tirages WHERE tontine_id IN ($mesTontines)"
        )->fetchColumn();

        // --- Listes récentes ---
        $dernieresCotis   = Cotisation::dernieres(6, $uid);
        $derniersGagnants = Tirage::derniersGagnants(6, $uid);
        $tontinesActives  = $db->query(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM membres m WHERE m.tontine_id=t.id) AS nb_membres,
                    (SELECT COUNT(*) FROM membres m WHERE m.tontine_id=t.id AND m.a_gagne=1) AS nb_gagnants
             FROM tontines t WHERE t.statut='active' AND t.user_id=$uid ORDER BY t.created_at DESC LIMIT 4"
        )->fetchAll();

        // --- Données graphiques (limitées à l'utilisateur) ---
        $catRows = $db->query(
            "SELECT cotisation_mensuelle AS cat, COUNT(*) AS n
             FROM membres WHERE tontine_id IN ($mesTontines)
             GROUP BY cotisation_mensuelle ORDER BY cotisation_mensuelle"
        )->fetchAll();
        $catLabels = array_map(fn($r) => nb($r['cat']), $catRows);
        $catData   = array_map(fn($r) => (int) $r['n'], $catRows);

        $annee = (int) date('Y');
        $paieParMois = array_fill(1, 12, 0);
        $rows = $db->prepare(
            "SELECT mois, COALESCE(SUM(montant),0) AS total FROM cotisations
             WHERE annee=? AND statut='paye' AND tontine_id IN ($mesTontines) GROUP BY mois"
        );
        $rows->execute([$annee]);
        foreach ($rows->fetchAll() as $r) {
            $paieParMois[(int) $r['mois']] = (int) $r['total'];
        }

        $progRows = $db->query(
            "SELECT t.nom,
                    (SELECT COUNT(*) FROM membres m WHERE m.tontine_id=t.id) AS total,
                    (SELECT COUNT(*) FROM membres m WHERE m.tontine_id=t.id AND m.a_gagne=1) AS gagnes
             FROM tontines t WHERE t.user_id=$uid ORDER BY t.created_at DESC LIMIT 6"
        )->fetchAll();

        $this->view('dashboard/index', [
            'title'            => 'Tableau de bord',
            'nbTontines'       => $nbTontines,
            'nbActives'        => $nbActives,
            'nbMembres'        => $nbMembres,
            'nbGagnants'       => $nbGagnants,
            'nbRestants'       => $nbRestants,
            'montantMois'      => $montantMois,
            'totalDistribue'   => $totalDistribue,
            'dernieresCotis'   => $dernieresCotis,
            'derniersGagnants' => $derniersGagnants,
            'tontinesActives'  => $tontinesActives,
            'catLabels'        => $catLabels,
            'catData'          => $catData,
            'paieParMois'      => array_values($paieParMois),
            'progRows'         => $progRows,
        ]);
    }
}
