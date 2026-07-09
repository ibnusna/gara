<?php
// GARA - Garuda Akademi
// File: actions/get_submission_detail.php
// Tujuan: Mengambil data status pengumpulan tugas seluruh siswa di kelas tertentu (AJAX).

require_once '../config/database.php';
require_once 'auth_helper.php';

// Cek Login Guru
requireGuruSession();

header('Content-Type: application/json');

$tugas_id = $_GET['tugas_id'] ?? null;
$mapel_id = $_SESSION['mapel_id'];
$kelas_id = $_SESSION['kelas_id'];

if (!$tugas_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID Tugas tidak valid']);
    exit;
}

try {
    // Ambil SEMUA siswa di kelas ini, lalu LEFT JOIN dengan tabel pengumpulan tugas
    // Ini agar kita bisa melihat siapa yang BELUM mengumpulkan (status NULL)
    $sql = "SELECT 
                s.id as siswa_id, 
                s.nis, 
                s.nama, 
                tp.id as pengumpulan_id,
                tp.link_pengumpulan,
                tp.dikumpulkan_pada,
                tp.catatan_siswa,
                tp.nilai,
                tp.metode,
                tp.file_type
            FROM siswa s
            LEFT JOIN tugas_pengumpulan tp 
                ON s.id = tp.siswa_id AND tp.tugas_id = ?
            WHERE s.kelas_id = ?
            ORDER BY s.nama ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tugas_id, $kelas_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format Data untuk Frontend
    $result = [];
    $total_siswa = count($data);
    $sudah_kumpul = 0;

    foreach ($data as $row) {
        $status = $row['link_pengumpulan'] ? 'sudah' : 'belum';
        if ($status === 'sudah') $sudah_kumpul++;

        $result[] = [
            'nama' => $row['nama'],
            'nis' => $row['nis'],
            'status' => $status,
            'waktu' => $row['dikumpulkan_pada'] ? date('d/m/Y H:i', strtotime($row['dikumpulkan_pada'])) : '-',
            'link' => $row['link_pengumpulan'],
            'metode' => $row['metode'] ?? 'link', // Default to link if null
            'file_type' => $row['file_type'],
            'catatan' => $row['catatan_siswa'],
            'nilai' => $row['nilai']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'rekap' => [
            'total' => $total_siswa,
            'sudah' => $sudah_kumpul,
            'belum' => $total_siswa - $sudah_kumpul
        ],
        'siswa' => $result
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>