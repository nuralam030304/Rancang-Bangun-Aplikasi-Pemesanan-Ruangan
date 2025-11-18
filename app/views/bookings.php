<?php
session_start();
include __DIR__ . '/../db.php';

// Cek login
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: login.php');
    exit;
}

// Ambil data pemesanan user
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';

$where = "WHERE b.user_id = ?";
$params = [$user['id']];

if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
    $where .= " AND b.status = ?";
    $params[] = $status;
}

// Hitung total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Ambil data dengan pagination
$offset = ($page - 1) * $perPage;
$sql = "SELECT b.*, r.name as room_name, r.code as room_code, r.image as room_image
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        $where 
        ORDER BY b.created_at DESC 
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Saya - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="d-flex justify-between align-center mb-3">
                <h1>Pemesanan Saya</h1>
                <div>
                    <a href="rooms.php" class="btn">➕ Pesan Ruangan Baru</a>
                    <a href="../../public/index.php" class="btn">Kembali</a>
                </div>
            </div>

            <!-- Filter Status -->
            <form method="get" class="mb-3">
                <div class="d-flex gap-2" style="gap: 10px;">
                    <select name="status" class="input-text" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
            </form>

            <!-- Daftar Pemesanan -->
            <div class="bookings-list">
                <?php if (empty($bookings)): ?>
                    <div class="text-center" style="padding: 40px;">
                        <p>Belum ada pemesanan</p>
                        <a href="rooms.php" class="btn">Pesan Ruangan Pertama Anda</a>
                    </div>
                <?php else: ?>
                    <?php foreach($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3><?= htmlspecialchars($booking['room_name']) ?> (<?= htmlspecialchars($booking['room_code']) ?>)</h3>
                                <span class="status-badge status-<?= $booking['status'] ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                            
                            <div class="booking-details">
                                <div class="detail-item">
                                    <strong>Tanggal Mulai:</strong>
                                    <?= date('d M Y H:i', strtotime($booking['date_start'])) ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Tanggal Selesai:</strong>
                                    <?= date('d M Y H:i', strtotime($booking['date_end'])) ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Tujuan:</strong>
                                    <?= htmlspecialchars($booking['purpose']) ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Dibuat pada:</strong>
                                    <?= date('d M Y H:i', strtotime($booking['created_at'])) ?>
                                </div>
                            </div>
                            
                            <?php if ($booking['status'] == 'pending'): ?>
                                <div class="booking-actions">
                                    <a href="cancel_booking.php?id=<?= $booking['id'] ?>" class="btn btn-danger" 
                                       onclick="return confirm('Yakin ingin membatalkan pemesanan?')">
                                        Batalkan
                                    </a>
                                </div>
                            <?php endif; ?>
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
                           href="?page=<?= $i ?><?= $status ? '&status='.$status : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            border-left: 4px solid #e91e63;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .booking-header h3 {
            margin: 0;
            color: #e91e63;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            padding: 5px 0;
        }
        
        .booking-actions {
            border-top: 1px solid #eee;
            padding-top: 15px;
            text-align: right;
        }
        
        .gap-2 {
            gap: 10px;
        }
    </style>
</body>
</html>