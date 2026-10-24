<?php
/** Formulaire tontine — TONTINE HDJ */
$edit   = $tontine !== null;
$action = $edit ? url('tontines/update') : url('tontines/store');
$val = function (string $k, $def = '') use ($edit, $tontine) {
    return old($k, $edit ? ($tontine[$k] ?? $def) : $def);
};
?>

<div class="page-head">
  <div>
    <h1 class="ttl"><?= $edit ? 'Modifier la tontine' : 'Nouvelle tontine' ?></h1>
    <p class="desc"><?= $edit ? e($tontine['nom']) : 'Définissez les paramètres de la tontine' ?></p>
  </div>
  <a href="<?= url('tontines') ?>" class="btn-ghost"><?= ic('back') ?> Retour</a>
</div>

<div class="card-hdj animate-up" style="max-width:760px;">
  <div class="cardbody">
    <form method="post" action="<?= e($action) ?>">
      <?= csrf_field() ?>
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$tontine['id'] ?>"><?php endif; ?>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label-hdj d-block" for="nom">Nom de la tontine *</label>
          <input class="form-control-hdj" id="nom" name="nom" value="<?= e($val('nom')) ?>" required>
        </div>
        <div class="col-12">
          <label class="form-label-hdj d-block" for="description">Description</label>
          <textarea class="form-control-hdj" id="description" name="description" rows="3"><?= e($val('description')) ?></textarea>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label-hdj d-block">Pot mensuel (cumul des cotisations)</label>
          <div class="input-money">
            <input class="form-control-hdj" type="text" value="<?= e(fcfa((int)$val('montant_mensuel', 0))) ?>" readonly disabled style="background:#eef4f2;">
          </div>
          <input type="hidden" name="montant_mensuel" value="<?= e($val('montant_mensuel', 0)) ?>">
          <small style="color:var(--muted);">Calculé automatiquement : somme des cotisations des membres. Chaque gagnant reçoit sa cotisation × la durée.</small>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label-hdj d-block" for="duree">Durée (mois) *</label>
          <input class="form-control-hdj" type="number" min="1" id="duree" name="duree" value="<?= e($val('duree', 10)) ?>" required>
          <small style="color:var(--muted);">Le gain d'un membre = sa cotisation × durée.</small>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label-hdj d-block" for="date_debut">Date de début</label>
          <input class="form-control-hdj" type="date" id="date_debut" name="date_debut" value="<?= e($val('date_debut')) ?>">
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label-hdj d-block" for="date_fin">Date de fin (auto)</label>
          <input class="form-control-hdj" type="date" id="date_fin" name="date_fin" value="<?= e($val('date_fin')) ?>" readonly style="background:#eef4f2;">
          <small style="color:var(--muted);">Déduite de la date de début + durée.</small>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label-hdj d-block" for="statut">Statut</label>
          <?php $st = $val('statut', 'active'); ?>
          <select class="form-select-hdj" id="statut" name="statut">
            <option value="active" <?= $st==='active'?'selected':'' ?>>Active</option>
            <option value="suspendue" <?= $st==='suspendue'?'selected':'' ?>>Suspendue</option>
            <option value="terminee" <?= $st==='terminee'?'selected':'' ?>>Terminée</option>
          </select>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn-hdj"><?= $edit ? 'Enregistrer les modifications' : 'Créer la tontine' ?></button>
        <a href="<?= url('tontines') ?>" class="btn-ghost">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php ob_start(); ?>
<script>
(function () {
  var debut = document.getElementById('date_debut');
  var duree = document.getElementById('duree');
  var fin   = document.getElementById('date_fin');
  if (!debut || !duree || !fin) return;

  function calcFin() {
    if (!debut.value) { return; }
    var d = new Date(debut.value + 'T00:00:00');
    var n = parseInt(duree.value, 10);
    if (isNaN(d.getTime()) || isNaN(n) || n < 1) { return; }
    // Une tontine de N mois démarrant au mois M se termine au mois M + (N-1).
    var j = d.getDate();
    d.setMonth(d.getMonth() + (n - 1));
    // Garde-fou si le jour déborde (ex. 31 -> mois plus court)
    if (d.getDate() < j) { d.setDate(0); }
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var dd = String(d.getDate()).padStart(2, '0');
    fin.value = y + '-' + m + '-' + dd;
  }

  debut.addEventListener('change', calcFin);
  duree.addEventListener('input', calcFin);
  // Recalcule au chargement si une date de début existe déjà
  if (debut.value && !fin.value) calcFin();
})();
</script>
<?php $scripts = ob_get_clean(); ?>
