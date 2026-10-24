<?php /** Liste des tontines — TONTINE HDJ */ ?>

<div class="page-head">
  <div>
    <h1 class="ttl">Tontines</h1>
    <p class="desc"><?= count($tontines) ?> tontine(s) enregistrée(s)</p>
  </div>
  <a href="<?= url('tontines/create') ?>" class="btn-hdj"><?= ic('plus') ?> Nouvelle tontine</a>
</div>

<?php if (empty($tontines)): ?>
  <div class="card-hdj animate-up"><div class="cardbody">
    <div class="empty">
      <div class="ic"><?= ic('layers') ?></div>
      <p>Aucune tontine pour le moment.</p>
      <a href="<?= url('tontines/create') ?>" class="btn-hdj mt-2"><?= ic('plus') ?> Créer la première tontine</a>
    </div>
  </div></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($tontines as $i => $t):
      [$lbl, $variant] = Tontine::statutBadge($t['statut']);
      $pct = $t['nb_membres'] > 0 ? round(100 * $t['nb_gagnants'] / $t['nb_membres']) : 0; ?>
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card-hdj animate-up delay-<?= min(6, $i+1) ?>">
          <div class="cardbody">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h2 class="card-title-hdj"><?= e($t['nom']) ?></h2>
              <span class="badge-hdj badge-<?= e($variant) ?>"><?= e($lbl) ?></span>
            </div>
            <p style="color:var(--muted);font-size:.86rem;min-height:38px;">
              <?= e(mb_strimwidth($t['description'] ?? '', 0, 90, '…')) ?: '—' ?>
            </p>
            <div class="d-flex justify-content-between" style="font-size:.9rem;">
              <span style="color:var(--muted);">Pot mensuel</span>
              <b style="color:var(--hdj-emerald);"><?= e(fcfa($t['montant_mensuel'])) ?></b>
            </div>
            <div class="d-flex justify-content-between" style="font-size:.9rem;margin-top:4px;">
              <span style="color:var(--muted);">Durée</span><b><?= (int)$t['duree'] ?> mois</b>
            </div>
            <div class="progress-hdj mt-3"><span style="width:<?= $pct ?>%"></span></div>
            <small style="color:var(--muted);"><?= (int)$t['nb_gagnants'] ?>/<?= (int)$t['nb_membres'] ?> gagnants</small>

            <div class="d-flex gap-2 mt-3">
              <a href="<?= url('tontines/show/' . $t['id']) ?>" class="btn-ghost btn-sm-hdj"><?= ic('eye') ?> Ouvrir</a>
              <a href="<?= url('tontines/edit/' . $t['id']) ?>" class="icon-btn" title="Modifier"><?= ic('edit') ?></a>
              <?php if ($t['statut'] !== 'active'): ?>
              <form method="post" action="<?= url('tontines/delete') ?>" class="m-0">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <button type="button" class="icon-btn danger" data-confirm="Supprimer la tontine « <?= e($t['nom']) ?> » et toutes ses données ?" title="Supprimer"><?= ic('trash') ?></button>
              </form>
              <?php else: ?>
              <span class="icon-btn" style="opacity:.4;cursor:not-allowed;" title="Tontine active : suspendez-la ou terminez-la pour pouvoir la supprimer"><?= ic('trash') ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
