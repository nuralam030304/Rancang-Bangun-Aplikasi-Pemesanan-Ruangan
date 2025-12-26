<?php
include __DIR__ . '/../db.php';

// Cek apakah user sedang login berdasarkan auth.php
$user = $_SESSION['user'] ?? null;
$user_data = null;

// Jika user login, ambil data lengkap termasuk role_name
if ($user && isset($user['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user['id']]);
        $user_data = $stmt->fetch();
        
        // Update session dengan role_name jika ditemukan
        if ($user_data && isset($user_data['role_name'])) {
            $_SESSION['user']['role_name'] = $user_data['role_name'];
            $user = $_SESSION['user']; // Update variabel $user dengan data terbaru
        }
    } catch (PDOException $e) {
        error_log("Error getting user data: " . $e->getMessage());
    }
}

// Ambil statistik untuk dashboard
$total_rooms = 0;
$total_bookings = 0;
$available_rooms = 0;
$today_bookings = 0;

try {
    // Total ruangan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
    $result = $stmt->fetch();
    $total_rooms = $result['total'];
    
    // Total pemesanan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $result = $stmt->fetch();
    $total_bookings = $result['total'];
    
    // Ruangan tersedia
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms WHERE capacity > 0");
    $result = $stmt->fetch();
    $available_rooms = $result['total'];
    
    // Pemesanan hari ini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(date_start) = CURDATE()");
    $result = $stmt->fetch();
    $today_bookings = $result['total'];
    
} catch (PDOException $e) {
    error_log("Database error in home.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- LeafletJS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map { height: 400px; width: 100%; border-radius: 12px; margin-top: 20px; z-index: 1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-hide notification
            const notif = document.querySelector(".notif");
            if (notif) {
                setTimeout(() => {
                    notif.style.opacity = "0";
                    notif.style.transition = "opacity 0.5s ease";
                    setTimeout(() => notif.remove(), 500);
                }, 5000);
            }

            // Chart untuk statistik pemesanan
            const ctx = document.getElementById('bookingChart');
            if (ctx) {
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                        datasets: [{
                            label: 'Jumlah Pemesanan',
                            data: [12, 19, 8, 15, 12, 5],
                            backgroundColor: [
                                '#f8bbd0', '#f48fb1', '#f06292', 
                                '#ec407a', '#e91e63', '#d81b60'
                            ],
                            borderColor: [
                                '#f8bbd0', '#f48fb1', '#f06292',
                                '#ec407a', '#e91e63', '#d81b60'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Jumlah Pemesanan'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Hari'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <!-- Header & Welcome Section -->
        <section class="welcome-section">
            <?php if ($user): ?>
                <div class="welcome-icon">👋</div>
                <h1>Selamat Datang, <?= htmlspecialchars($user['name']); ?>!</h1>
                <p style="font-size: 1.2rem; margin-top: 10px;">
                    Anda login sebagai 
                    <span class="user-role-badge">
                        <?= htmlspecialchars($user['role_name'] ?? 'User'); ?>
                    </span>
                </p>
                <p style="margin-top: 15px; color: #666;">
                    <?php if (($user['role_name'] ?? 'user') === 'admin'): ?>
                        Kelola sistem booking ruangan dengan mudah
                    <?php else: ?>
                        Temukan dan pesan ruangan sesuai kebutuhan Anda
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <div class="welcome-icon">🏢</div>
                <h1>Selamat Datang di Sistem Booking Ruangan</h1>
                <p style="font-size: 1.1rem; margin-top: 15px; color: #666;">
                    Sistem pemesanan ruangan yang mudah dan efisien untuk kebutuhan meeting Anda
                </p>
            <?php endif; ?>
        </section>

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="notif <?= $_SESSION['flash']['type']; ?>">
                <?= $_SESSION['flash']['message']; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Navigasi -->
        <div class="nav-container">
            <?php if ($user): ?>
                <a href="../app/views/rooms.php" class="nav-button"> Daftar Ruangan</a>
                <a href="../app/views/bookings.php" class="nav-button"> Pemesanan Saya</a>
                
                <?php if (($user['role_name'] ?? 'user') === 'admin'): ?>
                    <a href="../app/views/index.php" class="nav-button admin"> Kelola Ruangan</a>
                    <a href="../app/views/order.php" class="nav-button admin"> Daftar Pesanan</a>
                <?php endif; ?>
                
                <a href="../app/views/logout.php" class="nav-button logout" onclick="return confirm('Yakin ingin keluar?')"> Logout</a>
            <?php else: ?>
                <div class="login-prompt">
                    <h3>Mulai Menggunakan Sistem</h3>
                    <p>Login untuk mengakses fitur pemesanan ruangan</p>
                    <a href="login.php" class="login-button"> Login ke Sistem</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dashboard Stats -->
        <?php if ($user): ?>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_rooms ?></div>
                    <div class="stat-label">Total Ruangan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $available_rooms ?></div>
                    <div class="stat-label">Ruangan Tersedia</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_bookings ?></div>
                    <div class="stat-label">Total Pemesanan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $today_bookings ?></div>
                    <div class="stat-label">Pemesanan Hari Ini</div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <h3 style="text-align: center; margin-bottom: 20px;">Statistik Pemesanan Ruangan</h3>
                <canvas id="bookingChart" width="400" height="200"></canvas>
            </div>
        <?php else: ?>
            <!-- Features for Non-Logged in Users -->
            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon">🔍</div>
                    <h4>Lihat Ruangan</h4>
                    <p>Jelajahi ruangan yang tersedia</p>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">📅</div>
                    <h4>Pesan Mudah</h4>
                    <p>Sistem booking yang sederhana</p>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">⏰</div>
                    <h4>Real-time</h4>
                    <p>Ketersediaan ruangan update real-time</p>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">📱</div>
                    <h4>Responsif</h4>
                    <p>Akses dari berbagai perangkat</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Location Section -->
        <section class="location-section" style="margin-top: 50px; margin-bottom: 30px;">
            <h3 style="text-align: center; margin-bottom: 20px; color: #333;">📍 Lokasi Kami</h3>
            <div class="card">
                <div id="map"></div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Initialize Map (Default: Monas, Jakarta)
                    // Koordinat bisa diubah sesuai lokasi sebenarnya
                    var map = L.map('map').setView([-6.175392, 106.827153], 15);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    var marker = L.marker([-6.175392, 106.827153]).addTo(map);
                    marker.bindPopup("<b>Gedung Booking Ruangan</b><br>Jl. Medan Merdeka Barat No. 12<br>Jakarta Pusat").openPopup();
                });
            </script>
        </section>

        <footer style="margin-top: 50px;">
            <p>© <?= date('Y'); ?> Sistem Booking Ruangan - Dibuat dengan ❤️ oleh Mahruf</p>
        </footer>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>