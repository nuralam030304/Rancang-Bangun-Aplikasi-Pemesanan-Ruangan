<?php
// app/controllers/RoomController.php
namespace RoomController;
require __DIR__ . '/../helpers.php';
$pdo = require __DIR__ . '/../db.php';
$config = require __DIR__ . '/../config.php';

function index() {
  global $pdo;
  require_auth();
  $q = trim($_GET['q'] ?? '');
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 10;
  $offset = ($page-1)*$perPage;

  if ($q !== '') {
    $stmt = $pdo->prepare('SELECT r.*, c.name as category_name FROM rooms r JOIN categories c ON r.category_id=c.id WHERE r.name LIKE ? OR r.code LIKE ? LIMIT ? OFFSET ?');
    $stmt->execute(["%$q%","%$q%",$perPage,$offset]);
    $rows = $stmt->fetchAll();
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM rooms r WHERE r.name LIKE ? OR r.code LIKE ?');
    $countStmt->execute(["%$q%","%$q%"]);
    $total = $countStmt->fetchColumn();
  } else {
    $stmt = $pdo->prepare('SELECT r.*, c.name as category_name FROM rooms r JOIN categories c ON r.category_id=c.id LIMIT ? OFFSET ?');
    $stmt->execute([$perPage,$offset]);
    $rows = $stmt->fetchAll();
    $total = $pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
  }
  $perPage = (int)$perPage;
  require __DIR__ . '/../views/rooms/index.php';
}

function create() {
  global $pdo, $config;
  require_auth();
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = (int)($_POST['category_id'] ?? 0);
    $capacity = (int)($_POST['capacity'] ?? 0);

    $errors = [];
    if ($name === '') $errors['name'] = 'Nama ruangan wajib diisi';
    if ($category <= 0) $errors['category_id'] = 'Kategori wajib dipilih';
    if ($capacity <= 0) $errors['capacity'] = 'Kapasitas harus > 0';

    // upload
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $f = $_FILES['image'];
      $allowed = ['image/jpeg','image/png','image/webp'];
      if ($f['size'] > $config['max_upload_size']) $errors['image'] = 'File terlalu besar';
      if (!in_array(mime_content_type($f['tmp_name']), $allowed)) $errors['image'] = 'Format gambar tidak diizinkan';
      if (empty($errors['image'])) {
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $fn = uniqid('room_') . '.' . $ext;
        if (!is_dir($config['upload_dir'])) mkdir($config['upload_dir'], 0755, true);
        move_uploaded_file($f['tmp_name'], $config['upload_dir'] . '/' . $fn);
        $image = $fn;
      }
    }

    if (empty($errors)) {
      $code = 'RM' . strtoupper(substr(preg_replace('/\s+/', '', $name),0,4)) . rand(10,99);
      $stmt = $pdo->prepare('INSERT INTO rooms (category_id, code, name, capacity, image) VALUES (?,?,?,?,?)');
      $stmt->execute([$category, $code, $name, $capacity, $image ?? null]);
      flash('success','Ruangan berhasil dibuat');
      log_activity($pdo, $_SESSION['user']['id'] ?? null, 'create_room');
      header('Location: /../views/rooms/index.php'); exit;
    }
    set_old($_POST);
    flash('errors',$errors);
    header('Location: ' . $_SERVER['REQUEST_URI']); exit;
  }
  $cats = $pdo->query('SELECT * FROM categories')->fetchAll();
  require __DIR__ . '/../views/rooms/create.php';
}
