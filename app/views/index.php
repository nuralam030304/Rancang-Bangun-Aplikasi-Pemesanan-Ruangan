<?php
session_start();
require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

// Helper flash message
function flash($key, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $tmp = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $tmp;
        }
    }
    return null;
}

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$q = $_GET['q'] ?? '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle actions
switch ($action) {
    case 'delete':
        if ($id) {
            handleDeleteRoom($id);
        }
        break;
        
    case 'edit':
        if ($id) {
            displayEditForm($id);
            exit;
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            handleUpdateRoom();
        }
        break;
        
    case 'create':
        displayCreateForm();
        exit;
        
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handleCreateRoom();
        }
        break;
}

displayRoomsList();

function handleDeleteRoom($id) {
    global $pdo;
    
    $id = (int)$id;
    
    $stmt = $pdo->prepare('SELECT image, name FROM rooms WHERE id = ?');
    $stmt->execute([$id]);
    $room = $stmt->fetch();
    
    if ($room) {
        // Delete room from database
        $stmt = $pdo->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($room['image']) {
            $image_path = __DIR__ . '/../../uploads/' . $room['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $_SESSION['flash']['success'] = 'Ruangan ' . e($room['name']) . ' berhasil dihapus';
    } else {
        $_SESSION['flash']['error'] = 'Ruangan tidak ditemukan';
    }
    
    // Redirect back to rooms list
    header('Location: index.php');
    exit;
}

// Function to handle room update
function handleUpdateRoom() {
    global $pdo;
    
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $category_id = (int)$_POST['category_id'];
    $capacity = (int)$_POST['capacity'];
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = handleImageUpload($_FILES['image']);
    }
    
    // Update room data
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    if ($image) {
        $stmt = $pdo->prepare('UPDATE rooms SET name = ?, code = ?, category_id = ?, capacity = ?, image = ?, latitude = ?, longitude = ? WHERE id = ?');
        $stmt->execute([$name, $code, $category_id, $capacity, $image, $latitude, $longitude, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE rooms SET name = ?, code = ?, category_id = ?, capacity = ?, latitude = ?, longitude = ? WHERE id = ?');
        $stmt->execute([$name, $code, $category_id, $capacity, $latitude, $longitude, $id]);
    }
    
    $_SESSION['flash']['success'] = 'Ruangan ' . e($name) . ' berhasil diperbarui';
    header('Location: index.php');
    exit;
}
    

// Function to handle room creation
function handleCreateRoom() {
    global $pdo;
    
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $category_id = (int)$_POST['category_id'];
    $capacity = (int)$_POST['capacity'];
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = handleImageUpload($_FILES['image']);
    }
    
    // Insert new room
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $pdo->prepare('INSERT INTO rooms (name, code, category_id, capacity, image, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $code, $category_id, $capacity, $image, $latitude, $longitude]);
    
    $_SESSION['flash']['success'] = 'Ruangan ' . e($name) . ' berhasil ditambahkan';
    header('Location: index.php');
    exit;
}

// Function to display rooms list
function displayRoomsList() {
    global $pdo, $perPage, $page, $q;
    
    $where = '';
    $params = [];

    if ($q) {
        $where = "WHERE r.name LIKE ? OR r.code LIKE ?";
        $params = ["%$q%", "%$q%"];
    }

    // Hitung total data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms r $where");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Ambil data dengan pagination
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT r.*, c.name as category_name 
            FROM rooms r 
            LEFT JOIN categories c ON r.category_id = c.id 
            $where 
            ORDER BY r.id DESC 
            LIMIT $perPage OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Daftar Ruangan</title>
      <link rel="stylesheet" href="../../public/assets/css/style.css">
    </head>
    <body>
      <header>
        <h1>Daftar Ruangan</h1>
      </header>

      <div class="container">
        <div class="card">
          <div class="d-flex justify-between align-center mb-3">
            <h2>Data Ruangan</h2>
            <a href="../../public/index.php" class="btn">Kembali</a>
            <a href="index.php?action=create" class="btn">Tambah Ruangan</a>
          </div>

          <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success">
              <?= e($_SESSION['flash']['success']) ?>
              <?php unset($_SESSION['flash']['success']); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error">
              <?= e($_SESSION['flash']['error']) ?>
              <?php unset($_SESSION['flash']['error']); ?>
            </div>
          <?php endif; ?>

          <form method="get" action="index.php" class="mb-3">
            <input type="hidden" name="action" value="list">
            <div class="form-group">
              <input name="q" value="<?= e($q) ?>" placeholder="Cari nama atau kode..." class="input-text" />
              <button class="btn btn-outline">Cari</button>
            </div>
          </form>

          <table class="table">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Kapasitas</th>
                <th>Gambar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr>
                  <td colspan="6" class="text-center">Tidak ada data ruangan</td>
                </tr>
              <?php else: ?>
                <?php foreach($rows as $r): ?>
                <tr>
                  <td><strong><?= e($r['code']) ?></strong></td>
                  <td><?= e($r['name']) ?></td>
                  <td><?= e($r['category_name']) ?></td>
                  <td><?= e($r['capacity']) ?></td>
                  <td>
                    <?php if ($r['image']): ?>
                      <img src="/uploads/<?= e($r['image']) ?>" alt="<?= e($r['name']) ?>" class="table-img">
                    <?php else: ?>
                      <span>-</span>
                    <?php endif; ?>
                  </td>
                  <td class="actions">
                    <a href="index.php?action=edit&id=<?= $r['id'] ?>" class="btn-warning">Edit</a>
                    <a href="index.php?action=delete&id=<?= $r['id'] ?>" class="btn-danger" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus ruangan <?= e($r['name']) ?>?')">Hapus</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>

          <?php if ($total > 0): ?>
            <?php $pages = max(1, ceil($total / $perPage)); ?>
            <div class="pagination">
              <?php for($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i == $page ? 'active' : '' ?>" 
                   href="index.php?action=list&page=<?= $i ?><?= $q ? '&q='.urlencode($q): '' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <footer>
        <p>© <?= date('Y') ?> Sistem Ruangan</p>
      </footer>
    </body>
    </html>
    <?php
}

function displayEditForm($id) {
    global $pdo;
    
    $id = (int)$id;
    
    $stmt = $pdo->prepare('SELECT r.*, c.name as category_name FROM rooms r LEFT JOIN categories c ON r.category_id = c.id WHERE r.id = ?');
    $stmt->execute([$id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        $_SESSION['flash']['error'] = 'Ruangan tidak ditemukan';
        header('Location: index.php');
        exit;
    }
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Ruangan</title>
        <link rel="stylesheet" href="../../public/assets/css/style.css">
        <!-- LeafletJS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            #map { height: 300px; width: 100%; border-radius: 8px; margin-top: 10px; z-index: 1; }
        </style>
    </head>
    <body>
        <header>
            <h1>Edit Ruangan</h1>
        </header>

        <div class="container">
            <div class="card">
                <h2>Edit Ruangan</h2>
                
                <form method="post" action="index.php?action=update" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= e($room['id']) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Kode Ruangan</label>
                        <input type="text" name="code" class="form-control" value="<?= e($room['code']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" name="name" class="form-control" value="<?= e($room['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= e($cat['id']) ?>" <?= $cat['id'] == $room['category_id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="capacity" class="form-control" value="<?= e($room['capacity']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gambar (Opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($room['image']): ?>
                            <div>
                                <p>Gambar saat ini:</p>
                                <img src="/uploads/<?= e($room['image']) ?>" alt="Current image" class="current-image">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <div class="d-flex justify-between align-center">
                            <label class="form-label">Lokasi (Klik pada peta)</label>
                            <button type="button" id="btn-location" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.8rem;">📍 Gunakan Lokasi Saya</button>
                        </div>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latitude" value="<?= e($room['latitude'] ?? '') ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= e($room['longitude'] ?? '') ?>">
                        <p class="text-small text-muted">Koordinat: <span id="coords"><?= ($room['latitude'] && $room['longitude']) ? e($room['latitude']) . ', ' . e($room['longitude']) : 'Belum dipilih' ?></span></p>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var lat = <?= $room['latitude'] ? $room['latitude'] : '-6.175392' ?>;
                            var lng = <?= $room['longitude'] ? $room['longitude'] : '106.827153' ?>;
                            var map = L.map('map').setView([lat, lng], 15);

                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);

                            var marker;
                            <?php if ($room['latitude'] && $room['longitude']): ?>
                                marker = L.marker([lat, lng]).addTo(map);
                            <?php endif; ?>

                            map.on('click', function(e) {
                                updateLocation(e.latlng.lat, e.latlng.lng);
                            });

                            document.getElementById('btn-location').addEventListener('click', function() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(function(position) {
                                        updateLocation(position.coords.latitude, position.coords.longitude);
                                        map.setView([position.coords.latitude, position.coords.longitude], 16);
                                    }, function(error) {
                                        alert("Gagal mendapatkan lokasi: " + error.message);
                                    });
                                } else {
                                    alert("Browser Anda tidak mendukung Geolocation.");
                                }
                            });

                            function updateLocation(lat, lng) {
                                if (marker) {
                                    marker.setLatLng([lat, lng]);
                                } else {
                                    marker = L.marker([lat, lng]).addTo(map);
                                }
                                document.getElementById('latitude').value = lat;
                                document.getElementById('longitude').value = lng;
                                document.getElementById('coords').innerText = lat.toFixed(6) + ', ' + lng.toFixed(6);
                            }
                        });
                    </script>

                    <div class="form-actions">
                        <button type="submit" class="btn">Simpan Perubahan</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function displayCreateForm() {
    global $pdo;
    
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tambah Ruangan</title>
        <link rel="stylesheet" href="../../public/assets/css/style.css">
        <!-- LeafletJS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            #map { height: 300px; width: 100%; border-radius: 8px; margin-top: 10px; z-index: 1; }
        </style>
    </head>
    <body>
        <header>
            <h1>Tambah Ruangan Baru</h1>
        </header>

        <div class="container">
            <div class="card">
                <h2>Tambah Ruangan Baru</h2>
                
                <form method="post" action="index.php?action=store" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Kode Ruangan</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Ruangan</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="capacity" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gambar (Opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <div class="d-flex justify-between align-center">
                            <label class="form-label">Lokasi (Klik pada peta)</label>
                            <button type="button" id="btn-location" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 0.8rem;">📍 Gunakan Lokasi Saya</button>
                        </div>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <p class="text-small text-muted">Koordinat: <span id="coords">Belum dipilih</span></p>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var map = L.map('map').setView([-6.175392, 106.827153], 15);

                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);

                            var marker;

                            map.on('click', function(e) {
                                updateLocation(e.latlng.lat, e.latlng.lng);
                            });

                            document.getElementById('btn-location').addEventListener('click', function() {
                                if (navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(function(position) {
                                        updateLocation(position.coords.latitude, position.coords.longitude);
                                        map.setView([position.coords.latitude, position.coords.longitude], 16);
                                    }, function(error) {
                                        alert("Gagal mendapatkan lokasi: " + error.message);
                                    });
                                } else {
                                    alert("Browser Anda tidak mendukung Geolocation.");
                                }
                            });

                            function updateLocation(lat, lng) {
                                if (marker) {
                                    marker.setLatLng([lat, lng]);
                                } else {
                                    marker = L.marker([lat, lng]).addTo(map);
                                }
                                document.getElementById('latitude').value = lat;
                                document.getElementById('longitude').value = lng;
                                document.getElementById('coords').innerText = lat.toFixed(6) + ', ' + lng.toFixed(6);
                            }
                        });
                    </script>

                    <div class="form-actions">
                        <button type="submit" class="btn">Simpan</button>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function handleImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['flash']['error'] = 'Format file tidak didukung. Hanya JPEG, PNG, dan GIF yang diizinkan.';
        return null;
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['flash']['error'] = 'Ukuran file terlalu besar. Maksimal 2MB.';
        return null;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $upload_path = __DIR__ . '/../../uploads/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    $_SESSION['flash']['error'] = 'Gagal mengupload gambar.';
    return null;
}
?>
}