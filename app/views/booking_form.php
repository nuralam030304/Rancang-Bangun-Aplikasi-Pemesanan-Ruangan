<?php
session_start();
include __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: login.php');
    exit;
}

$room_id = $_GET['room_id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: rooms.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $purpose = $_POST['purpose'];
    
    $stmt = $pdo->prepare("INSERT INTO bookings (room_id, user_id, date_start, date_end, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$room_id, $user['id'], $date_start, $date_end, $purpose]);
    
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Pemesanan berhasil diajukan! Menunggu persetujuan admin.'
    ];
    header('Location: bookings.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Ruangan - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Pesan Ruangan: <?= htmlspecialchars($room['name']) ?></h1>
            
            <form method="POST">
                <div class="form-group">
                    <label>Tanggal & Jam Mulai</label>
                    <input type="datetime-local" name="date_start" required class="input-text">
                </div>
                
                <div class="form-group">
                    <label>Tanggal & Jam Selesai</label>
                    <input type="datetime-local" name="date_end" required class="input-text">
                </div>
                
                <div class="form-group">
                    <label>Tujuan Pemesanan</label>
                    <textarea name="purpose" required class="input-text" rows="4" placeholder="Jelaskan tujuan penggunaan ruangan..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Ajukan Pemesanan</button>
                    <a href="rooms.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>