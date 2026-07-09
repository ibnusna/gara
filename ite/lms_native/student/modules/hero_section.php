<?php
/**
 * GARA MODULE: HERO SECTION
 * -------------------------
 * Menampilkan sapaan, identitas siswa, dan kutipan motivasi harian.
 * Dependencies: $_SESSION vars
 */

// 1. Logika Sapaan Berdasarkan Jam
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu WIB
$jam = date('H');
$sapaan = "Selamat Datang";

if ($jam >= 4 && $jam < 11) {
    $sapaan = "Selamat Pagi";
} elseif ($jam >= 11 && $jam < 15) {
    $sapaan = "Selamat Siang";
} elseif ($jam >= 15 && $jam < 18) {
    $sapaan = "Selamat Sore";
} else {
    $sapaan = "Selamat Malam";
}

// 2. Database Kutipan Motivasi (Hardcoded Array)
$quotes = [
    "Pendidikan adalah senjata paling ampuh untuk mengubah dunia.",
    "Jangan pernah berhenti belajar, karena hidup tak pernah berhenti mengajarkan.",
    "Masa depan adalah milik mereka yang menyiapkannya hari ini.",
    "Ilmu tanpa amal bagaikan pohon tanpa buah.",
    "Barang siapa bersungguh-sungguh, maka dia akan mendapatkannya (Man Jadda Wajada).",
    "Kegagalan adalah guru terbaikmu. Belajarlah darinya.",
    "Satu-satunya cara untuk melakukan pekerjaan hebat adalah dengan mencintai apa yang kamu lakukan."
];

// Pilih 1 kutipan secara acak
$quote_harian = $quotes[array_rand($quotes)];
?>

<!-- VIEW HTML -->
<div class="hero-v2">
    <div class="hero-content">
        <!-- Baris 1: Sapaan & Nama -->
        <div class="mb-3">
            <p class="mb-0 text-white-50" style="font-size: 0.9rem;">
                <?= $sapaan ?>,
            </p>
            <h2 class="hero-title">
                <?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa') ?>
            </h2>
        </div>

        <!-- Baris 2: Badge Info -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                <i class="fas fa-users me-1"></i> Kelas <?= htmlspecialchars($_SESSION['nama_kelas'] ?? '-') ?>
            </span>
            <a href="pilih_mapel.php" class="text-decoration-none">
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                    <i class="fas fa-book me-1"></i> <?= htmlspecialchars($_SESSION['nama_mapel'] ?? '-') ?>
                </span>
            </a>
        </div>

        <!-- Baris 3: Kutipan Motivasi -->
        <div style="border-top: 1px solid rgba(255,255,255,0.2); margin-top: 15px; padding-top: 10px;">
            <p class="mb-0 fst-italic" style="font-size: 0.85rem; opacity: 0.9;">
                <i class="fas fa-quote-left me-2"></i><?= $quote_harian ?>
            </p>
        </div>
    </div>
</div>