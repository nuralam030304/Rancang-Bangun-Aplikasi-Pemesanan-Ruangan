<?php
require_once __DIR__ . '/controllers/RoomController.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$pdo = require __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';

function login_user($email, $password, $remember = false) {
  global $pdo, $config;
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if (!$user) return false;
  if (!password_verify($password, $user['password'])) return false;

  session_regenerate_id(true);
  $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role_id'=>$user['role_id']];

  if ($remember) {
    $token = bin2hex(random_bytes(32));
    $hashed = hash('sha256', $token);
    $stmt = $pdo->prepare('UPDATE users SET remember_token=? WHERE id=?');
    $stmt->execute([$hashed, $user['id']]);
    setcookie('remember', $user['id'].':'.$token, time() + (60*60*24*$config['remember_days']), '/', '', $config['cookie_secure'], true);
  }
  log_activity($pdo, $user['id'], 'login');
  return true;
}

function logout_user() {
  global $pdo;
  if (!empty($_SESSION['user'])) log_activity($pdo, $_SESSION['user']['id'], 'logout');
  setcookie('remember', '', time()-3600, '/');
  session_unset();
  session_destroy();
}

function check_remember() {
  global $pdo;
  if (!empty($_SESSION['user'])) return;
  if (empty($_COOKIE['remember'])) return;
  $parts = explode(':', $_COOKIE['remember']);
  if (count($parts) !== 2) return;
  [$id, $token] = $parts;
  $stmt = $pdo->prepare('SELECT remember_token FROM users WHERE id=?');
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if ($row && hash_equals($row['remember_token'], hash('sha256', $token))) {
    $stmt = $pdo->prepare('SELECT id,name,email,role_id FROM users WHERE id=?');
    $stmt->execute([$id]);
    $_SESSION['user'] = $stmt->fetch();
  }
}
check_remember();
