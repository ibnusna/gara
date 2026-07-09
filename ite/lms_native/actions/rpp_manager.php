<?php
// GARA - Garuda Akademi
// File: actions/rpp_manager.php
// Tujuan: Menangani Create, Update, Delete (CRUD) untuk data RPP/Materi Pembelajaran.

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. Keamanan: Pastikan Guru sudah login dan memilih sesi
requireGuruSession();

// 2. Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/ruang_materi.php");
    exit();
}

// 3. Ambil Data Sesi (Konteks)
$mapel_id   = $_SESSION['mapel_id'];
$kelas_id   = $_SESSION['kelas_id'];
$nama_mapel = $_SESSION['nama_mapel']; // Contoh: IPA
$nama_kelas = $_SESSION['nama_kelas']; // Contoh: VII

// Siapkan variabel untuk ID Generator (IPA-VII-...)
$mapel_abbr  = strtoupper(substr($nama_mapel, 0, 3)); // Ambil 3 huruf pertama
$kelas_roman = $nama_kelas; 

$action = $_POST['action'] ?? '';
$redirect_url = '../admin/ruang_materi.php';

// Helper: Validasi URL (Kosongkan jika bukan URL valid)
function validate_url($url) {
    $url = trim($url ?? '');
    return (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) ? $url : null;
}

try {
    switch ($action) {
        case 'add':
            // --- LOGIKA TAMBAH MATERI ---
            $semester   = $_POST['semester'];
            $bab        = $_POST['bab'];
            $bagian     = $_POST['bagian'];
            $judul      = trim($_POST['judul_materi']);

            // Validasi Input Wajib
            if (empty($judul) || empty($semester) || empty($bab)) {
                throw new Exception("Judul, Semester, dan Bab wajib diisi.");
            }

            // Generate ID Unik: MAPEL-KELAS-S1-B1-P1 (Contoh: IPA-VII-S1-B1-P1)
            $id_materi = "{$mapel_abbr}-{$kelas_roman}-S{$semester}-B{$bab}-P{$bagian}";

            // Cek Duplikat ID
            $stmtCheck = $pdo->prepare("SELECT id FROM rpp_materi WHERE id_materi = ?");
            $stmtCheck->execute([$id_materi]);
            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Materi dengan urutan tersebut (ID: $id_materi) sudah ada. Silakan cek semester/bab/bagian.");
            }

            // Ambil Link (Opsional)
            $link_ppt      = validate_url($_POST['link_ppt']);
            $link_youtube  = validate_url($_POST['link_youtube']);
            $link_modul    = validate_url($_POST['link_modul']);
            $link_tugas    = validate_url($_POST['link_tugas']);
            $link_notebook = validate_url($_POST['link_notebook']);

            // Insert Database
            $sql = "INSERT INTO rpp_materi 
                    (mapel_id, kelas_id, id_materi, semester, bab, bagian, judul_materi, 
                     link_ppt, link_youtube, link_modul, link_tugas, link_notebook) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $mapel_id, $kelas_id, $id_materi, $semester, $bab, $bagian, $judul,
                $link_ppt, $link_youtube, $link_modul, $link_tugas, $link_notebook
            ]);

            $_SESSION['success_message'] = "Materi berhasil ditambahkan!";
            break;

        case 'update':
            // --- LOGIKA EDIT MATERI ---
            $id_db = $_POST['id'];
            
            // Ambil data form
            $semester   = $_POST['semester'];
            $bab        = $_POST['bab'];
            $bagian     = $_POST['bagian'];
            $judul      = trim($_POST['judul_materi']);

            // Generate ID Baru (jika semester/bab berubah, ID ikut berubah agar konsisten)
            $id_materi_baru = "{$mapel_abbr}-{$kelas_roman}-S{$semester}-B{$bab}-P{$bagian}";

            // Ambil Link
            $link_ppt      = validate_url($_POST['link_ppt']);
            $link_youtube  = validate_url($_POST['link_youtube']);
            $link_modul    = validate_url($_POST['link_modul']);
            $link_tugas    = validate_url($_POST['link_tugas']);
            $link_notebook = validate_url($_POST['link_notebook']);

            // Update Database
            $sql = "UPDATE rpp_materi SET 
                    id_materi = ?, semester = ?, bab = ?, bagian = ?, judul_materi = ?, 
                    link_ppt = ?, link_youtube = ?, link_modul = ?, link_tugas = ?, link_notebook = ?
                    WHERE id = ? AND mapel_id = ? AND kelas_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_materi_baru, $semester, $bab, $bagian, $judul,
                $link_ppt, $link_youtube, $link_modul, $link_tugas, $link_notebook,
                $id_db, $mapel_id, $kelas_id
            ]);

            $_SESSION['success_message'] = "Materi berhasil diperbarui!";
            break;

        case 'delete':
            // --- LOGIKA HAPUS MATERI ---
            $id_db = $_POST['id'];

            // Hapus data (Filter by ID, Mapel, dan Kelas untuk keamanan ekstra)
            $stmt = $pdo->prepare("DELETE FROM rpp_materi WHERE id = ? AND mapel_id = ? AND kelas_id = ?");
            $stmt->execute([$id_db, $mapel_id, $kelas_id]);

            $_SESSION['success_message'] = "Materi berhasil dihapus.";
            break;

        default:
            throw new Exception("Aksi tidak dikenali.");
    }

} catch (Exception $e) {
    // Tangkap error dan simpan di session untuk ditampilkan di frontend
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
}

// Kembalikan ke halaman tabel
header("Location: " . $redirect_url);
exit();
?>