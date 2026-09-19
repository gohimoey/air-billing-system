<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$u = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($title) ? esc($title) . ' - ' : '' ?>Tagihan Air</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL ?>/includes/design.css" rel="stylesheet">
</head>
<body>

<?php if ($u): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/<?= is_admin() ? 'admin' : 'customer' ?>/index.php">
      <i class="bi bi-droplet-fill me-1"></i>Tagihan Air
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php if (is_admin()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/customers.php"><i class="bi bi-people me-1"></i>Pelanggan</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/readings.php"><i class="bi bi-speedometer me-1"></i>Meter</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/bills.php"><i class="bi bi-receipt me-1"></i>Tagihan</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/payments.php"><i class="bi bi-cash-coin me-1"></i>Pembayaran</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-bar-chart me-1"></i>Laporan</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/customer/index.php"><i class="bi bi-house me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/customer/bills.php"><i class="bi bi-file-earmark-text me-1"></i>Tagihan</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/customer/profile.php"><i class="bi bi-person me-1"></i>Profil</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i><?= esc($u['name']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<?php endif; ?>

<div class="container pb-5">
<?php foreach (flash() as $type => $msg): ?>
  <div class="alert alert-<?= esc($type) ?> alert-dismissible fade show" role="alert">
    <?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endforeach; ?>