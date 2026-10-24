<?php
/** Cotisations — TONTINE HDJ */
$tid = (int)$tontine['id'];
$annees = range((int)date('Y') + 1, (int)date('Y') - 4);
?>

<div class="page-head">
  <div>
    <h1 class="ttl">Cotisations</h1>
    <p class="desc"><?= e($tontine['nom']) ?> · <?= e(moisFr($mois)) ?> <?= (int)$annee ?></p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('tontines/show/' . $tid) ?>" class="btn-ghost"><?= ic('back') ?> Tontine</a>
    <a href="<?= url('exports/cotisations/' . $tid . '?mois=' . $mois . '&annee=' . $annee) ?>" class="btn-ghost"><?= ic('download') ?> Excel</a>
    <a href="<?= url('exports/rapport/' . $tid . '?mois=' . $mois . '&annee=' . $annee) ?>" target="_blank" class="btn-ghost"><?= ic('print') ?> Rapport</a>
  </div>
</div>

<!-- Sélecteur de période + totaux -->
<div class="row g-3 mb-3">
  <div class="col-12 col-lg-8">
    <div class="card-hdj"><div class="cardbody">
      <form method="get" action="<?= url('cotisations/index/' . $tid) ?>" class="row g-2 align-items-end">
        <div class="col-6 col-md-5">
          <label class="form-label-hdj d-block">Mois</label>
          <select class="form-select-hdj" name="mois" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?= $m ?>" <?= $m === $mois ? 'selected' : '' ?>><?= e(moisFr($m)) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-6 col-md-5">
          <label class="form-label-hdj d-block">Année</label>
          <select class="form-select-hdj" name="annee" onchange="this.form.submit()">
            <?php foreach ($annees as $a): ?>
              <option value="<?= $a ?>" <?= $a === (int)$annee ? 'selected' : '' ?>><?= $a ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <button class="btn-ghost w-100 justify-content-center" type="submit">OK</button>
        </div>
      </form>
    </div></div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="stat stat-feature h-100">
      <div class="ic"><?= ic('wallet') ?></div>
      <div class="lab">Total encaissé</div>
      <div class="val sm" id="totalPaye"><?= e(fcfa($totalPaye)) ?></div>
      <small style="color:rgba(255,255,255,.8);">sur <?= e(fcfa($totalAttendu)) ?> attendus</small>
    </div>
  </div>
</div>

