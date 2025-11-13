<?php // app/views/auth/forgot.php
// POST handler: buat token (random_bytes), simpan hash ke password_resets, kirim email.
// form:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pdo = require __DIR__ . '/../../app/db.php';
  $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if ($user) {
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 jam
    // hapus token lama untuk user ini (opsional)
    $pdo->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$user['id']]);
    $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)');
    $ins->execute([$user['id'], $token_hash, $expires]);

    // kirim email menggunakan fungsi sendResetEmail($email, $token, $userId) [contoh PHPMailer di bawah]
    require __DIR__ . '/../../app/helpers.php';
    require __DIR__ . '/../../app/mail_helper.php'; // file contoh PHPMailer helper (saya sertakan di bawah)
    sendResetEmail($email, $token, $user['id']); // implementasi di mail_helper.php
    flash('success','Link reset password telah dikirim ke email jika terdaftar.');
  } else {
    // jangan beri tahu apakah email ada atau tidak (privacy)
    flash('success','Link reset password telah dikirim ke email jika terdaftar.');
  }
  header('Location: /?p=forgot'); exit;
}
?>
<div class="container py-5" style="max-width:480px;">
  <h4 class="mb-3">Lupa Password</h4>
  <form method="post" action="/?p=forgot">
    <div class="mb-3">
      <label class="form-label">Email terdaftar</label>
      <input name="email" type="email" class="form-control" required>
    </div>
    <button class="btn btn-primary">Kirim Link Reset</button>
  </form>
</div>
