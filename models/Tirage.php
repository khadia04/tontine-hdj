<?php
/**
 * TONTINE HDJ — Moteur de tirage (v4 — équilibre strict des tailles de lots)
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * RÈGLE ABSOLUE
 * ──────────────────────────────────────────────────────────────────────────────
 * Chaque lot doit avoir le même nombre de membres (±1 maximum).
 * Avec 90 membres / 10 lots = 9 membres par lot exactement.
 *
 * PLAN DE RÉPARTITION (calculé une fois, garanti mathématiquement)
 * ──────────────────────────────────────────────────────────────────────────────
 * Chaque lot reçoit un quota EXACT de membres par catégorie.
 * La randomisation porte sur le CHOIX des membres dans chaque catégorie,
 * pas sur le nombre — ce qui garantit à la fois équité et équilibre.
 *
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ PLAN A — Tontine vierge (aucun tirage effectué)                         │
 * │ 10 lots × 9 membres × 5 250 000 FCFA                                    │
 * ├──────┬────────┬──────┬──────┬──────┬──────┬────────┬────────────────────┤
 * │ Lot  │ Spéc.  │ 100k │  75k │  50k │  25k │  Total │ Diversité          │
 * ├──────┼────────┼──────┼──────┼──────┼──────┼────────┼────────────────────┤
 * │  1   │ 200k   │  0   │  1   │  3   │  4   │   9    │ 200k,75k,50k,25k  │
 * │  2   │ 150k   │  0   │  1   │  5   │  2   │   9    │ 150k,75k,50k,25k  │
 * │  3   │ 150k   │  0   │  1   │  5   │  2   │   9    │ 150k,75k,50k,25k  │
 * │  4   │ 125k   │  0   │  1   │  6   │  1   │   9    │ 125k,75k,50k,25k  │
 * │  5   │  —     │  1   │  2   │  5   │  1   │   9    │ 100k,75k,50k,25k  │
 * │  6   │  —     │  1   │  2   │  5   │  1   │   9    │ 100k,75k,50k,25k  │
 * │  7   │  —     │  2   │  1   │  4   │  2   │   9    │ 100k,75k,50k,25k  │
 * │  8   │  —     │  3   │  1   │  1   │  4   │   9    │ 100k,75k,50k,25k  │
 * │  9   │  —     │  3   │  1   │  1   │  4   │   9    │ 100k,75k,50k,25k  │
 * │ 10   │  —     │  3   │  1   │  1   │  4   │   9    │ 100k,75k,50k,25k  │
 * ├──────┴────────┼──────┼──────┼──────┼──────┼────────┼────────────────────┤
 * │    TOTAUX     │  13  │  12  │  36  │  25  │   90   │                    │
 * └───────────────┴──────┴──────┴──────┴──────┴────────┴────────────────────┘
 *
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ PLAN B — Lot 1 déjà effectué (6 membres: 200k+100k+75k+75k+50k+25k)    │
 * │ 84 membres restants → 9 lots (6×9 + 3×10 membres)                       │
 * ├──────┬────────┬──────┬──────┬──────┬──────┬────────┬────────────────────┤
 * │ Lot  │ Spéc.  │ 100k │  75k │  50k │  25k │  Total │                    │
 * ├──────┼────────┼──────┼──────┼──────┼──────┼────────┼────────────────────┤
 * │  2   │ 150k   │  1   │  1   │  2   │  4   │   9    │                    │
 * │  3   │ 150k   │  1   │  1   │  2   │  4   │   9    │                    │
 * │  4   │ 125k   │  1   │  1   │  3   │  3   │   9    │                    │
 * │  5   │  —     │  1   │  1   │  6   │  2   │  10    │                    │
 * │  6   │  —     │  1   │  1   │  6   │  2   │  10    │                    │
 * │  7   │  —     │  1   │  1   │  6   │  2   │  10    │                    │
 * │  8   │  —     │  1   │  2   │  5   │  1   │   9    │                    │
 * │  9   │  —     │  2   │  1   │  4   │  2   │   9    │                    │
 * │ 10   │  —     │  3   │  1   │  1   │  4   │   9    │                    │
 * ├──────┴────────┼──────┼──────┼──────┼──────┼────────┼────────────────────┤
 * │    TOTAUX     │  12  │  10  │  35  │  24  │   84   │ (restants OK)      │
 * └───────────────┴──────┴──────┴──────┴──────┴────────┴────────────────────┘
 *
 * UTILISATION DANS LE CONTROLLER
 * ──────────────────────────────────────────────────────────────────────────────
 *   $eligibles = Membre::eligibles($tontineId);
 *   $pot       = (int) Tontine::find($tontineId)['montant_mensuel'];
 *   $gagnants  = Tirage::trouverCombinaison($eligibles, $pot, $tontineId);
 *   if ($gagnants) {
 *       $id = Tirage::enregistrer($tontineId, $mois, $annee, $gagnants);
 *   }
 * ══════════════════════════════════════════════════════════════════════════════
 */
