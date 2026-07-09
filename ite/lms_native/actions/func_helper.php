<?php
// GARA - Garuda Akademi
// File: actions/func_helper.php
// Tujuan: Kumpulan fungsi bantu (Helper) untuk memproses URL, Icon, dan Logika Tampilan Ruang Belajar.

if (!function_exists('getYoutubeEmbedId')) {
    /**
     * Mengambil ID Video Youtube dari berbagai format URL
     * Support: youtu.be, youtube.com/watch?v=, youtube.com/embed/
     */
    function getYoutubeEmbedId($url) {
        // Fix Deprecated: trim(): Passing null to parameter #1 ($string)
        if (is_null($url)) return null;
        
        $url = trim($url);
        if (empty($url)) return null;

        // Pola Regex untuk menangkap ID Youtube (11 karakter)
        // Menangani: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        if (preg_match($pattern, $url, $match)) {
            return $match[1]; // Mengembalikan ID Video (contoh: dQw4w9WgXcQ)
        }
        return null; // Bukan link youtube valid
    }
}

if (!function_exists('getMaterialIcon')) {
    /**
     * Menentukan Icon FontAwesome berdasarkan tipe link/aksi
     */
    function getMaterialIcon($type) {
        switch ($type) {
            case 'youtube': return 'fab fa-youtube text-danger';     // Merah khas Youtube
            case 'ppt':     return 'fas fa-file-powerpoint text-warning'; // Orange/Kuning khas PPT
            case 'modul':   return 'fas fa-file-pdf text-primary';   // Biru khas PDF/Word
            case 'tugas':   return 'fas fa-tasks text-success';      // Hijau khas Tugas
            case 'notebook':return 'fas fa-book-open text-info';     // Biru muda untuk Catatan
            default:        return 'fas fa-link text-secondary';
        }
    }
}

if (!function_exists('formatNomorBab')) {
    /**
     * Format Label Bab agar rapi dengan Zero Padding
     * Input: 1 -> Output: "01"
     */
    function formatNomorBab($nomor) {
        // Pastikan input angka
        $n = intval($nomor);
        return str_pad($n, 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('isGoogleDriveLink')) {
    /**
     * Cek apakah string adalah Link Google Drive/Docs (Preview/View)
     * Digunakan untuk menentukan apakah harus pakai Iframe Google Docs Viewer atau link biasa
     */
    function isGoogleDriveLink($url) {
        return (strpos($url, 'drive.google.com') !== false || strpos($url, 'docs.google.com') !== false);
    }
}

if (!function_exists('isMateriCompleted')) {
    /**
     * Helper sederhana untuk mengecek status "Selesai" (Simulasi Logic Progress)
     * Saat ini menggunakan Session array untuk menyimpan ID materi yang pernah diklik.
     * Nanti bisa diupgrade ke tabel database `progress_siswa` jika diperlukan.
     */
    function isMateriCompleted($id_materi) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (isset($_SESSION['completed_materi']) && is_array($_SESSION['completed_materi'])) {
            return in_array($id_materi, $_SESSION['completed_materi']);
        }
        return false;
    }
}

if (!function_exists('markMateriAsCompleted')) {
    /**
     * Menandai materi sebagai selesai (Disimpan ke Session)
     */
    function markMateriAsCompleted($id_materi) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['completed_materi'])) {
            $_SESSION['completed_materi'] = [];
        }
        
        if (!in_array($id_materi, $_SESSION['completed_materi'])) {
            $_SESSION['completed_materi'][] = $id_materi;
        }
    }
}
?>