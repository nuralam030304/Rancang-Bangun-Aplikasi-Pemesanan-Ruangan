<?php
session_start();
include __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role_name'] ?? 'user') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Pesanan berhasil disetujui!'
];
header('Location: order.php');
exit;