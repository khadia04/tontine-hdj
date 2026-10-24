<?php /** 404 — TONTINE HDJ */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page introuvable — TONTINE HDJ</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="auth-wrap">
    <div class="auth-card text-center">
      <img src="<?= asset('img/logo-light.png') ?>" alt="" class="auth-logo">
      <h1 style="font-size:3rem;">404</h1>
      <p class="sub">La page demandée est introuvable.</p>
      <a href="<?= url('dashboard') ?>" class="btn-hdj">Retour au tableau de bord</a>
    </div>
  </div>
</body>
</html>
