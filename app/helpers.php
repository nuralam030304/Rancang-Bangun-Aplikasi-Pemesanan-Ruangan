<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('flash')) {
  function flash($key = null, $value = null) {
    if ($key !== null && $value !== null) {
      $_SESSION['flash'][$key] = $value;
      return true;
    }
    if ($key !== null) {
      $val = $_SESSION['flash'][$key] ?? null;
      unset($_SESSION['flash'][$key]);
      return $val;
    }
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
  }
}

if (!function_exists('old')) {
  function old($name) {
    return $_SESSION['old'][$name] ?? '';
  }
}

if (!function_exists('set_old')) {
  function set_old($data) {
    $_SESSION['old'] = $data;
  }
}

if (!function_exists('e')) {
  function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('require_auth')) {
  function require_auth() {
    if (empty($_SESSION['user'])) {
      header('Location: /views/login');
      exit;
    }
  }
}

if (!function_exists('log_activity')) {
  function log_activity($pdo, $user_id, $action) {
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, ip, user_agent) VALUES (?,?,?,?)');
    $stmt->execute([$user_id, $action, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ??null]);
}
}