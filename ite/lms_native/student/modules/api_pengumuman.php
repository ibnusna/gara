<?php
// API Endpoint: Get Pengumuman Kelas
// File: student/modules/api_pengumuman.php
header('Content-Type: application/json');

require_once '../../actions/auth_helper.php';
require_once '../../config/database.php';

// Cek Sesi Manual (karena ini API request)
// requireSiswa() mungkin redirect, kita butuh return 401 jika gagal token/session (untuk simplicity kita asumsi session masih aktif karena dipanggil dari halaman yang sama)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$response = [
    'found' => false,
    'data' => null
];

try {
    $mapel_id = $_SESSION['mapel_id'] ?? null;
    $kelas_id = $_SESSION['kelas_id'] ?? null;
    
    if ($mapel_id && $kelas_id) {
        $stmtInfo = $pdo->prepare("SELECT judul, isi, updated_at FROM pengumuman WHERE mapel_id = ? AND kelas_id = ? ORDER BY updated_at DESC LIMIT 1");
        $stmtInfo->execute([$mapel_id, $kelas_id]);
        $infoKelas = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        
        // DEBUG LOGGING
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - SessID: " . session_id() . " - Mapel: $mapel_id, Kelas: $kelas_id - Found: " . ($infoKelas ? 'YES' : 'NO') . "\n", FILE_APPEND);

        if ($infoKelas) {
            $response['found'] = true;
            $response['data'] = $infoKelas;
        }
    } else {
         file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - MISSING SESSION VARS - SessID: " . session_id() . " - Dump: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
    }
} catch (PDOException $e) {
    // Log error internally if needed
    $response['error'] = 'Database error';
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - DB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo json_encode($response);
exit;
