<?php
session_start();
include __DIR__ . '/../db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Pastikan user hanya bisa membatalkan pemesanan miliknya sendiri
$stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$id, $user['id']]);

$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Pemesanan berhasil dibatalkan!'
];
header('Location: bookings.php');
exit;