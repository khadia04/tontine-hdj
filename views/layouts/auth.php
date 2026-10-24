<?php /** Layout d'authentification — TONTINE HDJ */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Connexion') ?> — TONTINE HDJ</title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo-dark.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
  <?= $content ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($err = flash('error')): ?>
  <script>Swal.fire({toast:true,position:'top-end',icon:'error',title:<?= json_encode($err, JSON_UNESCAPED_UNICODE) ?>,showConfirmButton:false,timer:3600,timerProgressBar:true});</script>
  <?php endif; ?>
  <?php if ($ok = flash('success')): ?>
  <script>Swal.fire({toast:true,position:'top-end',icon:'success',title:<?= json_encode($ok, JSON_UNESCAPED_UNICODE) ?>,showConfirmButton:false,timer:3200,timerProgressBar:true});</script>
  <?php endif; ?>
</body>
</html>
