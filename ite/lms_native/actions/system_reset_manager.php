<?php
// GARA - Garuda Akademi
// File: actions/system_reset_manager.php (V4 - Mode Arsip/Robust)

require_once '../config/database.php';
require_once 'auth_helper.php';
requireGuru();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/pengaturan_sistem.php");
    exit();
}

$action = $_POST['action'] ?? '';

try {
    // FUNGSI BERSIH-BERSIH (ROBUST VERSION)
    function resetTransactionalData($pdo) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // --- 1. RUANG TUGAS ---
        // Hapus JAWABAN SISWA (Bersih total)
        $pdo->exec("DELETE FROM tugas_pengumpulan");
        // [REMOVED] ALTER TABLE causes Implicit Commit, breaking the transaction.
        // $pdo->exec("ALTER TABLE tugas_pengumpulan AUTO_INCREMENT = 1");
        
        // JANGAN HAPUS SOAL GURU, TAPI ARSIPKAN (JADI DRAFT)
        // Agar guru bisa pakai lagi tahun depan
        $pdo->exec("UPDATE tugas SET status = 'draft'");
        
        // --- 2. RUANG DISKUSI ---
        // Hapus Komentar/Balasan (Bersih total)
        $pdo->exec("DELETE FROM diskusi_replies");
        // [REMOVED] ALTER TABLE causes Implicit Commit, breaking the transaction.
        // $pdo->exec("ALTER TABLE diskusi_replies AUTO_INCREMENT = 1");
        
        // Hapus Topik buatan SISWA (Karena biasanya sampah/pertanyaan spesifik semester itu)
        $pdo->exec("DELETE FROM diskusi_threads WHERE role_pembuat = 'siswa'");
        
        // ARSIPKAN TOPIK GURU (Jangan dihapus)
        $pdo->exec("UPDATE diskusi_threads SET status = 'draft' WHERE role_pembuat = 'guru'");
        
        // --- 3. UJIAN ---
        // Nonaktifkan semua ujian
        $pdo->exec("UPDATE ujian SET status = 'draft'");
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    // --- HELPER: GET CLASS ID BY NAME ---
    function getClassId($pdo, $name) {
        $stmt = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ? LIMIT 1");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    }

    // --- HELPER: BACKUP SISWA TO JSON ---
    function backupSiswaToJson($pdo) {
        $stmt = $pdo->query("SELECT * FROM siswa");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            $backupDir = '../backups';
            if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
            $filename = $backupDir . '/siswa_backup_' . date('Y-m-d_H-i-s') . '.json';
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    // --- HELPER: CREATE ALUMNI TABLE ---
    function createAlumniTable($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS siswa_alumni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nis VARCHAR(20),
            nama VARCHAR(100),
            tahun_lulus YEAR,
            original_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
    }

    // --- AKSI 1: GANTI SEMESTER ---
    if ($action === 'ganti_semester') {
        $semester_baru = $_POST['semester_tujuan'];
        
        // Update Semester
        $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('semester_aktif', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$semester_baru]);

        // Jalankan Logika Arsip
        resetTransactionalData($pdo);

        $_SESSION['success_message'] = "Semester diganti. Tugas & Diskusi Guru telah DIARSIPKAN (Draft), Jawaban Siswa dibersihkan.";
    }

    // --- AKSI 2: NAIK KELAS (SAFE VERSION) ---
    elseif ($action === 'naik_kelas') {
        // 1. Ambil ID Kelas Dinamis
        $id_7 = getClassId($pdo, 'VII');
        $id_8 = getClassId($pdo, 'VIII');
        $id_9 = getClassId($pdo, 'IX');

        if (!$id_7 || !$id_8 || !$id_9) {
            throw new Exception("Data Kelas (VII, VIII, IX) tidak lengkap di database.");
        }

        // 2. BACKUP DATA (Safety First) - No Transaction needed (Read Only/File Write)
        backupSiswaToJson($pdo);

        // 3. SIAPKAN TABEL ALUMNI - DDL must be outside Transaction (Causes Implicit Commit)
        createAlumniTable($pdo);

        $pdo->beginTransaction();
        try {
            // 4. ARSIPKAN KELAS 9 KE ALUMNI (Jangan Hapus Dulu)
            // Kita simpan data mentah di kolom 'original_data' jaga-jaga
            $stmtArchive = $pdo->prepare("
                INSERT INTO siswa_alumni (nis, nama, tahun_lulus, original_data)
                SELECT nis, nama, YEAR(NOW()), JSON_OBJECT('id', id, 'kelas_id', kelas_id, 'password', password)
                FROM siswa WHERE kelas_id = ?
            ");
            $stmtArchive->execute([$id_9]);

            // 5. HAPUS KELAS 9 (Setelah Aman Diarsip)
            $stmtDel = $pdo->prepare("DELETE FROM siswa WHERE kelas_id = ?");
            $stmtDel->execute([$id_9]);

            // 6. NAIKKAN KELAS (8 -> 9, 7 -> 8)
            // Update 8 ke 9
            $stmtPromote8 = $pdo->prepare("UPDATE siswa SET kelas_id = ? WHERE kelas_id = ?");
            $stmtPromote8->execute([$id_9, $id_8]);

            // Update 7 ke 8
            $stmtPromote7 = $pdo->prepare("UPDATE siswa SET kelas_id = ? WHERE kelas_id = ?");
            $stmtPromote7->execute([$id_8, $id_7]);

            // 7. RESET SEMESTER KE 1
            $stmtSem = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('semester_aktif', '1') ON DUPLICATE KEY UPDATE setting_value = '1'");
            $stmtSem->execute();

            // 8. BERSIH-BERSIH SYSTEM (Tugas, Diskusi dll)
            resetTransactionalData($pdo);

            $pdo->commit();
            $_SESSION['success_message'] = "Kenaikan Kelas Sukses! Alumni diarsipkan, Siswa naik tingkat.";
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal: " . $e->getMessage();
}

header("Location: ../admin/pengaturan_sistem.php");
exit();
?>