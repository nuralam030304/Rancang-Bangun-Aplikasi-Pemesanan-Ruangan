<?php
// public/index.php
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/auth.php';
$pdo = require __DIR__ . '/../app/db.php';

$page = $_GET['p'] ?? 'home';
ob_start();

switch ($page) {
  case 'login':
    require __DIR__ . '/../app/views/login.php';
    break;
  case 'logout':
    logout_user();
    header('Location: /login'); exit;
    break;
  case 'rooms':
    require __DIR__ . '/../app/controllers/RoomController.php';
    \RoomController\index();
    break;
  case 'rooms_create':
    require __DIR__ . '/../app/controllers/RoomController.php';
    \RoomController\create();
    break;
  default:
    require __DIR__ . '/../app/views/home.php';
}
$content = ob_get_clean();
require __DIR__ . '/../app/views/layout.php';
