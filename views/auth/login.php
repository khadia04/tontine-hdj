<?php /** Vue login — TONTINE HDJ */ ?>
<div class="auth-wrap">
  <div class="auth-card">
    <img src="<?= asset('img/logo-light.png') ?>" alt="Tontine HDJ" class="auth-logo">
    <h1>Plateforme des Tontines</h1>
    <p class="sub">Hôpital Dalal Jamm — Espace sécurisé</p>

    <form method="post" action="<?= url('auth/login') ?>" autocomplete="off">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label-hdj d-block" for="username">Identifiant</label>
        <input class="form-control-hdj" type="text" id="username" name="username"
               placeholder="ex : admin" value="<?= e(old('username')) ?>" autofocus required>
      </div>
      <div class="mb-4">
        <label class="form-label-hdj d-block" for="password">Mot de passe</label>
        <input class="form-control-hdj" type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-hdj w-100 justify-content-center" style="padding:12px;">
        Se connecter
      </button>
    </form>

    <p class="text-center mt-4 mb-0" style="font-size:.78rem;color:var(--muted);">
      Accès réservé au Super Administrateur et au Président.
    </p>
  </div>
</div>
