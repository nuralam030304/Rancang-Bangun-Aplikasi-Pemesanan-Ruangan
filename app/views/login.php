<?php // app/views/auth/login.php
require_once __DIR__ . '/../../app/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Handler sederhana: gunakan fungsi login_user($email,$password,$remember) dari auth.php
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  $remember = !empty($_POST['remember']);
  if (\login_user($email, $pass, $remember)) {
    header('Location: ./index.php'); exit;
  } else {
    flash('errors', ['login.php' => 'Email atau password salah']);
    header('Location: login.php'); exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Masuk ke Akun</h2>
      <form action="login.php" method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Masukkan email kamu" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password" required>

        <button type="submit" class="btn">Login</button>
      </form>
      <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
  </div>
</body>
</html>

