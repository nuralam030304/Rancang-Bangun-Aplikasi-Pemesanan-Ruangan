<?php // app/views/auth/reset.php
$uid = $_GET['uid'] ?? null;
$token = $_GET['token'] ?? null;
$pdo = require __DIR__ . '/../../app/db.php';

if (!$uid || !$token) {
  echo '<div class="container py-4">Token tidak valid.</div>'; exit;
}

// verify token
$stmt = $pdo->prepare('SELECT * FROM password_resets WHERE user_id=? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$uid]);
$row = $stmt->fetch();
if (!$row || $row['expires_at'] < date('Y-m-d H:i:s')) {
  echo '<div class="container py-4">Token kadaluarsa atau tidak ditemukan.</div>'; exit;
}
if (!hash_equals($row['token_hash'], hash('sha256', $token))) {
  echo '<div class="container py-4">Token tidak valid.</div>'; exit;
}

// jika submit ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pw = $_POST['password'] ?? '';
  $pw2 = $_POST['password_confirm'] ?? '';
  if ($pw === '' || strlen($pw) < 6) {
    flash('errors', ['pw'=>'Password minimal 6 karakter']); header("Location: /?p=reset&uid=$uid&token=$token"); exit;
  }
  if ($pw !== $pw2) {
    flash('errors', ['pwc'=>'Password tidak cocok']); header("Location: /?p=reset&uid=$uid&token=$token"); exit;
  }
  // update user password
  $hash = password_hash($pw, PASSWORD_DEFAULT);
  $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $uid]);
  // hapus token reset
  $pdo->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$uid]);
  flash('success','Password berhasil diubah. Silakan login.');
  header('Location: /login'); exit;
}
?>

<div class="container py-5" style="max-width:480px;">
  <h4 class="mb-3">Reset Password</h4>
  <form method="post" action="/?p=reset&uid=<?= e($uid) ?>&token=<?= e($token) ?>">
    <div class="mb-3">
      <label class="form-label">Password baru</label>
      <input name="password" type="password" class="form-control" required minlength="6">
    </div>
    <div class="mb-3">
      <label class="form-label">Konfirmasi password</label>
      <input name="password_confirm" type="password" class="form-control" required minlength="6">
    </div>
    <button class="btn btn-primary">Ganti Password</button>
  </form>
</div>
