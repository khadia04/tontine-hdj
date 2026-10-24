<?php /** Profil — TONTINE HDJ */ $u = Auth::user(); ?>

<div class="page-head">
  <div>
    <h1 class="ttl">Mon profil</h1>
    <p class="desc">Gérez vos informations de connexion</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card-hdj animate-up">
      <div class="cardbody text-center">
        <div class="avatar-sm" style="width:84px;height:84px;font-size:1.8rem;margin:6px auto 14px;">
          <?= e(mb_strtoupper(mb_substr($u['name'] ?? 'U', 0, 1))) ?>
        </div>
        <h2 class="card-title-hdj mb-1"><?= e($u['name'] ?? '') ?></h2>
        <p style="color:var(--muted);font-size:.9rem;margin:0;">
          <?= e(Auth::roleLabel()) ?><br>
          <span style="font-size:.82rem;">Identifiant : <b><?= e($u['username'] ?? '') ?></b></span>
        </p>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card-hdj animate-up delay-1">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Changer le mot de passe</h2>
        <form method="post" action="<?= url('profil/password') ?>" autocomplete="off">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label-hdj d-block" for="actuel">Mot de passe actuel</label>
            <input class="form-control-hdj" type="password" id="actuel" name="actuel" required>
          </div>
          <div class="mb-3">
            <label class="form-label-hdj d-block" for="nouveau">Nouveau mot de passe</label>
            <input class="form-control-hdj" type="password" id="nouveau" name="nouveau" minlength="6" required>
            <small style="color:var(--muted);">Au moins 6 caractères.</small>
          </div>
          <div class="mb-4">
            <label class="form-label-hdj d-block" for="confirm">Confirmer le nouveau mot de passe</label>
            <input class="form-control-hdj" type="password" id="confirm" name="confirm" minlength="6" required>
          </div>
          <button type="submit" class="btn-hdj"><?= ic('key') ?> Mettre à jour</button>
        </form>
      </div>
    </div>
  </div>
</div>
