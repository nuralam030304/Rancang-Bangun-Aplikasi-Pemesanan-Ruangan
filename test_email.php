<?php
// test_email.php
require __DIR__ . '/app/mail_helper.php';

echo "Testing Email Sending...\n";

// Ganti dengan email tujuan Anda untuk testing
$toEmail = 'test@example.com'; 
$token = 'test_token_123';
$userId = 1;

if (sendResetEmail($toEmail, $token, $userId)) {
    echo "Email berhasil dikirim (atau diterima oleh SMTP server)!\n";
    echo "Cek inbox Anda (atau folder spam).\n";
} else {
    echo "Gagal mengirim email.\n";
    echo "Pastikan konfigurasi SMTP di app/config.php sudah benar.\n";
}
