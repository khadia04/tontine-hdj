<?php
/** Procès-verbal de tirage imprimable — TONTINE HDJ (layout null) */
$total = array_sum(array_map(fn($d) => (int)$d['gain'], $details));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>PV tirage #<?= (int)$tirage['id'] ?> — <?= e($tontine['nom']) ?></title>
  <style>
    * { font-family:'Segoe UI', Arial, sans-serif; box-sizing:border-box; }
    body { margin:0; padding:30px 36px; color:#13201b; }
    .hd { display:flex; align-items:center; gap:14px; border-bottom:3px solid #0E6B57; padding-bottom:12px; margin-bottom:6px; }
    .hd img { height:52px; }
    .hd h1 { margin:0; font-size:1.3rem; color:#0E6B57; }
    .hd p { margin:2px 0 0; color:#6b8079; font-size:.85rem; }
    .ref { text-align:right; font-size:.8rem; color:#6b8079; margin-bottom:14px; }
    .meta { background:#f8faf9; border:1px solid #e7efec; border-radius:10px; padding:12px 14px; margin-bottom:14px; font-size:.88rem; line-height:1.6; }
    .meta b { color:#0E6B57; }
    table { width:100%; border-collapse:collapse; font-size:.85rem; page-break-inside:avoid; }
    th { background:#0E6B57; color:#fff; text-align:left; padding:7px 10px; }
    td { padding:6px 10px; border-bottom:1px solid #e7efec; }
    tr:nth-child(even) td { background:#f8faf9; }
    .gain { font-weight:800; color:#14806A; }
    tfoot td { font-weight:800; background:#e3f7f0; color:#0a7d5f; }
    .intro { font-size:.88rem; line-height:1.5; margin-bottom:14px; }
    .sign { margin-top:34px; display:flex; justify-content:space-between; page-break-inside:avoid; }
    .sign div { width:42%; border-top:1px solid #999; padding-top:6px; text-align:center; font-size:.82rem; color:#555; }
    .btn-print { position:fixed; top:18px; right:18px; background:#0E6B57; color:#fff; border:none; padding:10px 18px; border-radius:100px; cursor:pointer; font-weight:700; }
    /* Impression : tout sur une page, sans en-tête/pied de page navigateur (URL, date) */
    @page { size:A4; margin:0; }
    @media print {
      .btn-print { display:none; }
      body { padding:14mm 16mm; }
    }
  </style>
</head>
<body>
  <button class="btn-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
  <div class="hd">
    <img src="<?= asset('img/logo-dark.png') ?>" alt="">
    <div>
      <h1>Procès-verbal de tirage</h1>
      <p>Hôpital Dalal Jamm — TONTINE HDJ</p>
    </div>
  </div>
  <div class="ref">PV N° <?= str_pad((string)$tirage['id'], 4, '0', STR_PAD_LEFT) ?></div>

  <div class="meta">
    Tontine : <b><?= e($tontine['nom']) ?></b><br>
    Période du tirage : <b><?= e(moisFr((int)$tirage['mois']) . ' ' . $tirage['annee']) ?></b><br>
    Nombre de gagnants : <b><?= (int)$tirage['nb_gagnants'] ?></b> — Montant distribué : <b><?= e(fcfa($tirage['montant_distribue'])) ?></b>
  </div>

  <p class="intro">
    Le présent procès-verbal atteste qu'un tirage au sort sécurisé a été effectué pour la tontine
    « <?= e($tontine['nom']) ?> ». Le moteur a sélectionné automatiquement le groupe de bénéficiaires
    ci-dessous, dont la somme des gains correspond exactement au montant mensuel de la tontine.
  </p>

  <table>
    <thead><tr><th>#</th><th>Bénéficiaire</th><th>Téléphone</th><th style="text-align:right;">Gain attribué</th></tr></thead>
    <tbody>
    <?php foreach ($details as $i => $d): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($d['nom_complet']) ?></td>
        <td><?= e($d['telephone'] ?: '—') ?></td>
        <td class="gain" style="text-align:right;"><?= e(fcfa($d['gain'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="3">TOTAL DISTRIBUÉ</td><td style="text-align:right;"><?= e(fcfa($total)) ?></td></tr></tfoot>
  </table>

  <div class="sign">
    <div>Le Président</div>
    <div>Le Secrétaire</div>
  </div>
</body>
</html>
