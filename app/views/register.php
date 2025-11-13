<?php
session_start();
$pdo = require __DIR__ . '/../db.php';

// Jika sudah login, redirect ke home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$name = $email = $password = $confirm = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Semua field wajib diisi!'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format email tidak valid!'];
    } elseif ($password !== $confirm) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Konfirmasi password tidak cocok!'];
    } else {
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email sudah terdaftar!'];
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $role_id = 2; // default user
            $stmt = $pdo->prepare("INSERT INTO users (role_id, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$role_id, $name, $email, $hashed]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registrasi berhasil! Silakan login.'];
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun - Booking Ruangan</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<script>
function togglePassword(id) {
  const field = document.getElementById(id);
  field.type = field.type === "password" ? "text" : "password";
}
</script>

<body>
  <div class="container">
    <h2>Buat Akun Baru 📝</h2>
    <p>Silakan isi formulir untuk membuat akun.</p>

    <?php if (isset($_SESSION['flash'])): ?>
      <div class="notif <?= $_SESSION['flash']['type']; ?>">
        <?= $_SESSION['flash']['message']; ?>
      </div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form name="regForm" method="POST" onsubmit="return validateForm()">
      <div class="form-group">
        <label for="name">Nama Lengkap</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name); ?>" placeholder="Masukkan nama lengkap" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email); ?>" placeholder="Masukkan email aktif" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Masukkan password" required oninput="checkPasswordStrength()">

        <div id="password-strength" class="password-strength"></div>
      </div>

      <div class="form-group">
        <label for="confirm">Konfirmasi Password</label>
        <input type="password" name="confirm" id="confirm" placeholder="Ulangi password" required>
        
      </div>

      <button type="submit" class="btn" id="submit-btn">Daftar Sekarang</button>
    </form>

    <p style="text-align:center;margin-top:20px;">
      Sudah punya akun? <a href="login.php">Login di sini</a>
    </p>
  </div>

  <script>
    function validateForm() { /* ... */ }
    function togglePassword(id) { /* ... */ }
    function checkPasswordStrength() { /* ... */ }
  </script>
</body>
</html>
