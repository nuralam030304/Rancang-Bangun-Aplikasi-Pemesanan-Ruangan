<?php
session_start();
include __DIR__ . '/../db.php';

// Cek login dan role admin
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role_name'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil semua data pemesanan
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';
$q = $_GET['q'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
    $where .= " AND b.status = ?";
    $params[] = $status;
}

if ($q) {
    $where .= " AND (r.name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

// Hitung total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b 
                      JOIN rooms r ON b.room_id = r.id 
                      JOIN users u ON b.user_id = u.id 
                      $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Ambil data dengan pagination
$offset = ($page - 1) * $perPage;
$sql = "SELECT b.*, r.name as room_name, r.code as room_code, 
               u.name as user_name, u.email as user_email
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        JOIN users u ON b.user_id = u.id 
        $where 
        ORDER BY b.created_at DESC 
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="d-flex justify-between align-center mb-3">
                <h1>Daftar Pesanan</h1>
                <a href="../../public/index.php" class="btn">Kembali</a>
            </div>

            <!-- Filter dan Pencarian -->
            <form method="get" class="mb-3">
                <div class="d-flex gap-2" style="gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" 
                           placeholder="Cari ruangan atau user..." class="input-text" style="flex: 1;">
                    <select name="status" class="input-text">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                    <button type="submit" class="btn">Cari</button>
                </div>
            </form>

            <!-- Statistik Cepat -->
            <div class="stats-grid mb-3">
                <?php
                $stats = $pdo->query("
                    SELECT status, COUNT(*) as total 
                    FROM bookings 
                    GROUP BY status
                ")->fetchAll();
                
                $status_totals = [];
                foreach ($stats as $stat) {
                    $status_totals[$stat['status']] = $stat['total'];
                }
                ?>
                <div class="stat-item">
                    <div class="stat-number"><?= $status_totals['pending'] ?? 0 ?></div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $status_totals['approved'] ?? 0 ?></div>
                    <div class="stat-label">Disetujui</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $status_totals['rejected'] ?? 0 ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $total ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>

            <!-- Tabel Pesanan -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>User</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada pesanan</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($order['room_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($order['room_code']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($order['user_name']) ?><br>
                                        <small><?= htmlspecialchars($order['user_email']) ?></small>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($order['date_start'])) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($order['date_end'])) ?></td>
                                    <td><?= htmlspecialchars($order['purpose']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <a href="approve_order.php?id=<?= $order['id'] ?>" class="btn-warning">Setujui</a>
                                            <a href="reject_order.php?id=<?= $order['id'] ?>" class="btn-danger">Tolak</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total > 0): ?>
                <?php $pages = max(1, ceil($total / $perPage)); ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $pages; $i++): ?>
                        <a class="<?= $i == $page ? 'active' : '' ?>" 
                           href="?page=<?= $i ?><?= $status ? '&status='.$status : '' ?><?= $q ? '&q='.urlencode($q) : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .stat-item {
            background: linear-gradient(135deg, #e91e63, #ff7bbd);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .text-muted {
            color: #999;
        }
        
        .gap-2 {
            gap: 10px;
        }
    </style>
</body>
</html>