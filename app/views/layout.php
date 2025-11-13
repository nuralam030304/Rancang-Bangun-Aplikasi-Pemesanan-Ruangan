<?php
// app/views/layout.php
$config = require __DIR__ . '/../config.php';
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Booking Ruangan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="/">Booking Ruangan</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if (!empty($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link" href="/?p=rooms">Ruangan</a></li>
          <li class="nav-item"><a class="nav-link" href="/?p=logout">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="../app/views/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="../app/views/register.php">daftar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4" id="flash-container">
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($errs = flash('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach($errs as $k=>$v) echo '<div>'.e($v).'</div>'; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
</div>

<?= $content ?? '' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
