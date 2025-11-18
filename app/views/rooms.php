<?php
session_start();
include __DIR__ . '/../db.php';

// Cek login
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: login.php');
    exit;
}

// Ambil data ruangan
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$q = $_GET['q'] ?? '';
$category_id = $_GET['category_id'] ?? '';

$where = "WHERE r.capacity > 0";
$params = [];

if ($q) {
    $where .= " AND (r.name LIKE ? OR r.code LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($category_id && is_numeric($category_id)) {
    $where .= " AND r.category_id = ?";
    $params[] = $category_id;
}

// Hitung total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rooms r $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Ambil data dengan pagination
$offset = ($page - 1) * $perPage;
$sql = "SELECT r.*, c.name as category_name 
        FROM rooms r 
        LEFT JOIN categories c ON r.category_id = c.id 
        $where 
        ORDER BY r.name 
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

// Ambil kategori untuk filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ruangan - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="d-flex justify-between align-center mb-3">
                <h1>Daftar Ruangan</h1>
                <a href="../../public/index.php" class="btn">Kembali</a>
            </div>

            <!-- Filter dan Pencarian -->
            <form method="get" class="mb-3">
                <div class="d-flex gap-2" style="gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" 
                           placeholder="Cari ruangan..." class="input-text" style="flex: 1;">
                    <select name="category_id" class="input-text" style="min-width: 200px;">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $category_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Cari</button>
                </div>
            </form>

            <!-- Daftar Ruangan -->
            <div class="rooms-grid">
                <?php if (empty($rooms)): ?>
                    <div class="text-center" style="padding: 40px;">
                        <p>Tidak ada ruangan ditemukan</p>
                    </div>
                <?php else: ?>
                    <?php foreach($rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-image">
                                <?php if ($room['image']): ?>
                                    <img src="/uploads/<?= htmlspecialchars($room['image']) ?>" 
                                         alt="<?= htmlspecialchars($room['name']) ?>">
                                <?php else: ?>
                                    <div class="no-image">🏢</div>
                                <?php endif; ?>
                            </div>
                            <div class="room-info">
                                <h3><?= htmlspecialchars($room['name']) ?></h3>
                                <p class="room-code">Kode: <?= htmlspecialchars($room['code']) ?></p>
                                <p class="room-category">Kategori: <?= htmlspecialchars($room['category_name']) ?></p>
                                <p class="room-capacity">Kapasitas: <?= htmlspecialchars($room['capacity']) ?> orang</p>
                                <a href="booking_form.php?room_id=<?= $room['id'] ?>" class="btn" style="width: 100%; margin-top: 10px;">
                                    📅 Pesan Ruangan
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total > 0): ?>
                <?php $pages = max(1, ceil($total / $perPage)); ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $pages; $i++): ?>
                        <a class="<?= $i == $page ? 'active' : '' ?>" 
                           href="?page=<?= $i ?><?= $q ? '&q='.urlencode($q) : '' ?><?= $category_id ? '&category_id='.$category_id : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .room-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
        }
        
        .room-image {
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-image {
            font-size: 4rem;
            color: #e91e63;
        }
        
        .room-info {
            padding: 20px;
        }
        
        .room-info h3 {
            margin: 0 0 10px 0;
            color: #e91e63;
        }
        
        .room-code, .room-category, .room-capacity {
            margin: 5px 0;
            color: #666;
        }
        
        .gap-2 {
            gap: 10px;
        }
    </style>
</body>
</html>