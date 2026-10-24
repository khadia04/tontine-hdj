<?php
/** Détail tontine — TONTINE HDJ */
[$lbl, $variant] = Tontine::statutBadge($tontine['statut']);
$id = (int)$tontine['id'];
?>

<div class="page-head">
  <div>
    <h1 class="ttl"><?= e($tontine['nom']) ?> <span class="badge-hdj badge-<?= e($variant) ?>" style="vertical-align:middle;"><?= e($lbl) ?></span></h1>
    <p class="desc"><?= e($tontine['description'] ?: 'Tontine du personnel') ?></p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('tontines') ?>" class="btn-ghost"><?= ic('back') ?> Tontines</a>
    <a href="<?= url('tontines/edit/' . $id) ?>" class="btn-ghost"><?= ic('edit') ?> Modifier</a>
  </div>
</div>

<!-- Indicateurs -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="stat stat-feature animate-up"><div class="ic"><?= ic('cash') ?></div><div class="lab">Pot mensuel</div><div class="val sm"><?= e(fcfa($tontine['montant_mensuel'])) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat animate-up delay-1"><div class="ic"><?= ic('users') ?></div><div class="lab">Membres</div><div class="val"><?= (int)$stats['nb_membres'] ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat animate-up delay-2"><div class="ic"><?= ic('trophy') ?></div><div class="lab">Gagnants</div><div class="val"><?= (int)$stats['nb_gagnants'] ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="stat animate-up delay-3"><div class="ic"><?= ic('calendar') ?></div><div class="lab">Restant à tirer</div><div class="val"><?= (int)$stats['nb_restants'] ?></div></div></div>
</div>

<!-- Accès modules -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <a href="<?= url('membres/index/' . $id) ?>" class="card-hdj d-block animate-up" style="color:inherit;">
      <div class="cardbody d-flex align-items-center gap-3">
        <span class="ic" style="width:50px;height:50px;border-radius:14px;display:grid;place-items:center;background:var(--hdj-grad-sheen);color:var(--hdj-primary);"><?= ic('users') ?></span>
        <div><b style="color:var(--hdj-dark);">Membres</b><br><small style="color:var(--muted);">Gérer les participants</small></div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-4">
    <a href="<?= url('cotisations/index/' . $id) ?>" class="card-hdj d-block animate-up delay-1" style="color:inherit;">
      <div class="cardbody d-flex align-items-center gap-3">
        <span class="ic" style="width:50px;height:50px;border-radius:14px;display:grid;place-items:center;background:var(--hdj-grad-sheen);color:var(--hdj-primary);"><?= ic('wallet') ?></span>
        <div><b style="color:var(--hdj-dark);">Cotisations</b><br><small style="color:var(--muted);">Suivi des paiements</small></div>
      </div>
    </a>
  </div>
  <div class="col-12 col-md-4">
    <a href="<?= url('tirages/index/' . $id) ?>" class="card-hdj d-block animate-up delay-2" style="color:inherit;">
      <div class="cardbody d-flex align-items-center gap-3">
        <span class="ic" style="width:50px;height:50px;border-radius:14px;display:grid;place-items:center;background:var(--hdj-grad-sheen);color:var(--hdj-primary);"><?= ic('trophy') ?></span>
        <div><b style="color:var(--hdj-dark);">Tirage</b><br><small style="color:var(--muted);">Lancer la roue</small></div>
      </div>
    </a>
  </div>
</div>

<!-- Membres -->
<div class="card-hdj animate-up">
  <div class="cardbody">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h2 class="card-title-hdj">Membres (<?= count($membres) ?>)</h2>
      <div class="d-flex gap-2">
        <a href="<?= url('exports/membres/' . $id) ?>" class="btn-ghost btn-sm-hdj"><?= ic('download') ?> Excel</a>
        <a href="<?= url('membres/create/' . $id) ?>" class="btn-hdj btn-sm-hdj"><?= ic('plus') ?> Ajouter</a>
      </div>
    </div>
    <?php if (empty($membres)): ?>
      <div class="empty"><div class="ic"><?= ic('users') ?></div>Aucun membre. Ajoutez le premier participant.</div>
    <?php else: ?>
      <div class="mb-3">
        <input type="search" id="filtreMembres" class="form-control-hdj"
               placeholder="🔍 Filtrer par nom, téléphone, cotisation ou gain…"
               style="max-width:420px;">
        <small id="filtreMembresInfo" style="color:var(--muted);display:block;margin-top:6px;"></small>
      </div>
      <div class="table-wrap">
        <table class="table-hdj" id="tableMembres">
          <thead><tr><th>Membre</th><th>Téléphone</th><th>Cotisation</th><th>Gain</th><th>Statut</th></tr></thead>
          <tbody>
          <?php foreach ($membres as $m): ?>
            <tr>
              <td><div class="d-flex align-items-center gap-2"><span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($m['nom_complet'],0,1))) ?></span><b><?= e($m['nom_complet']) ?></b></div></td>
              <td><?= e($m['telephone'] ?: '—') ?></td>
              <td><?= e(fcfa($m['cotisation_mensuelle'])) ?></td>
              <td><b style="color:var(--hdj-emerald);"><?= e(fcfa($m['gain'])) ?></b></td>
              <td><?= $m['a_gagne'] ? '<span class="badge-hdj badge-win">🏆 Gagnant</span>' : '<span class="badge-hdj badge-secondary">⏳ En attente</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Historique des tirages -->
<?php if (!empty($tirages)): ?>
<div class="card-hdj animate-up mt-3">
  <div class="cardbody">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="card-title-hdj">Tirages (<?= count($tirages) ?>)</h2>
      <a href="<?= url('exports/tirages/' . $id) ?>" class="btn-ghost btn-sm-hdj"><?= ic('download') ?> Excel</a>
    </div>
    <div class="table-wrap">
      <table class="table-hdj">
        <thead><tr><th>Date</th><th>Période</th><th>Gagnants</th><th>Distribué</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tirages as $tr): ?>
          <tr>
            <td><?= e(dateFr($tr['date_tirage'], true)) ?></td>
            <td><?= e(moisFr((int)$tr['mois']) . ' ' . $tr['annee']) ?></td>
            <td><?= (int)$tr['nb_gagnants'] ?></td>
            <td><b style="color:var(--hdj-emerald);"><?= e(fcfa($tr['montant_distribue'])) ?></b></td>
            <td class="text-end"><a href="<?= url('tirages/show/' . $tr['id']) ?>" target="_blank" class="icon-btn" title="Procès-verbal"><?= ic('print') ?></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php ob_start(); ?>
<script>
(function () {
  var input = document.getElementById('filtreMembres');
  var table = document.getElementById('tableMembres');
  var info  = document.getElementById('filtreMembresInfo');
  if (!input || !table) return;
  var rows = Array.prototype.slice.call(table.tBodies[0].rows);

  function norm(s) {
    return (s || '').toString().toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // ignore les accents
  }

  input.addEventListener('input', function () {
    var q = norm(input.value.trim());
    var visibles = 0;
    rows.forEach(function (tr) {
      var txt = norm(tr.innerText);
      var ok = q === '' || txt.indexOf(q) !== -1;
      tr.style.display = ok ? '' : 'none';
      if (ok) visibles++;
    });
    info.textContent = q === '' ? '' : visibles + ' membre(s) trouvé(s)';
  });
})();
</script>
<?php $scripts = ob_get_clean(); ?>
