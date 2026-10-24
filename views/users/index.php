<?php /** Présidents de tontine — TONTINE HDJ */ ?>
<div class="page-head">
  <div>
    <h1 class="ttl">Présidents de tontine</h1>
    <p class="desc">Créez et gérez les comptes des présidents qui animeront les tontines.</p>
  </div>
</div>

<div class="row g-3">
  <!-- Formulaire de création -->
  <div class="col-12 col-lg-5">
    <div class="card-hdj animate-up">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Nouveau président</h2>
        <form method="post" action="<?= url('utilisateurs/store') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label-hdj d-block" for="name">Nom complet *</label>
            <input class="form-control-hdj" id="name" name="name" value="<?= e(old('name')) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label-hdj d-block" for="username">Identifiant de connexion *</label>
            <input class="form-control-hdj" id="username" name="username" value="<?= e(old('username')) ?>" autocomplete="off" required>
          </div>
          <div class="mb-3">
            <label class="form-label-hdj d-block" for="password">Mot de passe *</label>
            <input class="form-control-hdj" type="text" id="password" name="password" autocomplete="new-password" required>
            <small style="color:var(--muted);">Au moins 6 caractères. Communiquez-le au président.</small>
          </div>
          <button type="submit" class="btn-hdj"><?= ic('plus') ?> Créer le président</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Liste -->
  <div class="col-12 col-lg-7">
    <div class="card-hdj animate-up delay-1">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Comptes (<?= count($users) ?>)</h2>
        <div class="table-wrap">
          <table class="table-hdj">
            <thead><tr><th>Nom</th><th>Identifiant</th><th>Rôle</th><th>Tontines</th><th>Membres</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $usr): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($usr['name'], 0, 1))) ?></span>
                    <b><?= e($usr['name']) ?></b>
                  </div>
                </td>
                <td><?= e($usr['username']) ?></td>
                <td>
                  <?php if ($usr['role'] === 'super_admin'): ?>
                    <span class="badge-hdj badge-win">Super administrateur</span>
                  <?php else: ?>
                    <span class="badge-hdj badge-secondary">Président</span>
                  <?php endif; ?>
                </td>
                <td><b><?= (int)$usr['nb_tontines'] ?></b></td>
                <td><b><?= (int)$usr['nb_membres'] ?></b></td>
                <td style="text-align:right;">
                  <?php if ($usr['role'] !== 'super_admin' && (int)$usr['id'] !== Auth::id()): ?>
                    <form method="post" action="<?= url('utilisateurs/delete') ?>" class="m-0 d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$usr['id'] ?>">
                      <button type="button" class="icon-btn danger" data-confirm="Supprimer le compte de « <?= e($usr['name']) ?> » ?" title="Supprimer"><?= ic('trash') ?></button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--muted);font-size:.8rem;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