class Tirage
{
    // =========================================================================
    // PLANS DE RÉPARTITION
    // =========================================================================

    /**
     * Plan A — tontine vierge : 10 lots × 9 membres.
     * À utiliser quand aucun tirage n'a encore eu lieu.
     */
    private const PLAN_A = [
        1  => ['sp' => 200000, 'n100' => 0, 'n75' => 1, 'n50' => 3, 'n25' => 4],
        2  => ['sp' => 150000, 'n100' => 0, 'n75' => 1, 'n50' => 5, 'n25' => 2],
        3  => ['sp' => 150000, 'n100' => 0, 'n75' => 1, 'n50' => 5, 'n25' => 2],
        4  => ['sp' => 125000, 'n100' => 0, 'n75' => 1, 'n50' => 6, 'n25' => 1],
        5  => ['sp' => null,   'n100' => 1, 'n75' => 2, 'n50' => 5, 'n25' => 1],
        6  => ['sp' => null,   'n100' => 1, 'n75' => 2, 'n50' => 5, 'n25' => 1],
        7  => ['sp' => null,   'n100' => 2, 'n75' => 1, 'n50' => 4, 'n25' => 2],
        8  => ['sp' => null,   'n100' => 3, 'n75' => 1, 'n50' => 1, 'n25' => 4],
        9  => ['sp' => null,   'n100' => 3, 'n75' => 1, 'n50' => 1, 'n25' => 4],
        10 => ['sp' => null,   'n100' => 3, 'n75' => 1, 'n50' => 1, 'n25' => 4],
    ];

    /**
     * Plan B — lot 1 déjà effectué avec 6 membres (200k+100k+75k+75k+50k+25k).
     * 84 membres restants sur 9 lots : 6 lots×9 + 3 lots×10, écart max = 1.
     */
    private const PLAN_B = [
        2  => ['sp' => 150000, 'n100' => 1, 'n75' => 1, 'n50' => 2, 'n25' => 4],
        3  => ['sp' => 150000, 'n100' => 1, 'n75' => 1, 'n50' => 2, 'n25' => 4],
        4  => ['sp' => 125000, 'n100' => 1, 'n75' => 1, 'n50' => 3, 'n25' => 3],
        5  => ['sp' => null,   'n100' => 1, 'n75' => 1, 'n50' => 6, 'n25' => 2],
        6  => ['sp' => null,   'n100' => 1, 'n75' => 1, 'n50' => 6, 'n25' => 2],
        7  => ['sp' => null,   'n100' => 1, 'n75' => 1, 'n50' => 6, 'n25' => 2],
        8  => ['sp' => null,   'n100' => 1, 'n75' => 2, 'n50' => 5, 'n25' => 1],
        9  => ['sp' => null,   'n100' => 2, 'n75' => 1, 'n50' => 4, 'n25' => 2],
        10 => ['sp' => null,   'n100' => 3, 'n75' => 1, 'n50' => 1, 'n25' => 4],
    ];

    // =========================================================================
    // ACCÈS BDD
    // =========================================================================
    private static function db(): PDO
    {
        return Database::connection();
    }

