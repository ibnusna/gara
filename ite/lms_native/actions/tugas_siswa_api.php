<?php
// GARA - Garuda Akademi
// File: actions/tugas_siswa_api.php (V6 - Fix Duplicates & Deadline Block)
// FIX: Prevent Edit/Delete after deadline, Prevent Duplicates on Method Switch

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. HARDCODE ZONA WAKTU (Agar konsisten)
date_default_timezone_set('Asia/Jakarta');

// 2. CEK AKSES
requireSiswa();
header('Content-Type: application/json');

$siswa_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Helper Sanitasi Nama File
function sanitizeFileName($str) {
    // Hapus karakter ilegal
    $clean = preg_replace('/[\\/\\\\:*?"<>|]/', '', $str);
    return trim($clean);
}

try {
    
    // --- AKSI 1: MENGUMPULKAN TUGAS (SUBMIT) ---
    if ($action === 'submit_tugas') {
        $tugas_id = $_POST['tugas_id'];
        $catatan = trim($_POST['catatan'] ?? '');
        $metode = $_POST['metode'] ?? 'link'; 

        if (empty($tugas_id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID Tugas tidak ditemukan.']);
            exit;
        }

        // QUERY FIX: JOIN ke mata_pelajaran (bukan mapel)
        $sqlInfo = "SELECT t.batas_waktu, t.is_auto_close, t.allow_upload, t.judul,
                           m.nama_mapel, k.nama_kelas,
                           s.nama as nama_siswa
                    FROM tugas t
                    JOIN mata_pelajaran m ON t.mapel_id = m.id
                    JOIN kelas k ON t.kelas_id = k.id
                    JOIN siswa s ON s.id = ? 
                    WHERE t.id = ?";
        
        $stmtTugas = $pdo->prepare($sqlInfo);
        $stmtTugas->execute([$siswa_id, $tugas_id]);
        $tugas = $stmtTugas->fetch();

        if (!$tugas) {
            echo json_encode(['status' => 'error', 'message' => 'Tugas tidak ditemukan atau data siswa invalid.']);
            exit;
        }

        // [MODIFIKASI] Allow logic allow_upload=0 (Informasi Saja)
        // Jika allow_upload=0, kita izinkan metode 'manual' (Sudah Dibaca)
        if (!$tugas['allow_upload']) {
            if ($metode !== 'manual') {
               echo json_encode(['status' => 'error', 'message' => 'Tugas ini hanya informasi, cukup tandai sudah dibaca.']);
               exit;
            }
        }

        
        // --- CEK EXISTING SUBMISSION DULU (Untuk Validasi Edit) ---
        $stmtCheck = $pdo->prepare("SELECT id, link_pengumpulan FROM tugas_pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
        $stmtCheck->execute([$tugas_id, $siswa_id]);
        $existing = $stmtCheck->fetch();

        // --- LOGIKA WAKTU & BLOCKING ---
        $current_ts = time(); 
        $waktu_sekarang_db = date('Y-m-d H:i:s', $current_ts); 
        $is_late = false;
        $late_label = "";

        if ($tugas['batas_waktu']) {
            $deadline_ts = strtotime($tugas['batas_waktu']);

            if ($current_ts > $deadline_ts) {
                // 1. Jika Auto Close ON -> Block SEMUA (Baru/Edit)
                if ($tugas['is_auto_close'] == 1) {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal! Batas waktu telah habis (Auto Close).']);
                    exit;
                }

                // 2. Jika Deadline Lewat & Sudah Kumpul -> BLOCK EDIT
                if ($existing) {
                    echo json_encode(['status' => 'error', 'message' => 'Maaf, waktu habis. Anda tidak bisa mengedit tugas yang sudah dikumpulkan.']);
                    exit;
                }

                // 3. Jika Belum Kumpul -> Allow Late Submission
                $is_late = true;
                // Hitung Durasi Telat
                $diff = $current_ts - $deadline_ts;
                $days = floor($diff / (60 * 60 * 24));
                $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                $minutes = floor(($diff % (60 * 60)) / 60);
                
                $durasi_str = [];
                if ($days > 0) $durasi_str[] = "$days hari";
                if ($hours > 0) $durasi_str[] = "$hours jam";
                if ($minutes > 0) $durasi_str[] = "$minutes menit";
                if (empty($durasi_str)) $durasi_str[] = "< 1 menit"; 

                $late_label = "[TERLAMBAT " . implode(" ", $durasi_str) . "]";
            }
        }

        $final_link = ''; 
        $final_file_type = null;

        if ($metode === 'link') {
            $link_pengumpulan = trim($_POST['link_pengumpulan']);
            if (empty($link_pengumpulan)) {
                echo json_encode(['status' => 'error', 'message' => 'Link tidak boleh kosong.']);
                exit;
            }
            if (!filter_var($link_pengumpulan, FILTER_VALIDATE_URL)) {
                echo json_encode(['status' => 'error', 'message' => 'Link tidak valid (gunakan http:// atau https://).']);
                exit;
            }
            $final_link = $link_pengumpulan;

        } elseif ($metode === 'file') {
            if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] === UPLOAD_ERR_NO_FILE) {
                // Jika EDIT dan tidak upload file baru, tapi metode FILE, mungkin error? 
                // Tapi frontend harusnya handle ini. Asumsi user mau ganti file.
                echo json_encode(['status' => 'error', 'message' => 'Pilih file yang akan diupload.']);
                exit;
            }

            $file = $_FILES['file_upload'];
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'ppt', 'pptx'];
            $max_size = 5 * 1024 * 1024; // 5 MB

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['status' => 'error', 'message' => 'Upload error: ' . $file['error']]);
                exit;
            }

            if ($file['size'] > $max_size) {
                echo json_encode(['status' => 'error', 'message' => 'File terlalu besar (Max 5MB).']);
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) {
                echo json_encode(['status' => 'error', 'message' => 'Format file tidak diizinkan.']);
                exit;
            }

            // Generate Nama File (Fix Format)
            // Nama siswa-nama tugas-mapel & kelas-idkhusus
            $s_nama = sanitizeFileName($tugas['nama_siswa']);
            $s_tugas = sanitizeFileName($tugas['judul']);
            $s_mapel = sanitizeFileName($tugas['nama_mapel']);
            $s_kelas = sanitizeFileName($tugas['nama_kelas']);
            $s_id = time(); 

            $unique_name = "{$s_nama}-{$s_tugas}-{$s_mapel} {$s_kelas}-{$s_id}.{$ext}";
            
            $upload_dir = '../uploads/tugas/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $destination = $upload_dir . $unique_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $final_link = $unique_name; 
                $final_file_type = $ext;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal simpan file ke server.']);
                exit;
            }


        } elseif ($metode === 'manual') {
            // Logic KHUSUS untuk marking "Sudah Dibaca" pada tugas informasi
            if ($tugas['allow_upload']) {
                echo json_encode(['status' => 'error', 'message' => 'Metode manual tidak diizinkan untuk tugas ini.']);
                exit;
            }
            $final_link = '-'; // Placeholder agar tidak null
            $final_catatan = 'Sudah dibaca oleh siswa.'; // Default note

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Metode invalid.']);
            exit;
        }


        // --- MANIPULASI DB ---
        
        $final_catatan = $catatan;
        if ($is_late && !$existing) {
             // Tambah label terlambat hanya jika submit baru
             $final_catatan = $late_label . " " . $catatan;
        } elseif ($is_late && $existing) {
             // Should be blocked above, but fail safe check
             // Kalau lolos, jangan timpa label terlambat lama (logic complex, but blocked above)
        }

        // Jika EDIT: Hapus file lama jika ada dan valid file
        if ($existing) {
            if (!filter_var($existing['link_pengumpulan'], FILTER_VALIDATE_URL)) {
                 $old_file = '../uploads/tugas/' . $existing['link_pengumpulan'];
                 if (file_exists($old_file)) unlink($old_file);
            }
            
            // LAKUKAN UPDATE
            $sql = "UPDATE tugas_pengumpulan SET 
                    metode = ?, link_pengumpulan = ?, file_type = ?,
                    catatan_siswa = ?, dikumpulkan_pada = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$metode, $final_link, $final_file_type, $final_catatan, $waktu_sekarang_db, $existing['id']]);
            
            $msg = 'Tugas berhasil diperbarui!';
        } else {
            // LAKUKAN INSERT
            $sql = "INSERT INTO tugas_pengumpulan (tugas_id, siswa_id, metode, link_pengumpulan, file_type, catatan_siswa, dikumpulkan_pada) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tugas_id, $siswa_id, $metode, $final_link, $final_file_type, $final_catatan, $waktu_sekarang_db]);
            
            $msg = $is_late ? 'Berhasil dikumpulkan (Terlambat).' : 'Berhasil dikumpulkan!';
        }

        echo json_encode([
            'status' => 'success', 
            'message' => $msg,
            'data' => ['link' => $final_link, 'metode' => $metode, 'catatan' => $final_catatan]
        ]);
        exit;
    }

    // --- AKSI 2: DELETE ---
    elseif ($action === 'delete_submission') {
        $tugas_id = $_POST['tugas_id'];

        $stmtTugas = $pdo->prepare("SELECT batas_waktu, is_auto_close FROM tugas WHERE id = ?");
        $stmtTugas->execute([$tugas_id]);
        $tugas = $stmtTugas->fetch();

        if ($tugas && $tugas['batas_waktu']) {
            $current_ts = time();
            $deadline_ts = strtotime($tugas['batas_waktu']);

            if ($current_ts > $deadline_ts) {
                // BLOCK DELETE if Deadline Passed
                 echo json_encode(['status' => 'error', 'message' => 'Maaf, waktu habis. Tidak bisa membatalkan pengumpulan.']);
                 exit;
            }
        }

         $stmtGet = $pdo->prepare("SELECT link_pengumpulan, metode FROM tugas_pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
         $stmtGet->execute([$tugas_id, $siswa_id]);
         $row = $stmtGet->fetch();
         
         if ($row && $row['metode'] === 'file') {
             $path = '../uploads/tugas/' . $row['link_pengumpulan'];
             if (file_exists($path)) unlink($path);
         }

        $stmtDel = $pdo->prepare("DELETE FROM tugas_pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
        $stmtDel->execute([$tugas_id, $siswa_id]);

        echo json_encode(['status' => 'success', 'message' => 'Pengumpulan dibatalkan.']);
        exit;
    }

    else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>