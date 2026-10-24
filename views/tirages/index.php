<?php
/** Tirage — TONTINE HDJ */
$tid = (int)$tontine['id'];
$active = $tontine['statut'] === 'active';
$peutTirer = $active && $comboExiste && !empty($eligibles);
$eligJs = array_map(fn($m) => [
    'id'   => (int)$m['id'],
    'nom'  => $m['nom_complet'],
    'gain' => (int)$m['gain'],
], array_values($eligibles));
?>

<div class="page-head">
  <div>
    <h1 class="ttl">Tirage au sort</h1>
    <p class="desc"><?= e($tontine['nom']) ?> · pot mensuel collecté <b><?= e(fcfa($tontine['montant_mensuel'])) ?></b></p>
  </div>
  <a href="<?= url('tontines/show/' . $tid) ?>" class="btn-ghost"><?= ic('back') ?> Tontine</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="stat"><div class="lab">Éligibles</div><div class="val" id="statEligibles"><?= count($eligibles) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="stat"><div class="lab">Déjà gagnants</div><div class="val" id="statGagnants"><?= (int)$stats['nb_gagnants'] ?></div></div></div>
  <div class="col-6 col-md-3"><div class="stat"><div class="lab">Pot mensuel collecté</div><div class="val sm"><?= e(fcfa($tontine['montant_mensuel'])) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="stat"><div class="lab">Tirages effectués</div><div class="val" id="statTirages"><?= count($historique) ?></div></div></div>
</div>

<?php if (!$active): ?>
  <div class="flash error">Cette tontine n'est pas active : le tirage est désactivé.</div>
<?php elseif (empty($eligibles)): ?>
  <div class="flash success">🎉 Tous les membres ont déjà gagné ! La tontine est complète.</div>
<?php elseif (!$comboExiste): ?>
  <div class="flash error">Impossible de constituer un lot dont la somme des gains égale exactement <?= e(fcfa($tontine['montant_mensuel'])) ?> avec les membres restants.</div>
<?php endif; ?>

<div class="row g-3">
  <!-- Roue -->
  <div class="col-12 col-lg-7">
    <div class="card-hdj animate-up"><div class="cardbody text-center">
      <div class="wheel-scene">
        <div class="wheel-lights" id="wheelLights"></div>
        <div class="wheel-pointer"></div>
        <div class="wheel" id="wheel"></div>
        <div class="wheel-center"><img src="<?= asset('img/logo-light.png') ?>" alt="HDJ"></div>
        <div class="wheel-count" id="wheelCount"></div>
      </div>
      <div class="d-flex gap-2 justify-content-center align-items-end flex-wrap mt-3" style="max-width:420px;margin-inline:auto;">
        <div style="flex:1;min-width:140px;">
          <label class="form-label-hdj d-block" style="text-align:left;">Mois du tirage</label>
          <select id="tirageMois" class="form-select-hdj">
            <?php for ($mo = 1; $mo <= 12; $mo++): ?>
              <option value="<?= $mo ?>" <?= $mo === (int)date('n') ? 'selected' : '' ?>><?= e(moisFr($mo)) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="flex:1;min-width:110px;">
          <label class="form-label-hdj d-block" style="text-align:left;">Année</label>
          <select id="tirageAnnee" class="form-select-hdj">
            <?php for ($yr = (int)date('Y') - 1; $yr <= (int)date('Y') + 2; $yr++): ?>
              <option value="<?= $yr ?>" <?= $yr === (int)date('Y') ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <button id="btnTirage" class="btn-hdj mt-4" style="padding:14px 34px;font-size:1.05rem;" <?= $peutTirer ? '' : 'disabled' ?>>
        <?= ic('trophy') ?> Lancer le tirage
      </button>
      <p style="color:var(--muted);font-size:.82rem;margin-top:10px;">
        Le moteur constitue un lot de gagnants dont les gains totalisent exactement le pot mensuel. Chacun reçoit sa cotisation × la durée.
      </p>
    </div></div>
  </div>

  <!-- Résultats -->
  <div class="col-12 col-lg-5">
    <div class="card-hdj animate-up delay-1" id="resultCard" style="display:none;">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-1">🏆 Gagnants du tirage</h2>
        <p style="color:var(--muted);font-size:.85rem;margin:0 0 12px;">Total distribué : <b id="resTotal" style="color:var(--hdj-emerald);"></b></p>
        <div id="resList"></div>
        <a id="resPv" href="#" target="_blank" class="btn-ghost btn-sm-hdj mt-3"><?= ic('print') ?> Procès-verbal</a>
        <a href="<?= url('tirages/index/' . $tid) ?>" class="btn-hdj btn-sm-hdj mt-3">Actualiser</a>
      </div>
    </div>

    <div class="card-hdj animate-up delay-2 mt-3">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Historique des tirages</h2>
        <div id="histList">
        <?php if (empty($historique)): ?>
          <p id="histEmpty" style="color:var(--muted);font-size:.88rem;">Aucun tirage pour le moment.</p>
        <?php else: foreach ($historique as $h): $tr = $h['tirage']; ?>
          <div class="hist-card" style="border:1px solid var(--line);border-radius:var(--radius-sm);padding:12px;margin-bottom:10px;">
            <div class="d-flex justify-content-between align-items-center">
              <b style="color:var(--hdj-dark);"><?= e(moisFr((int)$tr['mois']) . ' ' . $tr['annee']) ?></b>
              <a href="<?= url('tirages/show/' . $tr['id']) ?>" target="_blank" class="icon-btn" title="PV"><?= ic('print') ?></a>
            </div>
            <small style="color:var(--muted);"><?= e(dateFr($tr['date_tirage'], true)) ?> · <?= (int)$tr['nb_gagnants'] ?> gagnant<?= (int)$tr['nb_gagnants'] > 1 ? 's' : '' ?></small>
            <div class="mt-2 d-flex flex-wrap gap-1">
              <?php foreach ($h['details'] as $d): ?>
                <span class="badge-hdj badge-win"><?= e($d['nom_complet']) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script>
window.TIRAGE = {
  tontineId: <?= $tid ?>,
  mois: <?= (int)date('n') ?>,
  annee: <?= (int)date('Y') ?>,
  target: <?= (int)$tontine['montant_mensuel'] ?>,
  base: <?= json_encode(url('tirages/show/')) ?>,
  eligibles: <?= json_encode($eligJs, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="<?= asset('js/tirage.js') ?>"></script>
<?php $scripts = ob_get_clean(); ?>
