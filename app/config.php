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
  'base_url' => '/booking-ruangan/public', // sesuaikan path
  'cookie_secure' => false, // true jika HTTPS
  'remember_days' => 30,
  'upload_dir' => __DIR__ . '/../public/assets/uploads',
  'max_upload_size' => 2 * 1024 * 1024 // 2MB
];
