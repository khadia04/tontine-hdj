<?php
/** Rapport mensuel imprimable — TONTINE HDJ (layout null) */
$nbPaye = 0; $totalAttendu = 0;
foreach ($lignes as $l) { $totalAttendu += (int)$l['cotisation_mensuelle']; if (($l['statut'] ?? '') === 'paye') $nbPaye++; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rapport <?= e(moisFr($mois) . ' ' . $annee) ?> — <?= e($tontine['nom']) ?></title>
  <style>
    * { font-family: 'Segoe UI', Arial, sans-serif; box-sizing:border-box; }
    body { margin:0; padding:32px; color:#13201b; }
    .hd { display:flex; align-items:center; gap:16px; border-bottom:3px solid #0E6B57; padding-bottom:16px; margin-bottom:22px; }
    .hd img { height:58px; }
    .hd h1 { margin:0; font-size:1.4rem; color:#0E6B57; }
    .hd p { margin:2px 0 0; color:#6b8079; font-size:.9rem; }
    .meta { display:flex; gap:28px; margin-bottom:18px; font-size:.92rem; }
    .meta b { color:#0E6B57; }
    table { width:100%; border-collapse:collapse; font-size:.88rem; }
    th { background:#0E6B57; color:#fff; text-align:left; padding:9px 12px; }
    td { padding:8px 12px; border-bottom:1px solid #e7efec; }
    tr:nth-child(even) td { background:#f8faf9; }
    .paye { color:#0a7d5f; font-weight:700; } .nonpaye { color:#c0392b; font-weight:700; }
    .tot { margin-top:20px; display:flex; gap:16px; }
    .tot div { flex:1; border:1px solid #e7efec; border-radius:12px; padding:14px; }
    .tot .v { font-size:1.3rem; font-weight:800; color:#0E6B57; }
    .sign { margin-top:48px; display:flex; justify-content:space-between; }
    .sign div { width:42%; border-top:1px solid #999; padding-top:6px; text-align:center; font-size:.85rem; color:#555; }
    .btn-print { position:fixed; top:18px; right:18px; background:#0E6B57; color:#fff; border:none; padding:10px 18px; border-radius:100px; cursor:pointer; font-weight:700; }
    @media print { .btn-print { display:none; } body { padding:0; } }
  </style>
</head>
<body>
  <button class="btn-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
  <div class="hd">
    <img src="<?= asset('img/logo-dark.png') ?>" alt="">
    <div>
      <h1>Rapport mensuel des cotisations</h1>
      <p>Hôpital Dalal Jamm — TONTINE HDJ</p>
    </div>
  </div>

  <div class="meta">
    <span>Tontine : <b><?= e($tontine['nom']) ?></b></span>
    <span>Période : <b><?= e(moisFr($mois) . ' ' . $annee) ?></b></span>
    <span>Édité le : <b><?= e(dateFr(date('Y-m-d'))) ?></b></span>
  </div>

  <table>
    <thead><tr><th>#</th><th>Membre</th><th>Téléphone</th><th>Cotisation</th><th>Statut</th><th>Date paiement</th></tr></thead>
    <tbody>
    <?php foreach ($lignes as $i => $l): $st = $l['statut'] ?? 'non_paye'; ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($l['nom_complet']) ?></td>
        <td><?= e($l['telephone'] ?: '—') ?></td>
        <td><?= e(fcfa($l['cotisation_mensuelle'])) ?></td>
        <td class="<?= $st === 'paye' ? 'paye' : 'nonpaye' ?>"><?= $st === 'paye' ? '✅ Payé' : '❌ Non payé' ?></td>
        <td><?= $l['date_paiement'] ? e(dateFr($l['date_paiement'], true)) : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="tot">
    <div><small>Membres</small><div class="v"><?= count($lignes) ?></div></div>
    <div><small>Ont payé</small><div class="v"><?= $nbPaye ?></div></div>
    <div><small>Total encaissé</small><div class="v"><?= e(fcfa($totalPaye)) ?></div></div>
    <div><small>Total attendu</small><div class="v"><?= e(fcfa($totalAttendu)) ?></div></div>
  </div>

  <div class="sign">
    <div>Le Président</div>
    <div>Le Trésorier</div>
  </div>
</body>
</html>