<div class="card-hdj animate-up">
  <div class="cardbody">
    <?php if (empty($lignes)): ?>
      <div class="empty"><div class="ic"><?= ic('users') ?></div>Aucun membre dans cette tontine.</div>
    <?php else: ?>
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <input type="search" id="filtreCotis" class="form-control-hdj"
               placeholder="🔍 Filtrer par nom ou téléphone…" style="max-width:340px;">
        <button id="btnMarquerPaye" class="btn-hdj btn-sm-hdj" type="button" disabled>
          <?= ic('cash') ?> Marquer la sélection comme payé (<span id="nbSel">0</span>)
        </button>
      </div>
      <div class="table-wrap">
        <table class="table-hdj" id="tableCotis">
          <thead><tr>
            <th style="width:38px;"><input type="checkbox" id="chkAll" title="Tout sélectionner"></th>
            <th>Membre</th><th>Cotisation</th><th>Paiement</th><th>Date de paiement</th>
          </tr></thead>
          <tbody>
          <?php foreach ($lignes as $l):
            $statut = $l['statut'] ?? 'non_paye'; ?>
            <tr>
              <td><input type="checkbox" class="chk-membre" value="<?= (int)$l['membre_id'] ?>" <?= $statut === 'paye' ? 'data-paye="1"' : '' ?>></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($l['nom_complet'],0,1))) ?></span>
                  <div><b><?= e($l['nom_complet']) ?></b><br><small style="color:var(--muted);"><?= e($l['telephone'] ?: '—') ?></small></div>
                </div>
              </td>
              <td><?= e(fcfa($l['cotisation_mensuelle'])) ?></td>
              <td>
                <select class="form-select-hdj cotis-select<?= $statut === 'paye' ? ' paye' : '' ?>" style="max-width:160px;"
                        data-membre="<?= (int)$l['membre_id'] ?>"
                        data-tontine="<?= $tid ?>"
                        data-mois="<?= $mois ?>"
                        data-annee="<?= (int)$annee ?>"
                        data-prev="<?= e($statut) ?>">
                  <option value="non_paye" <?= $statut !== 'paye' ? 'selected' : '' ?>>❌ Non payé</option>
                  <option value="paye" <?= $statut === 'paye' ? 'selected' : '' ?>>✅ Payé</option>
                </select>
              </td>
              <td id="date-<?= (int)$l['membre_id'] ?>"><?= $l['date_paiement'] ? e(dateFr($l['date_paiement'], true)) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .cotis-select.paye { border-color:#0DBA98; background:#e3f7f0; color:#0a7d5f; font-weight:700; }
</style>

<?php ob_start(); ?>
<script>
(function () {
  var filtre = document.getElementById('filtreCotis');
  var table  = document.getElementById('tableCotis');
  if (!table) return;
  var rows = Array.prototype.slice.call(table.tBodies[0].rows);
  var chkAll = document.getElementById('chkAll');
  var btn    = document.getElementById('btnMarquerPaye');
  var nbSel  = document.getElementById('nbSel');

  function norm(s) {
    return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  // --- Filtre par nom / téléphone ---
  filtre && filtre.addEventListener('input', function () {
    var q = norm(filtre.value.trim());
    rows.forEach(function (tr) {
      var txt = norm(tr.cells[1].innerText);
      tr.style.display = (q === '' || txt.indexOf(q) !== -1) ? '' : 'none';
    });
    sync();
  });

  function visibleChecks() {
    return rows.filter(function (tr) { return tr.style.display !== 'none'; })
               .map(function (tr) { return tr.querySelector('.chk-membre'); })
               .filter(Boolean);
  }
  function checkedIds() {
    return rows.map(function (tr) { return tr.querySelector('.chk-membre'); })
               .filter(function (c) { return c && c.checked; })
               .map(function (c) { return c.value; });
  }
  function sync() {
    var ids = checkedIds();
    if (nbSel) nbSel.textContent = ids.length;
    if (btn) btn.disabled = ids.length === 0;
    if (chkAll) {
      var vis = visibleChecks();
      chkAll.checked = vis.length > 0 && vis.every(function (c) { return c.checked; });
    }
  }

  chkAll && chkAll.addEventListener('change', function () {
    visibleChecks().forEach(function (c) { c.checked = chkAll.checked; });
    sync();
  });
  rows.forEach(function (tr) {
    var c = tr.querySelector('.chk-membre');
    c && c.addEventListener('change', sync);
  });

  // --- Marquer la sélection comme payé ---
  btn && btn.addEventListener('click', function () {
    var ids = checkedIds();
    if (!ids.length) return;
    btn.disabled = true;

    var data = new FormData();
    data.append('_csrf', window.CSRF);
    data.append('tontine_id', table.dataset.tontine || '');
    var first = table.querySelector('.cotis-select');
    data.append('tontine_id', first ? first.dataset.tontine : '');
    data.append('mois', first ? first.dataset.mois : '');
    data.append('annee', first ? first.dataset.annee : '');
    data.append('statut', 'paye');
    ids.forEach(function (id) { data.append('membre_ids[]', id); });

    fetch(window.BASE + 'cotisations/bulkPay', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.ok) { window.hdjToast('error', res.message || 'Échec'); btn.disabled = false; return; }
        res.maj.forEach(function (m) {
          var sel = document.querySelector('.cotis-select[data-membre="' + m.membre_id + '"]');
          if (sel) {
            sel.value = 'paye';
            sel.classList.add('paye');
            sel.dataset.prev = 'paye';
          }
          var dcell = document.getElementById('date-' + m.membre_id);
          if (dcell && m.date_paiement) dcell.textContent = m.date_paiement;
          var chk = document.querySelector('.chk-membre[value="' + m.membre_id + '"]');
          if (chk) { chk.checked = false; chk.setAttribute('data-paye', '1'); }
        });
        var tp = document.getElementById('totalPaye');
        if (tp) tp.textContent = res.total_paye;
        window.hdjToast('success', res.nb + ' paiement(s) enregistré(s)');
        sync();
      })
      .catch(function () { window.hdjToast('error', 'Erreur réseau'); btn.disabled = false; });
  });

  sync();
})();
</script>
<?php $scripts = ob_get_clean(); ?>
