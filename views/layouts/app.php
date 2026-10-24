<?php
/** Layout principal — TONTINE HDJ */
$u = Auth::user();
$initiales = '';
foreach (preg_split('/\s+/', trim($u['name'] ?? 'U')) as $mot) {
    if ($mot !== '') $initiales .= mb_strtoupper(mb_substr($mot, 0, 1));
}
$initiales = mb_substr($initiales, 0, 2) ?: 'U';

// Détection du contrôleur actif pour la navigation
$rawRoute = $_GET['r'] ?? '';
if ($rawRoute === '') {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
    $base = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
    if ($base !== '' && str_starts_with($uri, $base)) $uri = trim(substr($uri, strlen($base)), '/');
    if (str_starts_with($uri, 'index.php')) $uri = trim(substr($uri, 9), '/');
    $rawRoute = $uri;
}
$activeCtrl = explode('/', $rawRoute)[0] ?: 'dashboard';
$navActive = fn(string $k) => $activeCtrl === $k ? ' active' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'TONTINE HDJ') ?> — TONTINE HDJ</title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo-dark.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
  <script>window.BASE = <?= json_encode(BASE_URL) ?>; window.CSRF = <?= json_encode(csrf_token()) ?>;</script>
</head>
<body>
<div class="layout">
  <!-- ============ SIDEBAR ============ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <img src="<?= asset('img/logo-light.png') ?>" alt="Tontine HDJ" style="background:#fff;border-radius:12px;padding:4px;">
      <span class="b-text">TONTINE<small>HÔPITAL DALAL JAMM</small></span>
    </div>

    <nav>
      <div class="nav-section">Pilotage</div>
      <a class="nav-link-hdj<?= $navActive('dashboard') ?>" href="<?= url('dashboard') ?>"><?= ic('grid') ?> Tableau de bord</a>
      <a class="nav-link-hdj<?= $navActive('tontines') ?>" href="<?= url('tontines') ?>"><?= ic('layers') ?> Tontines</a>
      <?php if (Auth::isSuperAdmin()): ?>
      <a class="nav-link-hdj<?= $navActive('utilisateurs') ?>" href="<?= url('utilisateurs') ?>"><?= ic('users') ?> Présidents</a>
      <?php endif; ?>

      <div class="nav-section">Compte</div>
      <a class="nav-link-hdj<?= $navActive('profil') ?>" href="<?= url('profil') ?>"><?= ic('user') ?> Mon profil</a>
      <a class="nav-link-hdj" href="<?= url('logout') ?>"><?= ic('logout') ?> Déconnexion</a>
    </nav>

    <div class="sidebar-foot">
      © <?= date('Y') ?> Hôpital Dalal Jamm<br>Plateforme de gestion des tontines
    </div>
  </aside>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- ============ MAIN ============ -->
  <div class="main">
    <header class="topbar">
      <button class="burger" id="burger" aria-label="Menu"><?= ic('menu') ?></button>
      <div style="flex:1;min-width:0;">
        <h1><?= e($title ?? 'Tableau de bord') ?></h1>
        <div class="crumb">Bienvenue, <?= e($u['name'] ?? '') ?></div>
      </div>
      <a class="userchip" href="<?= url('profil') ?>" title="Mon profil">
        <span class="av"><?= e($initiales) ?></span>
        <span class="nm"><?= e($u['name'] ?? '') ?><small><?= e(Auth::roleLabel()) ?></small></span>
      </a>
    </header>

    <main class="content">
      <?php if ($ok = flash('success')): ?>
        <div class="flash success"><?= e($ok) ?></div>
      <?php endif; ?>
      <?php if ($err = flash('error')): ?>
        <div class="flash error"><?= e($err) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
