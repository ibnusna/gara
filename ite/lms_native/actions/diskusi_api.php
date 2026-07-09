<?php
// GARA - Garuda Akademi
// File: actions/diskusi_api.php
// Tujuan: API Endpoint untuk Frontend Siswa (Get, Post, Edit, Delete)
// FIX: Timezone Issue (InfinityFree UTC vs WIB) - Menggunakan PHP Timestamp

require_once '../config/database.php';
require_once 'auth_helper.php';

// 1. HARDCODE ZONA WAKTU (Agar konsisten perhitungan 'Waktu Relatif')
date_default_timezone_set('Asia/Jakarta');

// Security Layer: Pastikan hanya Siswa yang akses
requireSiswa();

header('Content-Type: application/json');

// Ambil Konteks dari Sesi
$mapel_id   = $_SESSION['mapel_id'];
$kelas_id   = $_SESSION['kelas_id'];
$siswa_id   = $_SESSION['user_id'];
$siswa_nama = $_SESSION['nama'];
$siswa_nis  = $_SESSION['nis'] ?? '-';

// Cek Action
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        
        // ========================================
        // 1. AMBIL DAFTAR TOPIK (THREADS)
        // ========================================
        case 'get_threads':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $sql = "
                SELECT t.*, 
                    s.nama as nama_siswa_db,
                    s.nis as nis_siswa,
                    (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
                FROM diskusi_threads t
                LEFT JOIN siswa s ON t.siswa_id = s.id
                WHERE t.mapel_id = :mapel_id 
                  AND t.kelas_id = :kelas_id 
                  AND t.status = 'aktif'
                ORDER BY t.is_pinned DESC, t.created_at DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':mapel_id', $mapel_id, PDO::PARAM_INT);
            $stmt->bindValue(':kelas_id', $kelas_id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($threads as &$t) {
                // Logic Nama: Guru vs Siswa vs Null
                if ($t['role_pembuat'] === 'guru') {
                    $t['nama_penulis'] = 'Ibnu Sina Sudrajat';
                    $t['is_guru'] = true;
                } else {
                    // Logic: Jika DB nama oke, pakai DB. Jika tidak, cek apakah ini saya?
                    if ($t['role_pembuat'] === 'siswa' && $t['siswa_id'] == $siswa_id) {
                         $t['nama_penulis'] = $t['nama_siswa_db'] ?? $siswa_nama;
                         $t['nis_siswa']    = $t['nis_siswa'] ?? $siswa_nis;
                    } else {
                         $t['nama_penulis'] = $t['nama_siswa_db'] ?? 'Siswa Tidak Dikenal';
                    }
                    $t['is_guru'] = false;
                }
                
                $t['waktu_relatif'] = formatWaktuRelatif($t['created_at']);
                $t['is_me'] = ($t['role_pembuat'] === 'siswa' && $t['siswa_id'] == $siswa_id);
            }

            $hasNextPage = count($threads) === $limit;

            echo json_encode([
                'status' => 'success', 
                'data' => $threads,
                'meta' => ['page' => $page, 'has_next' => $hasNextPage]
            ]);
            break;

        // ========================================
        // 2. AMBIL DETAIL THREAD (UNTUK HEADER MODAL)
        // ========================================
        case 'get_thread_detail':
            $thread_id = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
            
            if ($thread_id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID thread tidak valid']);
                exit;
            }

            $sql = "
                SELECT t.*, 
                    s.nama as nama_siswa_db,
                    s.nis as nis_siswa,
                    (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
                FROM diskusi_threads t
                LEFT JOIN siswa s ON t.siswa_id = s.id
                WHERE t.id = :thread_id
                  AND t.mapel_id = :mapel_id
                  AND t.kelas_id = :kelas_id
                  AND t.status = 'aktif'
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
            $stmt->bindValue(':mapel_id', $mapel_id, PDO::PARAM_INT);
            $stmt->bindValue(':kelas_id', $kelas_id, PDO::PARAM_INT);
            $stmt->execute();
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($thread) {
                // Logic Nama
                if ($thread['role_pembuat'] === 'guru') {
                    $thread['nama_penulis'] = 'Ibnu Sina Sudrajat';
                    $thread['is_guru'] = true;
                } else {
                    if ($thread['role_pembuat'] === 'siswa' && $thread['siswa_id'] == $siswa_id) {
                        $thread['nama_penulis'] = $thread['nama_siswa_db'] ?? $siswa_nama;
                        $thread['nis_siswa']    = $thread['nis_siswa'] ?? $siswa_nis;
                    } else {
                        $thread['nama_penulis'] = $thread['nama_siswa_db'] ?? 'Siswa Tidak Dikenal';
                    }
                    $thread['is_guru'] = false;
                }

                $thread['waktu_relatif'] = formatWaktuRelatif($thread['created_at']);
                $thread['waktu_lengkap'] = date('H:i · d M Y', strtotime($thread['created_at']));
                $thread['is_me'] = ($thread['role_pembuat'] === 'siswa' && $thread['siswa_id'] == $siswa_id);
                echo json_encode(['status' => 'success', 'data' => $thread]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Diskusi tidak ditemukan']);
            }
            break;

        // ========================================
        // 3. AMBIL BALASAN (REPLIES)
        // ========================================
        case 'get_replies':
            $thread_id = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
            
            if ($thread_id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID thread tidak valid']);
                exit;
            }

            $sql = "
                SELECT r.*, 
                    s.nama as nama_siswa_db,
                    s.nis as nis_siswa
                FROM diskusi_replies r
                LEFT JOIN siswa s ON r.siswa_id = s.id
                WHERE r.thread_id = :thread_id
                ORDER BY r.created_at ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':thread_id', $thread_id, PDO::PARAM_INT);
            $stmt->execute();
            $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($replies as &$r) {
                // Logic Nama
                if ($r['role_pembuat'] === 'guru') {
                    $r['nama_penulis'] = 'Ibnu Sina Sudrajat'; // Atau 'Guru Mata Pelajaran'
                    $r['is_guru'] = true;
                } else {
                    if ($r['role_pembuat'] === 'siswa' && $r['siswa_id'] == $siswa_id) {
                        $r['nama_penulis'] = $r['nama_siswa_db'] ?? $siswa_nama;
                        $r['nis_siswa']    = $r['nis_siswa'] ?? $siswa_nis;
                    } else {
                        $r['nama_penulis'] = $r['nama_siswa_db'] ?? 'Siswa Tidak Dikenal';
                    }
                    $r['is_guru'] = false;
                }

                $r['waktu_relatif'] = formatWaktuRelatif($r['created_at']);
                $r['is_me'] = ($r['role_pembuat'] === 'siswa' && $r['siswa_id'] == $siswa_id);
            }

            echo json_encode(['status' => 'success', 'data' => $replies]);
            break;

        // ========================================
        // 4. POST TOPIK BARU (CREATE THREAD)
        // ========================================
        case 'create_thread':
            $judul = trim($_POST['judul'] ?? '');
            $isi   = trim($_POST['isi_konten'] ?? '');
            
            if (empty($judul)) {
                $judul = mb_substr($isi, 0, 50) . (mb_strlen($isi) > 50 ? '...' : '');
            }
            
            if (empty($isi)) {
                echo json_encode(['status' => 'error', 'message' => 'Konten tidak boleh kosong']);
                exit;
            }

            // Handle Upload Gambar
            $media_path = null;
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($ext, $allowedExts)) {
                    $uploadDir = '../uploads/diskusi/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $newName = uniqid('IMG_') . '.' . $ext;
                    $target = $uploadDir . $newName;
                    
                    if (move_uploaded_file($_FILES['media']['tmp_name'], $target)) {
                        $media_path = 'uploads/diskusi/' . $newName;
                    }
                }
            }

            // FIX TIMEZONE: Generate waktu di PHP (WIB)
            $waktu_sekarang = date('Y-m-d H:i:s');

            // Insert ke Database
            $sql = "INSERT INTO diskusi_threads 
                    (mapel_id, kelas_id, judul, isi_konten, role_pembuat, siswa_id, media_path, status, created_at) 
                    VALUES (?, ?, ?, ?, 'siswa', ?, ?, 'aktif', ?)";
            
            $stmt = $pdo->prepare($sql);
            // Ganti NOW() dengan $waktu_sekarang
            $stmt->execute([$mapel_id, $kelas_id, $judul, $isi, $siswa_id, $media_path, $waktu_sekarang]);
            
            // Ambil ID terakhir
            $lastId = $pdo->lastInsertId();

            // Ambil Data Lengkap Thread yang baru dibuat
            $sqlNew = "
                SELECT t.*, 
                    s.nama as nama_siswa_db,
                    s.nis as nis_siswa,
                    0 as jumlah_balasan
                FROM diskusi_threads t
                LEFT JOIN siswa s ON t.siswa_id = s.id
                WHERE t.id = ?
            ";
            $stmtNew = $pdo->prepare($sqlNew);
            $stmtNew->execute([$lastId]);
            $newThread = $stmtNew->fetch(PDO::FETCH_ASSOC);

            if ($newThread) {
                // Postingan baru pasti buatan current user (siswa)
                $newThread['nama_penulis'] = $newThread['nama_siswa_db'] ?? $siswa_nama; // Fallback ke sesi jika DB delay
                $newThread['nis_siswa']    = $newThread['nis_siswa'] ?? $siswa_nis;
                $newThread['waktu_relatif'] = 'Baru saja';
                $newThread['waktu_lengkap'] = date('H:i · d M Y', strtotime($newThread['created_at']));
                $newThread['is_guru'] = false;
                $newThread['is_me'] = true;

                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Postingan berhasil dibuat',
                    'data' => $newThread
                ]);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Postingan berhasil dibuat (Reload diperlukan)']);
            }
            break;

        // ========================================
        // 5. POST BALASAN (CREATE REPLY)
        // ========================================
        case 'post_reply':
            $thread_id = isset($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
            $isi = trim($_POST['isi_balasan'] ?? '');
            
            if ($thread_id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID thread tidak valid']);
                exit;
            }
            
            if (empty($isi)) {
                echo json_encode(['status' => 'error', 'message' => 'Balasan tidak boleh kosong']);
                exit;
            }

            $cekThread = $pdo->prepare("SELECT id FROM diskusi_threads WHERE id = ? AND mapel_id = ? AND kelas_id = ?");
            $cekThread->execute([$thread_id, $mapel_id, $kelas_id]);
            
            if (!$cekThread->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Thread tidak ditemukan']);
                exit;
            }

            // FIX TIMEZONE: Generate waktu di PHP (WIB)
            $waktu_sekarang = date('Y-m-d H:i:s');

            $sql = "INSERT INTO diskusi_replies 
                    (thread_id, isi_balasan, role_pembuat, siswa_id, created_at) 
                    VALUES (?, ?, 'siswa', ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            // Ganti NOW() dengan $waktu_sekarang
            $stmt->execute([$thread_id, $isi, $siswa_id, $waktu_sekarang]);
            
            // Ambil ID terakhir
            $lastId = $pdo->lastInsertId();

            // Ambil Data Lengkap Reply yang baru dibuat
            $sqlNew = "
                SELECT r.*, 
                    s.nama as nama_siswa_db,
                    s.nis as nis_siswa
                FROM diskusi_replies r
                LEFT JOIN siswa s ON r.siswa_id = s.id
                WHERE r.id = ?
            ";
            $stmtNew = $pdo->prepare($sqlNew);
            $stmtNew->execute([$lastId]);
            $newReply = $stmtNew->fetch(PDO::FETCH_ASSOC);

            if ($newReply) {
                $newReply['nama_penulis'] = $newReply['nama_siswa_db'] ?? $siswa_nama;
                $newReply['nis_siswa']    = $newReply['nis_siswa'] ?? $siswa_nis;
                $newReply['waktu_relatif'] = 'Baru saja';
                $newReply['is_me'] = true;
                $newReply['is_guru'] = false;

                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Balasan berhasil dikirim',
                    'data' => $newReply
                ]);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Balasan berhasil dikirim (Reload diperlukan)']);
            }
            break;

        // ========================================
        // 6. EDIT THREAD
        // ========================================
        case 'edit_thread':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $isi = trim($_POST['isi_konten'] ?? '');
            
            if ($id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
                exit;
            }
            
            if (empty($isi)) {
                echo json_encode(['status' => 'error', 'message' => 'Konten tidak boleh kosong']);
                exit;
            }

            $cek = $pdo->prepare("
                SELECT id FROM diskusi_threads 
                WHERE id = ? AND siswa_id = ? AND mapel_id = ? AND kelas_id = ?
            ");
            $cek->execute([$id, $siswa_id, $mapel_id, $kelas_id]);
            
            if (!$cek->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk mengedit postingan ini']);
                exit;
            }

            $upd = $pdo->prepare("UPDATE diskusi_threads SET isi_konten = ? WHERE id = ?");
            $upd->execute([$isi, $id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Postingan berhasil diperbarui']);
            break;

        // ========================================
        // 7. EDIT REPLY
        // ========================================
        case 'edit_reply':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $isi = trim($_POST['isi_balasan'] ?? '');
            
            if ($id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
                exit;
            }
            
            if (empty($isi)) {
                echo json_encode(['status' => 'error', 'message' => 'Balasan tidak boleh kosong']);
                exit;
            }

            $cek = $pdo->prepare("
                SELECT r.id 
                FROM diskusi_replies r
                INNER JOIN diskusi_threads t ON r.thread_id = t.id
                WHERE r.id = ? 
                  AND r.siswa_id = ? 
                  AND r.role_pembuat = 'siswa'
                  AND t.mapel_id = ? 
                  AND t.kelas_id = ?
            ");
            $cek->execute([$id, $siswa_id, $mapel_id, $kelas_id]);
            
            if (!$cek->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk mengedit balasan ini']);
                exit;
            }

            $upd = $pdo->prepare("UPDATE diskusi_replies SET isi_balasan = ? WHERE id = ?");
            $upd->execute([$isi, $id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Balasan berhasil diperbarui']);
            break;

        // ========================================
        // 8. DELETE THREAD
        // ========================================
        case 'delete_thread':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
                exit;
            }

            $cek = $pdo->prepare("
                SELECT id, media_path FROM diskusi_threads 
                WHERE id = ? AND siswa_id = ? AND mapel_id = ? AND kelas_id = ?
            ");
            $cek->execute([$id, $siswa_id, $mapel_id, $kelas_id]);
            $thread = $cek->fetch(PDO::FETCH_ASSOC);
            
            if (!$thread) {
                echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk menghapus postingan ini']);
                exit;
            }

            if (!empty($thread['media_path']) && file_exists('../' . $thread['media_path'])) {
                unlink('../' . $thread['media_path']);
            }

            $del = $pdo->prepare("DELETE FROM diskusi_threads WHERE id = ?");
            $del->execute([$id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Postingan berhasil dihapus']);
            break;

        // ========================================
        // 9. DELETE REPLY
        // ========================================
        case 'delete_reply':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id === 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
                exit;
            }

            $cek = $pdo->prepare("
                SELECT r.id, r.media_path 
                FROM diskusi_replies r
                INNER JOIN diskusi_threads t ON r.thread_id = t.id
                WHERE r.id = ? 
                  AND r.siswa_id = ? 
                  AND r.role_pembuat = 'siswa'
                  AND t.mapel_id = ? 
                  AND t.kelas_id = ?
            ");
            $cek->execute([$id, $siswa_id, $mapel_id, $kelas_id]);
            $reply = $cek->fetch(PDO::FETCH_ASSOC);
            
            if (!$reply) {
                echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki izin untuk menghapus balasan ini']);
                exit;
            }

            if (!empty($reply['media_path']) && file_exists('../' . $reply['media_path'])) {
                unlink('../' . $reply['media_path']);
            }

            $del = $pdo->prepare("DELETE FROM diskusi_replies WHERE id = ?");
            $del->execute([$id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Balasan berhasil dihapus']);
            break;

        // ========================================
        // 10. CHECK UPDATES (LONG POLLING SIMULATION)
        // ========================================
        case 'check_updates':
            $last_thread_id = isset($_GET['last_thread_id']) ? (int)$_GET['last_thread_id'] : 0;
            $active_thread_id = isset($_GET['active_thread_id']) ? (int)$_GET['active_thread_id'] : 0;
            $last_reply_id = isset($_GET['last_reply_id']) ? (int)$_GET['last_reply_id'] : 0;
            
            $response = ['status' => 'success', 'new_threads' => [], 'new_replies' => []];

            // 1. Cek Thread Baru
            if ($last_thread_id > 0) {
                $sql = "
                    SELECT t.*, 
                        s.nama as nama_siswa_db,
                        s.nis as nis_siswa,
                        (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
                    FROM diskusi_threads t
                    LEFT JOIN siswa s ON t.siswa_id = s.id
                    WHERE t.mapel_id = :mapel_id 
                      AND t.kelas_id = :kelas_id 
                      AND t.status = 'aktif'
                      AND t.id > :last_id
                      AND t.siswa_id != :my_id
                    ORDER BY t.created_at ASC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['mapel_id' => $mapel_id, 'kelas_id' => $kelas_id, 'last_id' => $last_thread_id, 'my_id' => $siswa_id]);
                $newThreads = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($newThreads as &$t) {
                    // Logic Nama
                    if ($t['role_pembuat'] === 'guru') {
                        $t['nama_penulis'] = 'Ibnu Sina Sudrajat';
                        $t['is_guru'] = true;
                    } else {
                         if ($t['role_pembuat'] === 'siswa' && $t['siswa_id'] == $siswa_id) {
                             $t['nama_penulis'] = $t['nama_siswa_db'] ?? $siswa_nama;
                             $t['nis_siswa']    = $t['nis_siswa'] ?? $siswa_nis;
                         } else {
                             $t['nama_penulis'] = $t['nama_siswa_db'] ?? 'Siswa Tidak Dikenal';
                         }
                        $t['is_guru'] = false;
                    }

                    $t['waktu_relatif'] = formatWaktuRelatif($t['created_at']);
                    $t['is_me'] = false;
                }
                $response['new_threads'] = $newThreads;
            }

            // 2. Cek Reply Baru (Jika sedang membuka thread)
            if ($active_thread_id > 0 && $last_reply_id > 0) {
                $sql = "
                    SELECT r.*, 
                        s.nama as nama_siswa_db,
                        s.nis as nis_siswa
                    FROM diskusi_replies r
                    LEFT JOIN siswa s ON r.siswa_id = s.id
                    WHERE r.thread_id = :thread_id
                      AND r.id > :last_id
                      AND r.siswa_id != :my_id
                    ORDER BY r.created_at ASC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['thread_id' => $active_thread_id, 'last_id' => $last_reply_id, 'my_id' => $siswa_id]);
                $newReplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($newReplies as &$r) {
                    // Logic Nama
                    if ($r['role_pembuat'] === 'guru') {
                        $r['nama_penulis'] = 'Ibnu Sina Sudrajat';
                        $r['is_guru'] = true;
                    } else {
                        if ($r['role_pembuat'] === 'siswa' && $r['siswa_id'] == $siswa_id) {
                            $r['nama_penulis'] = $r['nama_siswa_db'] ?? $siswa_nama;
                            $r['nis_siswa']    = $r['nis_siswa'] ?? $siswa_nis;
                        } else {
                            $r['nama_penulis'] = $r['nama_siswa_db'] ?? 'Siswa Tidak Dikenal';
                        }
                        $r['is_guru'] = false;
                    }
                    
                    $r['waktu_relatif'] = formatWaktuRelatif($r['created_at']);
                    $r['is_me'] = false; // Karena query filter != my_id
                }
                $response['new_replies'] = $newReplies;
            }

            echo json_encode($response);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
            break;
    }

} catch (PDOException $e) {
    error_log('Diskusi API Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi.'
    ]);
}

// Helper: Format Waktu Relatif
// Dengan timezone PHP yang sudah WIB, selisih waktu akan akurat.
function formatWaktuRelatif($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 5) {
        return 'Baru saja';
    } elseif ($diff < 60) {
        return floor($diff) . 'd'; // detik
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm'; // menit
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'j'; // jam
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . 'h'; // hari
    } else {
        return date('d M', $time);
    }
}
?>