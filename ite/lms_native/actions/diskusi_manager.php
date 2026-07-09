<?php
// GARA - Garuda Akademi
// File: actions/diskusi_manager.php
// Tujuan: Backend CRUD Diskusi Lengkap (Create, Delete, Pin, Toggle Status Arsip)
// FIX: Timezone Issue (InfinityFree UTC vs WIB) - Menggunakan PHP Timestamp

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. HARDCODE ZONA WAKTU (Agar konsisten di Hosting manapun)
date_default_timezone_set('Asia/Jakarta');

// Cek Login Guru
requireGuruSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/ruang_diskusi.php");
    exit();
}

$action = $_POST['action'] ?? '';

// VALIDASI SESI
$mapel_id = $_SESSION['mapel_id'] ?? null;
$kelas_id = $_SESSION['kelas_id'] ?? null;
$guru_id  = $_SESSION['user_id'] ?? null;

if (empty($mapel_id) || empty($kelas_id) || empty($guru_id)) {
    $_SESSION['error_message'] = "Sesi tidak valid atau kadaluarsa. Silakan pilih kelas kembali.";
    header("Location: ../admin/pilih_sesi.php");
    exit();
}

try {
    switch ($action) {
        // 1. BUAT TOPIK BARU (GURU)
        case 'create_thread_guru':
            $isi   = trim($_POST['isi_konten'] ?? '');
            $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
            
            $media_path = null;
            $has_media = false;

            // Handle Upload Gambar
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (!in_array($ext, $allowed)) {
                    throw new Exception("Format file tidak didukung. Harap upload gambar (JPG, PNG, WEBP).");
                }

                if (!is_dir('../uploads/diskusi/')) {
                    mkdir('../uploads/diskusi/', 0755, true);
                }

                $newName = uniqid('G_') . '.' . $ext;
                $target = '../uploads/diskusi/' . $newName;

                if (move_uploaded_file($_FILES['media']['tmp_name'], $target)) {
                    $media_path = 'uploads/diskusi/' . $newName;
                    $has_media = true;
                }
            }

            if (empty($isi) && !$has_media) {
                throw new Exception("Postingan tidak boleh kosong. Tulis sesuatu atau upload gambar.");
            }

            // AUTO-GENERATE JUDUL
            $judul_otomatis = !empty($isi) ? substr($isi, 0, 50) : 'Postingan Gambar';

            // FIX TIMEZONE: Generate waktu di PHP (WIB), jangan pakai NOW() MySQL (UTC)
            $waktu_sekarang = date('Y-m-d H:i:s');

            // Simpan ke Database
            $sql = "INSERT INTO diskusi_threads 
                    (mapel_id, kelas_id, judul, isi_konten, role_pembuat, siswa_id, media_path, is_pinned, status, created_at) 
                    VALUES (?, ?, ?, ?, 'guru', ?, ?, ?, 'aktif', ?)";
            
            $stmt = $pdo->prepare($sql);
            // Perhatikan parameter terakhir adalah $waktu_sekarang
            $stmt->execute([$mapel_id, $kelas_id, $judul_otomatis, $isi, $guru_id, $media_path, $is_pinned, $waktu_sekarang]);

            $_SESSION['success_message'] = "Topik diskusi berhasil diposting.";
            break;

        // 2. HAPUS TOPIK PERMANEN
        case 'delete_thread_guru':
            $id = $_POST['id'];

            // Hapus file gambar jika ada
            $stmt = $pdo->prepare("SELECT media_path FROM diskusi_threads WHERE id = ?");
            $stmt->execute([$id]);
            $thread = $stmt->fetch();

            if ($thread && $thread['media_path']) {
                $file = '../' . $thread['media_path'];
                if (file_exists($file)) unlink($file);
            }

            // Hapus data (Reply & Thread)
            $pdo->prepare("DELETE FROM diskusi_replies WHERE thread_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM diskusi_threads WHERE id = ?")->execute([$id]);

            $_SESSION['success_message'] = "Diskusi berhasil dihapus permanen.";
            break;

        // 3. SEMATKAN / LEPAS PIN
        case 'toggle_pin':
            $id = $_POST['id'];
            $current = $_POST['current_pin'];
            $new = ($current == 1) ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE diskusi_threads SET is_pinned = ? WHERE id = ?");
            $stmt->execute([$new, $id]);

            $_SESSION['success_message'] = ($new == 1) ? "Diskusi berhasil disematkan." : "Sematkan diskusi dilepas.";
            break;

        // 4. TOGGLE STATUS (ARSIP/DRAFT vs AKTIF)
        case 'toggle_status':
            $id = $_POST['id'];
            $current = $_POST['current_status'];
            $new = ($current == 'aktif') ? 'draft' : 'aktif';

            $stmt = $pdo->prepare("UPDATE diskusi_threads SET status = ? WHERE id = ?");
            $stmt->execute([$new, $id]);

            $_SESSION['success_message'] = ($new == 'aktif') ? "Diskusi diaktifkan kembali." : "Diskusi berhasil diarsipkan (Draft).";
            break;

        default:
            throw new Exception("Aksi tidak dikenal: " . htmlspecialchars($action));
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
}

header("Location: ../admin/ruang_diskusi.php");
exit();
?>