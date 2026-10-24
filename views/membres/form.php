<?php
/** Formulaire membre — TONTINE HDJ */
$edit   = $membre !== null;
$tid    = (int)$tontine['id'];
$duree  = (int)$tontine['duree'];
$action = $edit ? url('membres/update') : url('membres/store');
$val = function (string $k, $def = '') use ($edit, $membre) {
    return old($k, $edit ? ($membre[$k] ?? $def) : $def);
};
$cotisCourante = (int) $val('cotisation_mensuelle', 0);
?>

<div class="page-head">
  <div>
    <h1 class="ttl"><?= $edit ? 'Modifier le membre' : 'Nouveau membre' ?></h1>
    <p class="desc">Tontine : <?= e($tontine['nom']) ?> · durée <?= $duree ?> mois</p>
  </div>
  <a href="<?= url('membres/index/' . $tid) ?>" class="btn-ghost"><?= ic('back') ?> Retour</a>
</div>

<div class="card-hdj animate-up" style="max-width:820px;">
  <div class="cardbody">
    <form method="post" action="<?= e($action) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="tontine_id" value="<?= $tid ?>">
      <input type="hidden" id="dureeData" value="<?= $duree ?>">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$membre['id'] ?>"><?php endif; ?>

      <div class="row g-3">
        <div class="col-12">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label-hdj d-block" for="nom_complet">Nom complet *</label>
              <input class="form-control-hdj" id="nom_complet" name="nom_complet" value="<?= e($val('nom_complet')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label-hdj d-block" for="telephone">Téléphone</label>
              <input class="form-control-hdj" id="telephone" name="telephone" value="<?= e($val('telephone')) ?>" placeholder="77 000 00 00">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label-hdj d-block" for="date_inscription">Date d'inscription</label>
              <input class="form-control-hdj" type="date" id="date_inscription" name="date_inscription" value="<?= e($val('date_inscription', date('Y-m-d'))) ?>">
            </div>
            <div class="col-12">
              <label class="form-label-hdj d-block" for="adresse">Adresse</label>
              <input class="form-control-hdj" id="adresse" name="adresse" value="<?= e($val('adresse')) ?>">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label-hdj d-block" for="cotisField">Cotisation mensuelle *</label>
              <div class="input-money">
                <input class="form-control-hdj" type="number" min="0" step="1000" id="cotisField" name="cotisation_mensuelle" value="<?= $cotisCourante ?>" required>
                <span class="suffix">FCFA</span>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label-hdj d-block">Gain automatique</label>
              <div class="form-control-hdj" style="background:var(--hdj-grad-sheen);font-weight:800;color:var(--hdj-emerald);">
                <span id="gainOut"><?= e(fcfa($cotisCourante * $duree)) ?></span>
              </div>
              <small style="color:var(--muted);">= cotisation × durée (<?= $duree ?> mois)</small>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn-hdj"><?= $edit ? 'Enregistrer' : 'Ajouter le membre' ?></button>
        <a href="<?= url('membres/index/' . $tid) ?>" class="btn-ghost">Annuler</a>
      </div>
    </form>
  </div>
</div>
