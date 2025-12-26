<?php
// app/config.php
return [
  'db' => [
    'host' => '127.0.0.1',
    'dbname' => 'booking_db',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
  ],
  'mail' => [
    'host' => 'smtp.gmail.com', // Ganti dengan SMTP server Anda
    'username' => 'your_email@gmail.com', // Ganti dengan email Anda
    'password' => 'your_app_password', // Ganti dengan App Password Anda
    'port' => 587,
    'encryption' => 'tls', // tls atau ssl
    'from_address' => 'no-reply@booking-ruangan.com',
    'from_name' => 'Sistem Booking Ruangan'
  ],
  'base_url' => '/booking-ruangan/public', // sesuaikan path
  'cookie_secure' => false, // true jika HTTPS
  'remember_days' => 30,
  'upload_dir' => __DIR__ . '/../public/assets/uploads',
  'max_upload_size' => 2 * 1024 * 1024 // 2MB
];