    // =========================================================================
    // MÉLANGE CSPRNG (Fisher-Yates + random_int)
    // =========================================================================
    private static function melangeSecurity(array &$arr): void
    {
        for ($i = count($arr) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    // =========================================================================
    // POINT D'ENTRÉE PUBLIC
    // =========================================================================
    /**
     * Génère le lot du mois.
     *
     * Le numéro de lot est déduit automatiquement du nombre de tirages déjà
     * enregistrés. Le plan (A ou B) est sélectionné automatiquement selon
     * l'historique réel de la tontine.
     *
     * @param array $eligibles  Membres éligibles : id, cotisation_mensuelle, gain
     * @param int   $pot        Montant cible (5 250 000)
     * @param int   $tontineId  ID de la tontine (requis pour auto-détecter le lot)
     *
     * @return array|null  Gagnants triés par gain décroissant, ou null si impossible.
     */
    public static function trouverCombinaison(
        array $eligibles,
        int   $pot,
        int   $tontineId = 0
    ): ?array {
        if ($pot <= 0 || empty($eligibles)) {
            return null;
        }

        // Déduire le numéro du lot en cours
        $tiragePrecedents = $tontineId > 0 ? count(self::byTontine($tontineId)) : 0;
        $numeroLot        = $tiragePrecedents + 1;

        // Choisir le plan selon l'historique de la tontine
        $plan = self::choisirPlan($tiragePrecedents);
        if (!isset($plan[$numeroLot])) {
            return null; // Tontine terminée ou hors plan
        }

        // Classer les membres par catégorie et mélanger (CSPRNG)
        $categories = self::classerEtMelangerParCategorie($eligibles);

        // Construire le lot selon le plan
        $lot = self::construireLotSelonPlan($categories, $plan[$numeroLot]);

        if ($lot === null) {
            // Fallback : le plan ne peut pas être respecté (données inhabituelles)
            $lot = self::construireLotFallback($categories, $pot, $numeroLot);
        }

        if ($lot === null) {
            return null;
        }

        // Vérification de sécurité : la somme doit être exacte
        if ((int) array_sum(array_column($lot, 'gain')) !== $pot) {
            return null;
        }

        usort($lot, static fn($a, $b) => (int)$b['gain'] <=> (int)$a['gain']);
        return $lot;
    }

    // =========================================================================
    // PRÉ-CONTRÔLE UI
    // =========================================================================
    /**
     * Retourne true si un tirage peut encore être lancé.
     * Utilisé pour activer/désactiver le bouton « Lancer le tirage ».
     */
    public static function combinaisonExiste(int $tontineId): bool
    {
        $tontine = Tontine::find($tontineId);
        if (!$tontine) {
            return false;
        }
        $eligibles      = Membre::eligibles($tontineId);
        $tiragePrecedents = count(self::byTontine($tontineId));
        $numeroLot        = $tiragePrecedents + 1;
        $plan             = self::choisirPlan($tiragePrecedents);
        return !empty($eligibles) && isset($plan[$numeroLot]);
    }

    // =========================================================================
    // PERSISTANCE
    // =========================================================================
    /**
     * Enregistre un tirage et marque les gagnants (transaction).
     *
     * @return int  ID du tirage créé.
     * @throws Throwable  En cas d'erreur BDD.
     */
    public static function enregistrer(
        int   $tontineId,
        int   $mois,
        int   $annee,
        array $gagnants
    ): int {
        $db = self::db();
        $db->beginTransaction();
        try {
            $montant = (int) array_sum(array_column($gagnants, 'gain'));

            $stmtTirage = $db->prepare(
                "INSERT INTO tirages (tontine_id, mois, annee, montant_distribue, nb_gagnants)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmtTirage->execute([$tontineId, $mois, $annee, $montant, count($gagnants)]);
            $tirageId = (int) $db->lastInsertId();

            $stmtDetail  = $db->prepare(
                "INSERT INTO tirage_details (tirage_id, membre_id, gain) VALUES (?, ?, ?)"
            );
            $stmtMarquer = $db->prepare(
                "UPDATE membres SET a_gagne = 1 WHERE id = ?"
            );

            foreach ($gagnants as $g) {
                $stmtDetail->execute([$tirageId, (int)$g['id'], (int)$g['gain']]);
                $stmtMarquer->execute([(int)$g['id']]);
            }

            $db->commit();
            return $tirageId;

        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // =========================================================================
    // LECTURES BDD
    // =========================================================================
    public static function byTontine(int $tontineId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM tirages WHERE tontine_id = ? ORDER BY date_tirage ASC"
        );
        $stmt->execute([$tontineId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM tirages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function details(int $tirageId): array
    {
        $stmt = self::db()->prepare(
            "SELECT td.*, m.nom_complet, m.telephone, m.photo
             FROM tirage_details td
             JOIN membres m ON m.id = td.membre_id
             WHERE td.tirage_id = ?
             ORDER BY td.gain DESC"
        );
        $stmt->execute([$tirageId]);
        return $stmt->fetchAll();
    }

    public static function derniersGagnants(int $limit = 6, ?int $userId = null): array
    {
        $sql = "SELECT td.gain, td.created_at, m.nom_complet, m.photo, t.nom AS tontine_nom
                FROM tirage_details td
                JOIN membres m ON m.id = td.membre_id
                JOIN tirages tr ON tr.id = td.tirage_id
                JOIN tontines t ON t.id = tr.tontine_id";
        $params = [];
        if ($userId !== null) {
            $sql    .= " WHERE t.user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY td.created_at DESC LIMIT " . (int)$limit;
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // =========================================================================
    // MÉTHODES PRIVÉES — MOTEUR INTERNE
    // =========================================================================

    /**
     * Sélectionne le plan de répartition selon le nombre de tirages déjà faits.
     *
     * Plan A → tontine vierge (0 tirage effectué).
     * Plan B → lot 1 déjà effectué avec 6 membres (composition réelle constatée).
     *
     * Si la tontine a un historique différent (autre nombre de membres au lot 1),
     * le plan A reste le référentiel de départ ; les lots suivants s'adapteront
     * via le fallback greedy si les quotas ne correspondent pas.
     */
    private static function choisirPlan(int $tiragePrecedents): array
    {
        // Lot 1 a déjà été tiré avec 6 membres → utiliser le plan B
        if ($tiragePrecedents >= 1) {
            return self::PLAN_B;
        }
        // Aucun tirage → plan A standard
        return self::PLAN_A;
    }

    /**
     * Classe les membres par cotisation et mélange chaque groupe (CSPRNG).
     *
     * @return array<int, array>  [200000 => [...], 150000 => [...], ...]
     */
    private static function classerEtMelangerParCategorie(array $membres): array
    {
        $categories = [
            200000 => [], 150000 => [], 125000 => [],
            100000 => [], 75000  => [], 50000  => [], 25000 => [],
        ];
        foreach ($membres as $m) {
            $cot = (int)$m['cotisation_mensuelle'];
            if (isset($categories[$cot])) {
                $categories[$cot][] = $m;
            }
        }
        foreach ($categories as &$groupe) {
            self::melangeSecurity($groupe);
        }
        unset($groupe);
        return $categories;
    }

    /**
     * Construit un lot en respectant exactement les quotas du plan.
     *
     * Retourne null si une catégorie n'a pas assez de membres disponibles
     * (déclenche alors le fallback).
     */
    private static function construireLotSelonPlan(array &$categories, array $planLot): ?array
    {
        $lot = [];

        // Membre spécial
        if ($planLot['sp'] !== null) {
            $cot = $planLot['sp'];
            if (empty($categories[$cot])) {
                return null; // Membre spécial épuisé → fallback
            }
            $lot[] = array_shift($categories[$cot]);
        }

        // Quotas par catégorie secondaire
        foreach ([100000 => 'n100', 75000 => 'n75', 50000 => 'n50', 25000 => 'n25'] as $cot => $key) {
            $nb = $planLot[$key];
            if (count($categories[$cot]) < $nb) {
                // Remettre le membre spécial en place avant de partir en fallback
                if ($planLot['sp'] !== null && !empty($lot)) {
                    array_unshift($categories[$planLot['sp']], array_shift($lot));
                }
                return null;
            }
            for ($i = 0; $i < $nb; $i++) {
                $lot[] = array_shift($categories[$cot]);
            }
        }

        return $lot;
    }

    /**
     * Fallback greedy équilibré — utilisé uniquement si le plan ne peut pas
     * être respecté (composition inhabituelle ou historique partiel).
     *
     * Garantit au minimum : membre spécial + diversité + somme exacte.
     */
    private static function construireLotFallback(
        array &$categories,
        int    $pot,
        int    $numeroLot
    ): ?array {
        $lot   = [];
        $somme = 0;

        // Membre spécial selon le numéro de lot
        $priorites = [1 => 200000, 2 => 150000, 3 => 150000, 4 => 125000];
        $spCot = $priorites[$numeroLot] ?? null;
        if ($spCot !== null && !empty($categories[$spCot])) {
            $m = array_shift($categories[$spCot]);
            $lot[]  = $m;
            $somme += (int)$m['gain'];
        }

        // Un représentant de chaque catégorie secondaire
        foreach ([100000, 75000, 50000, 25000] as $cot) {
            if ($somme >= $pot || empty($categories[$cot])) {
                continue;
            }
            if ($somme + (int)$categories[$cot][0]['gain'] <= $pot) {
                $m = array_shift($categories[$cot]);
                $lot[]  = $m;
                $somme += (int)$m['gain'];
            }
        }

        // Greedy décroissant pour combler le solde
        foreach ([200000, 150000, 125000, 100000, 75000, 50000, 25000] as $cot) {
            if ($somme >= $pot) {
                break;
            }
            while (!empty($categories[$cot]) && $somme < $pot) {
                $gain = (int)$categories[$cot][0]['gain'];
                if ($somme + $gain > $pot) {
                    break;
                }
                $lot[]  = array_shift($categories[$cot]);
                $somme += $gain;
            }
        }

        return ($somme === $pot) ? $lot : null;
    }
}