<?php 
// logout.php
session_start();

// Simpan pesan logout sukses sebelum menghapus session
$_SESSION['flash']['success'] = 'Anda telah berhasil logout.';

// Hapus semua data session
$_SESSION = array();

// Jika ingin menghapus session cookie juga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login atau halaman utama
header('Location: login.php');
exit;
?>