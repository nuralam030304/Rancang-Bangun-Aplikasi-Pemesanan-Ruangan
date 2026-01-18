🏢 Rancang Bangun Aplikasi Pemesanan Ruang Berbasis Web

Aplikasi Rancang Bangun Aplikasi Pemesanan Ruang merupakan sistem berbasis web yang dirancang untuk memudahkan proses reservasi, pengelolaan, dan penjadwalan penggunaan ruangan atau laboratorium secara terstruktur dan aman.

Aplikasi ini dibangun menggunakan PHP Native dan MySQL tanpa framework eksternal, menerapkan arsitektur Model–View–Controller (MVC), antarmuka responsif, serta keamanan tingkat enterprise.

📋 Daftar Isi

✨ Fitur Utama

🏗️ Arsitektur Sistem

🗄️ Database Schema

🖥️ Persyaratan Sistem

🚀 Instalasi & Setup

🎯 Cara Menggunakan

📁 Struktur Project

🔐 Keamanan

👨‍💻 Development

🐛 Troubleshooting

📝 Catatan & To-Do

📄 Lisensi

✨ Fitur Utama
👤 Fitur untuk User (Pengguna)

✅ Register & Login — Autentikasi pengguna yang aman

✅ Lihat Ruangan Tersedia — Informasi detail ruangan

✅ Buat Booking Ruangan — Pemesanan berbasis tanggal dan time slot

✅ Kelola Booking Saya — Lihat, ubah, dan batalkan booking

✅ Remember Me — Login persisten hingga 30 hari

✅ Reset Password — Pemulihan akun menggunakan email dan token

🛠️ Fitur untuk Admin

✅ Manajemen Ruangan — Tambah, ubah, hapus ruangan (CRUD)

✅ Manajemen Time Slot — Atur jam operasional pemesanan

✅ Manajemen Booking — Approve, reject, atau cancel booking

✅ Activity Log — Audit trail aktivitas pengguna

✅ Export Data — Unduh data laporan (CSV)

🔧 Fitur Teknis

🔐 Keamanan Tingkat Enterprise

Password hashing (bcrypt)

CSRF protection

Session regeneration

SQL Injection prevention (prepared statements)

Secure remember-me token

📱 Responsive Design — Mobile-first & desktop-ready

🎨 Modern UI — Tampilan bersih dan animasi halus

♿ Accessibility Ready — Semantic HTML & ARIA

🚀 High Performance — Minim dependency, query optimal

🏗️ Arsitektur Sistem

Aplikasi menerapkan pola Model–View–Controller (MVC) untuk memisahkan logika bisnis, tampilan, dan akses data.

public/
├── index.php              # Entry point & router
├── assets/
│   ├── css/style.css
│   └── js/app.js

app/
├── config/
├── controllers/
├── models/
├── views/
├── middleware/
└── helpers/

🗄️ Database Schema
Tabel Utama

users
(id, name, email, password_hash, role_id, created_at)

roles
(id, name) → Admin & User

rooms
(id, name, location, capacity, description)

timeslots
(id, name, start_time, end_time)

bookings
(id, user_id, room_id, timeslot_id, booking_date, status, created_at)

persistent_logins
(selector, token_hash, expires_at)

password_resets
(user_id, token_hash, expires_at, used_at)

activity_logs
(user_id, action, description, created_at)

🖥️ Persyaratan Sistem

PHP ≥ 7.4 (disarankan 8.0+)

MySQL ≥ 5.7 / MariaDB ≥ 10.2

Web Server: Apache / Nginx / Laragon

Browser modern (Chrome, Firefox, Edge)

🚀 Instalasi & Setup
1️⃣ Clone Project
git clone https://github.com/nuralam030304/Bookingroom.git
cd Bookingroom

2️⃣ Setup Database

Buat database:

room_booking


Import file schema.sql

mysql -u root -p room_booking < schema.sql

3️⃣ Konfigurasi Database

Edit file app/config/db.php:

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'room_booking');

4️⃣ Jalankan Aplikasi

Simpan project di:

htdocs (XAMPP) atau

www (Laragon)

Jalankan Apache & MySQL

Akses:

http://localhost/Bookingroom/

🎯 Cara Menggunakan
🔐 Akun Demo

Admin

Email: admin@example.com

Password: Admin@123

User

Email: user@example.com

Password: User@123

🧭 Alur Penggunaan

Register / Login

Lihat daftar ruangan

Buat booking ruangan

Admin melakukan approval

User memantau status booking

📁 Struktur Project Lengkap
Bookingroom/
├── public/
│   ├── index.php
│   └── assets/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── middleware/
│   └── helpers/
├── storage/
├── tests/
├── composer.json
├── schema.sql
└── README.md

🔐 Keamanan
Fitur	Implementasi
Password	password_hash (bcrypt)
SQL Injection	Prepared Statements
CSRF	Token validation
Session	session_regenerate_id
Remember Me	Token rotation
Reset Password	Token valid 1 jam

✅ Best Practice Security diterapkan

👨‍💻 Development
Menjalankan Unit Test
composer install
vendor/bin/phpunit

Pengembangan Fitur Baru

Tambah controller di app/controllers

Tambah model di app/models

Tambah view di app/views

Update routing di public/index.php

🐛 Troubleshooting

Database connection failed

Pastikan MySQL aktif

Cek konfigurasi db.php

Pastikan database tersedia

Login gagal

Pastikan data user ada di database

Cek password_verify()

CSRF token mismatch

Pastikan session aktif

Form memiliki input _csrf
[README.md](https://github.com/user-attachments/files/24697879/README.md)

📝 Catatan & To-Do
✅ Sudah Selesai

Authentication

Booking ruangan

Admin dashboard

CSRF protection

Activity log

🔜 Pengembangan Lanjutan

Email notification

Kalender booking

Dark mode

Two-factor authentication (2FA)

📄 Lisensi

MIT License — Bebas digunakan untuk pembelajaran dan pengembangan.

👨‍💼 Author

Project Rancang Bangun Aplikasi Pemesanan Ruang Berbasis Web
Menggunakan PHP Native & MySQL
