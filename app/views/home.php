<?php
include __DIR__ . '/../db.php';

// Cek apakah user sedang login
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT username, role FROM users WHERE id = '$user_id'";
    $result = $conn->query($query);
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Booking Ruangan</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <script>
        // Notifikasi dinamis (DOM Manipulation)
        document.addEventListener("DOMContentLoaded", function() {
            const notif = document.querySelector(".notif");
            if (notif) {
                setTimeout(() => {
                    notif.style.opacity = "0";
                    setTimeout(() => notif.remove(), 500);
                }, 4000);
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <?php if ($user): ?>
                <h2>Selamat Datang, <?= htmlspecialchars($user['username']); ?> 👋</h2>
                <p>Anda login sebagai <b><?= htmlspecialchars($user['role']); ?></b>.</p>
            <?php else: ?>
                <h2>Selamat Datang di Aplikasi Booking Ruangan 👋</h2>
                <p>Silakan login untuk memesan ruangan atau lihat daftar ruangan yang tersedia.</p>
            <?php endif; ?>
        </header>

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="notif <?= $_SESSION['flash']['type']; ?>">
                <?= $_SESSION['flash']['message']; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Navigasi -->
        <nav>
            <a href="/?p=rooms">Daftar Ruangan</a>
            <a href="/?p=bookings">Pemesanan</a>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="/?p=users">Kelola Pengguna</a>
            <?php endif; ?>

            <?php if ($user): ?>
                <a href="logout" onclick="return confirm('Yakin ingin keluar?')">Logout</a>
            <?php else: ?>
                <a href="login">Login</a>
            <?php endif; ?>
        </nav>

        <hr>

        <!-- Konten Dashboard -->
        <section>
            <h3>Dashboard Ringkas</h3>
            <p>Statistik pemesanan ruangan (contoh data):</p>

            <canvas id="chart" width="400" height="200"></canvas>
        </section>

        <footer>
            <p>© <?= date('Y'); ?> Aplikasi Booking Ruangan - Mahruf</p>
        </footer>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart laporan ringkas
        const ctx = document.getElementById('chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ruang A', 'Ruang B', 'Ruang C', 'Ruang D'],
                datasets: [{
                    label: 'Jumlah Booking',
                    data: [5, 2, 3, 7],
                    backgroundColor: ['#f8bbd0', '#f48fb1', '#f06292', '#ec407a']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Statistik Pemesanan Ruangan' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
