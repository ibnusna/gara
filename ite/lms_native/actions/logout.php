<?php
// GARA - Garuda Akademi
// File: actions/logout.php
// Tujuan: Logout Total. Menghapus sesi server DAN menghancurkan cookie "Abadi" di browser.

// 1. Mulai sesi dulu agar bisa kita akses data yang mau dihapus
session_start();

// 2. Kosongkan semua variabel sesi ($_SESSION array)
$_SESSION = [];

// 3. HANCURKAN COOKIE SESI (PENTING UNTUK FITUR AUTO-LOGIN)
// Jika browser punya cookie sesi, kita set expired-nya ke masa lalu agar browser menghapusnya.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan sesi di server
session_destroy();

// 5. Tendang balik ke halaman login utama
header("Location: ../index.php");
exit();
?>