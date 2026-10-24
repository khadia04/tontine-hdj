<?php /** Membres — TONTINE HDJ */ $tid = (int)$tontine['id']; ?>

<div class="page-head">
  <div>
    <h1 class="ttl">Membres</h1>
    <p class="desc"><?= e($tontine['nom']) ?> · <?= count($membres) ?> membre(s)</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('tontines/show/' . $tid) ?>" class="btn-ghost"><?= ic('back') ?> Tontine</a>
    <a href="<?= url('exports/membres/' . $tid) ?>" class="btn-ghost"><?= ic('download') ?> Excel</a>
    <a href="<?= url('membres/create/' . $tid) ?>" class="btn-hdj"><?= ic('plus') ?> Nouveau membre</a>
  </div>
</div>

<div class="card-hdj animate-up">
  <div class="cardbody">
    <form method="get" action="<?= url('membres/index/' . $tid) ?>" class="mb-3" style="max-width:340px;">
      <input class="form-control-hdj" type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher un membre…">
    </form>

    <?php if (empty($membres)): ?>
      <div class="empty"><div class="ic"><?= ic('users') ?></div><?= $search !== '' ? 'Aucun membre ne correspond à « ' . e($search) . ' ».' : 'Aucun membre enregistré.' ?></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table-hdj">
          <thead><tr><th>Membre</th><th>Téléphone</th><th>Adresse</th><th>Cotisation</th><th>Gain</th><th>Statut</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($membres as $m): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($m['photo'])): ?>
                    <img class="avatar-sm" src="<?= url('uploads/' . rawurlencode($m['photo'])) ?>" alt="">
                  <?php else: ?>
                    <span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($m['nom_complet'],0,1))) ?></span>
                  <?php endif; ?>
                  <b><?= e($m['nom_complet']) ?></b>
                </div>
              </td>
              <td><?= e($m['telephone'] ?: '—') ?></td>
              <td><?= e($m['adresse'] ?: '—') ?></td>
              <td><?= e(fcfa($m['cotisation_mensuelle'])) ?></td>
              <td><b style="color:var(--hdj-emerald);"><?= e(fcfa($m['gain'])) ?></b></td>
              <td><?= $m['a_gagne'] ? '<span class="badge-hdj badge-win">🏆 Gagnant</span>' : '<span class="badge-hdj badge-secondary">⏳ En attente</span>' ?></td>
              <td class="text-end">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="<?= url('membres/edit/' . $m['id']) ?>" class="icon-btn" title="Modifier"><?= ic('edit') ?></a>
                  <form method="post" action="<?= url('membres/delete') ?>" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button type="button" class="icon-btn danger" data-confirm="Supprimer « <?= e($m['nom_complet']) ?> » ?" title="Supprimer"><?= ic('trash') ?></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
