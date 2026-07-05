<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiskusiController extends Controller
{
    


    public function index(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');
        $user = auth()->user();
        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();
        $siswa_nama = $siswa ? $siswa->nama : ($user->nama_lengkap ?? session('nama'));
        $siswa_nis = $siswa && $siswa->nis ? $siswa->nis : (session('nis') ?? '-');

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('student.pilih-mapel')->with('error', 'Silakan pilih mata pelajaran terlebih dahulu.');
        }

        $guruInfo = DB::connection('mysql_apps')->selectOne("
            SELECT u.id as user_id, g.nama_lengkap, u.profile_photo 
            FROM teaching_assignments ta 
            JOIN " . config('database.connections.mysql_auth.database') . ".guru g ON ta.teacher_id = g.id 
            JOIN " . config('database.connections.mysql_auth.database') . ".users u ON g.user_id = u.id 
            WHERE ta.class_id = ? AND ta.subject_id = ?
        ", [$kelas_id, $mapel_id]);
        
        $guru_nama = $guruInfo ? $guruInfo->nama_lengkap : 'Guru Pengajar';

        
        $avatar_siswa = $this->getConsistentAvatarUrl($siswa_nama, $user->profile_photo);
        $avatar_guru = $this->getConsistentAvatarUrl($guru_nama, $guruInfo ? $guruInfo->profile_photo : null);

        return view('siswa.diskusi.index', compact(
            'siswa_nama',
            'siswa_nis',
            'avatar_siswa',
            'avatar_guru',
            'guru_nama'
        ));
    }

    


    public function api(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');
        $user = auth()->user();
        $siswa_id = $user->id;
        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $siswa_id)->first();
        $siswa_nama = $siswa ? $siswa->nama : ($user->nama_lengkap ?? session('nama'));
        $siswa_nis = $siswa && $siswa->nis ? $siswa->nis : (session('nis') ?? '-');

        $guruInfo = DB::connection('mysql_apps')->selectOne("
            SELECT u.id as user_id, g.nama_lengkap, u.profile_photo 
            FROM teaching_assignments ta 
            JOIN " . config('database.connections.mysql_auth.database') . ".guru g ON ta.teacher_id = g.id 
            JOIN " . config('database.connections.mysql_auth.database') . ".users u ON g.user_id = u.id 
            WHERE ta.class_id = ? AND ta.subject_id = ?
        ", [$kelas_id, $mapel_id]);
        $guru_nama = $guruInfo ? $guruInfo->nama_lengkap : 'Guru Pengajar';
        $guru_photo = $guruInfo ? $guruInfo->profile_photo : null;

        $action = $request->input('action');

        date_default_timezone_set('Asia/Jakarta');

        try {
            switch ($action) {
                
                case 'get_threads':
                    $page = (int) $request->input('page', 1);
                    $limit = 10;
                    $offset = ($page - 1) * $limit;

                    $sql = "
                        SELECT t.*, 
                            u.nama_lengkap as user_full_name,
                            u.profile_photo as user_photo,
                            s.nama as nama_siswa_db,
                            s.nis as nis_siswa,
                            (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
                        FROM diskusi_threads t
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".users u ON t.siswa_id = u.id
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON u.id = s.user_id
                        WHERE t.mapel_id = ? 
                          AND t.kelas_id = ? 
                          AND t.status = 'aktif'
                        ORDER BY t.is_pinned DESC, t.created_at DESC
                        LIMIT ? OFFSET ?
                    ";

                    $threadsInfo = DB::connection('mysql_apps')->select($sql, [$mapel_id, $kelas_id, $limit, $offset]);
                    $threads = array_map(function ($t) {
                        return (array) $t; }, $threadsInfo);

                    foreach ($threads as &$t) {
                        $t['is_guru'] = ($t['role_pembuat'] === 'guru');
                        $t['is_me'] = ($t['role_pembuat'] === 'siswa' && $t['siswa_id'] == $siswa_id);
                        
                        
                        if ($t['is_guru']) {
                            $t['nama_penulis'] = $guru_nama;
                            $t['user_photo'] = $guru_photo;
                        } else {
                            $namaSiswa = $t['user_full_name'] ?? $t['nama_siswa_db'] ?? 'Siswa';
                            $nisSiswa  = $t['nis_siswa'] ?? '-';
                            $t['nama_penulis'] = $namaSiswa;
                            $t['nis_siswa']    = $nisSiswa;
                        }

                        
                        $t['avatar'] = $this->getConsistentAvatarUrl($t['nama_penulis'], $t['user_photo']);

                        $t['waktu_relatif'] = $this->formatWaktuRelatif($t['created_at']);
                    }

                    $hasNextPage = count($threads) === $limit;

                    return response()->json([
                        'status' => 'success',
                        'data' => $threads,
                        'meta' => ['page' => $page, 'has_next' => $hasNextPage]
                    ]);

                
                case 'get_thread_detail':
                    $thread_id = (int) $request->input('thread_id', 0);
                    if ($thread_id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID thread tidak valid']);

                    $sql = "
                        SELECT t.*, 
                            u.nama_lengkap as user_full_name,
                            u.profile_photo as user_photo,
                            s.nama as nama_siswa_db,
                            s.nis as nis_siswa,
                            (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan
                        FROM diskusi_threads t
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".users u ON t.siswa_id = u.id
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON u.id = s.user_id
                        WHERE t.id = ? AND t.mapel_id = ? AND t.kelas_id = ? AND t.status = 'aktif'
                    ";

                    $threadInfo = DB::connection('mysql_apps')->selectOne($sql, [$thread_id, $mapel_id, $kelas_id]);
                    if ($threadInfo) {
                        $thread = (array) $threadInfo;
                        $thread['is_guru'] = ($thread['role_pembuat'] === 'guru');
                        $thread['is_me'] = ($thread['role_pembuat'] === 'siswa' && $thread['siswa_id'] == $siswa_id);
                        
                        
                        if ($thread['is_guru']) {
                            $thread['nama_penulis'] = $guru_nama;
                            $thread['user_photo'] = $guru_photo;
                        } else {
                            $namaSiswa = $thread['user_full_name'] ?? $thread['nama_siswa_db'] ?? 'Siswa';
                            $nisSiswa  = $thread['nis_siswa'] ?? '-';
                            $thread['nama_penulis'] = $namaSiswa;
                            $thread['nis_siswa']    = $nisSiswa;
                        }

                        
                        $thread['avatar'] = $this->getConsistentAvatarUrl($thread['nama_penulis'], $thread['user_photo']);
                        $thread['waktu_relatif'] = $this->formatWaktuRelatif($thread['created_at']);
                        $thread['waktu_lengkap'] = date('H:i · d M Y', strtotime($thread['created_at']));
                        return response()->json(['status' => 'success', 'data' => $thread]);
                    }
                    return response()->json(['status' => 'error', 'message' => 'Diskusi tidak ditemukan']);

                
                case 'get_replies':
                    $thread_id = (int) $request->input('thread_id', 0);
                    if ($thread_id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID thread tidak valid']);

                    $sql = "
                        SELECT r.*, 
                            u.nama_lengkap as user_full_name,
                            u.profile_photo as user_photo,
                            s.nama as nama_siswa_db,
                            s.nis as nis_siswa
                        FROM diskusi_replies r
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".users u ON r.siswa_id = u.id
                        LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON u.id = s.user_id
                        WHERE r.thread_id = ?
                        ORDER BY r.created_at ASC
                    ";
                    $repliesInfo = DB::connection('mysql_apps')->select($sql, [$thread_id]);
                    $replies = array_map(function ($r) {
                        return (array) $r; }, $repliesInfo);

                    foreach ($replies as &$r) {
                        $r['is_guru'] = ($r['role_pembuat'] === 'guru');
                        $r['is_me'] = ($r['role_pembuat'] === 'siswa' && $r['siswa_id'] == $siswa_id);
                        
                        
                        if ($r['is_guru']) {
                            $r['nama_penulis'] = $guru_nama;
                            $r['user_photo']   = $guru_photo;
                            $r['nis_siswa']    = null; 
                        } else {
                            $namaSiswa = $r['user_full_name'] ?? $r['nama_siswa_db'] ?? 'Siswa';
                            $nisSiswa  = $r['nis_siswa'] ?? null;
                            $r['nama_penulis'] = $namaSiswa;
                            $r['nis_siswa']    = $nisSiswa;
                        }

                        
                        $r['avatar'] = $this->getConsistentAvatarUrl($r['nama_penulis'], $r['user_photo']);

                        $r['waktu_relatif'] = $this->formatWaktuRelatif($r['created_at']);
                    }
                    return response()->json(['status' => 'success', 'data' => $replies]);

                
                case 'create_thread':
                    $judul = trim($request->input('judul', ''));
                    $isi = trim($request->input('isi_konten', ''));

                    if (empty($judul)) {
                        $judul = mb_substr($isi, 0, 50) . (mb_strlen($isi) > 50 ? '...' : '');
                    }
                    if (empty($isi))
                        return response()->json(['status' => 'error', 'message' => 'Konten tidak boleh kosong']);

                    $media_path = null;
                    if ($request->hasFile('media') && $request->file('media')->isValid()) {
                        $file = $request->file('media');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
                        $fileMime = $file->getMimeType();

                        
                        if (!in_array($ext, $allowedExts) || !in_array($fileMime, $allowedMimes)) {
                            return response()->json([
                                'status'  => 'error',
                                'message' => 'Format file tidak diizinkan. Hanya gambar (JPG, PNG, WEBP, GIF) yang dapat dilampirkan pada diskusi.'
                            ], 422);
                        }

                        $newName = uniqid('IMG_') . '.' . $ext;
                        if (!file_exists(public_path('lms/uploads/diskusi'))) {
                            mkdir(public_path('lms/uploads/diskusi'), 0755, true);
                        }
                        $file->move(public_path('lms/uploads/diskusi'), $newName);
                        $media_path = 'uploads/diskusi/' . $newName;
                    }

                    $waktu_sekarang = date('Y-m-d H:i:s');
                    $sql = "INSERT INTO diskusi_threads (mapel_id, kelas_id, judul, isi_konten, role_pembuat, siswa_id, media_path, status, created_at) VALUES (?, ?, ?, ?, 'siswa', ?, ?, 'aktif', ?)";

                    DB::connection('mysql_apps')->insert($sql, [$mapel_id, $kelas_id, $judul, $isi, $siswa_id, $media_path, $waktu_sekarang]);
                    $lastId = DB::connection('mysql_apps')->getPdo()->lastInsertId();

                    $sqlNew = "SELECT t.*, s.nama as nama_siswa_db, s.nis as nis_siswa, 0 as jumlah_balasan FROM diskusi_threads t LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON t.siswa_id = s.user_id WHERE t.id = ?";
                    $newThreadInfo = DB::connection('mysql_apps')->selectOne($sqlNew, [$lastId]);

                    if ($newThreadInfo) {
                        $newThread = (array) $newThreadInfo;
                        $newThread['nama_penulis'] = $newThread['nama_siswa_db'] ?? $siswa_nama;
                        $newThread['avatar'] = $this->getConsistentAvatarUrl($newThread['nama_penulis'], $user->profile_photo);
                        $newThread['nis_siswa'] = $newThread['nis_siswa'] ?? $siswa_nis;
                        $newThread['waktu_relatif'] = 'Baru saja';
                        $newThread['waktu_lengkap'] = date('H:i · d M Y', strtotime($newThread['created_at']));
                        $newThread['is_guru'] = false;
                        $newThread['is_me'] = true;
                        return response()->json(['status' => 'success', 'message' => 'Postingan berhasil dibuat', 'data' => $newThread]);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Postingan berhasil dibuat (Reload diperlukan)']);

                
                case 'post_reply':
                    $thread_id = (int) $request->input('thread_id', 0);
                    $isi = trim($request->input('isi_balasan', ''));

                    if ($thread_id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID thread tidak valid']);
                    if (empty($isi))
                        return response()->json(['status' => 'error', 'message' => 'Balasan tidak boleh kosong']);

                    $cekThread = DB::connection('mysql_apps')->selectOne("SELECT id FROM diskusi_threads WHERE id = ? AND mapel_id = ? AND kelas_id = ?", [$thread_id, $mapel_id, $kelas_id]);
                    if (!$cekThread)
                        return response()->json(['status' => 'error', 'message' => 'Thread tidak ditemukan']);

                    $media_path = null;
                    if ($request->hasFile('media') && $request->file('media')->isValid()) {
                        $file = $request->file('media');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
                        $fileMime = $file->getMimeType();

                        
                        if (!in_array($ext, $allowedExts) || !in_array($fileMime, $allowedMimes)) {
                            return response()->json([
                                'status'  => 'error',
                                'message' => 'Format file tidak diizinkan. Hanya gambar (JPG, PNG, WEBP, GIF) yang dapat dilampirkan pada balasan diskusi.'
                            ], 422);
                        }

                        $newName = uniqid('IMG_') . '.' . $ext;
                        if (!file_exists(public_path('lms/uploads/diskusi'))) {
                            mkdir(public_path('lms/uploads/diskusi'), 0755, true);
                        }
                        $file->move(public_path('lms/uploads/diskusi'), $newName);
                        $media_path = 'uploads/diskusi/' . $newName;
                    }

                    $waktu_sekarang = date('Y-m-d H:i:s');
                    $sql = "INSERT INTO diskusi_replies (thread_id, isi_balasan, role_pembuat, siswa_id, media_path, created_at) VALUES (?, ?, 'siswa', ?, ?, ?)";
                    DB::connection('mysql_apps')->insert($sql, [$thread_id, $isi, $siswa_id, $media_path, $waktu_sekarang]);
                    $lastId = DB::connection('mysql_apps')->getPdo()->lastInsertId();

                    $sqlNew = "SELECT r.*, s.nama as nama_siswa_db, s.nis as nis_siswa FROM diskusi_replies r LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON r.siswa_id = s.user_id WHERE r.id = ?";
                    $newReplyInfo = DB::connection('mysql_apps')->selectOne($sqlNew, [$lastId]);

                    if ($newReplyInfo) {
                        $newReply = (array) $newReplyInfo;
                        $newReply['nama_penulis'] = $newReply['nama_siswa_db'] ?? $siswa_nama;
                        $newReply['avatar'] = $this->getConsistentAvatarUrl($newReply['nama_penulis'], $user->profile_photo);
                        $newReply['nis_siswa'] = $newReply['nis_siswa'] ?? $siswa_nis;
                        $newReply['waktu_relatif'] = 'Baru saja';
                        $newReply['is_me'] = true;
                        $newReply['is_guru'] = false;
                        return response()->json(['status' => 'success', 'message' => 'Balasan berhasil dikirim', 'data' => $newReply]);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Balasan berhasil dikirim (Reload diperlukan)']);

                
                case 'edit_thread':
                    $id = (int) $request->input('id', 0);
                    $isi = trim($request->input('isi_konten', ''));

                    if ($id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID tidak valid']);
                    if (empty($isi))
                        return response()->json(['status' => 'error', 'message' => 'Konten tidak boleh kosong']);

                    $cek = DB::connection('mysql_apps')->selectOne("SELECT id FROM diskusi_threads WHERE id = ? AND siswa_id = ? AND mapel_id = ? AND kelas_id = ?", [$id, $siswa_id, $mapel_id, $kelas_id]);
                    if (!$cek)
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin mengedit postingan ini']);

                    DB::connection('mysql_apps')->update("UPDATE diskusi_threads SET isi_konten = ? WHERE id = ?", [$isi, $id]);
                    return response()->json(['status' => 'success', 'message' => 'Postingan berhasil diperbarui']);

                
                case 'edit_reply':
                    $id = (int) $request->input('id', 0);
                    $isi = trim($request->input('isi_balasan', ''));

                    if ($id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID tidak valid']);
                    if (empty($isi))
                        return response()->json(['status' => 'error', 'message' => 'Balasan tidak boleh kosong']);

                    $cek = DB::connection('mysql_apps')->selectOne("SELECT r.id FROM diskusi_replies r INNER JOIN diskusi_threads t ON r.thread_id = t.id WHERE r.id = ? AND r.siswa_id = ? AND r.role_pembuat = 'siswa' AND t.mapel_id = ? AND t.kelas_id = ?", [$id, $siswa_id, $mapel_id, $kelas_id]);
                    if (!$cek)
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin mengedit balasan ini']);

                    DB::connection('mysql_apps')->update("UPDATE diskusi_replies SET isi_balasan = ? WHERE id = ?", [$isi, $id]);
                    return response()->json(['status' => 'success', 'message' => 'Balasan berhasil diperbarui']);

                
                case 'delete_thread':
                    $id = (int) $request->input('id', 0);
                    if ($id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID tidak valid']);

                    $thread = DB::connection('mysql_apps')->selectOne("SELECT id, media_path FROM diskusi_threads WHERE id = ? AND siswa_id = ? AND mapel_id = ? AND kelas_id = ?", [$id, $siswa_id, $mapel_id, $kelas_id]);
                    if (!$thread)
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin menghapus postingan ini']);

                    if (!empty($thread->media_path) && file_exists(public_path('lms/' . $thread->media_path))) {
                        unlink(public_path('lms/' . $thread->media_path));
                    }

                    DB::connection('mysql_apps')->delete("DELETE FROM diskusi_threads WHERE id = ?", [$id]);
                    return response()->json(['status' => 'success', 'message' => 'Postingan berhasil dihapus']);

                
                case 'delete_reply':
                    $id = (int) $request->input('id', 0);
                    if ($id === 0)
                        return response()->json(['status' => 'error', 'message' => 'ID tidak valid']);

                    $reply = DB::connection('mysql_apps')->selectOne("SELECT r.id, r.media_path FROM diskusi_replies r INNER JOIN diskusi_threads t ON r.thread_id = t.id WHERE r.id = ? AND r.siswa_id = ? AND r.role_pembuat = 'siswa' AND t.mapel_id = ? AND t.kelas_id = ?", [$id, $siswa_id, $mapel_id, $kelas_id]);
                    if (!$reply)
                        return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin menghapus balasan ini']);

                    if (!empty($reply->media_path) && file_exists(public_path('lms/' . $reply->media_path))) {
                        unlink(public_path('lms/' . $reply->media_path));
                    }

                    DB::connection('mysql_apps')->delete("DELETE FROM diskusi_replies WHERE id = ?", [$id]);
                    return response()->json(['status' => 'success', 'message' => 'Balasan berhasil dihapus']);

                
                case 'check_updates':
                    $last_thread_id = (int) $request->input('last_thread_id', 0);
                    $active_thread_id = (int) $request->input('active_thread_id', 0);
                    $last_reply_id = (int) $request->input('last_reply_id', 0);

                    $response = ['status' => 'success', 'new_threads' => [], 'new_replies' => []];

                    if ($last_thread_id > 0) {
                        $sql = "
                            SELECT t.*, 
                                u.nama_lengkap as user_full_name,
                                u.profile_photo as user_photo,
                                s.nama as nama_siswa_db,
                                s.nis as nis_siswa, 
                                (SELECT COUNT(*) FROM diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan 
                            FROM diskusi_threads t 
                            LEFT JOIN " . config('database.connections.mysql_auth.database') . ".users u ON t.siswa_id = u.id
                            LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON u.id = s.user_id
                            WHERE t.mapel_id = ? AND t.kelas_id = ? AND t.status = 'aktif' AND t.id > ? AND t.siswa_id != ? 
                            ORDER BY t.created_at ASC
                        ";
                        $newThreadsInfo = DB::connection('mysql_apps')->select($sql, [$mapel_id, $kelas_id, $last_thread_id, $siswa_id]);
                        $newThreads = array_map(function ($t) {
                            return (array) $t; }, $newThreadsInfo);

                        foreach ($newThreads as &$t) {
                            if ($t['role_pembuat'] === 'guru') {
                                $t['nama_penulis'] = $t['user_full_name'] ?? $guru_nama;
                                $t['is_guru'] = true;
                            } else {
                                $namaSiswa = $t['nama_siswa_db'] ?? $t['user_full_name'] ?? 'Siswa';
                                $nisSiswa  = $t['nis_siswa'] ?? '-';
                                $t['nama_penulis'] = $namaSiswa;
                                $t['nis_siswa'] = $nisSiswa;
                                $t['is_guru'] = false;
                            }

                            
                            $photoToUse = $t['user_photo'];
                            if ($t['role_pembuat'] === 'guru' && !$photoToUse) {
                                $photoToUse = $guru_photo;
                            }

                            if ($photoToUse && Storage::disk('public')->exists('profile_photos/' . $photoToUse)) {
                                $t['avatar'] = asset('storage/profile_photos/' . $photoToUse) . '?v=' . time();
                            } else {
                                $t['avatar'] = "https://api.dicebear.com/9.x/fun-emoji/svg?seed=" . urlencode($t['nama_penulis']);
                            }

                            $t['waktu_relatif'] = $this->formatWaktuRelatif($t['created_at']);
                            $t['is_me'] = false;
                        }
                        $response['new_threads'] = $newThreads;
                    }

                    if ($active_thread_id > 0 && $last_reply_id > 0) {
                        $sql = "
                            SELECT r.*, 
                                u.nama_lengkap as user_full_name,
                                u.profile_photo as user_photo,
                                s.nama as nama_siswa_db,
                                s.nis as nis_siswa 
                            FROM diskusi_replies r 
                            LEFT JOIN " . config('database.connections.mysql_auth.database') . ".users u ON r.siswa_id = u.id
                            LEFT JOIN " . config('database.connections.mysql_auth.database') . ".siswa s ON u.id = s.user_id
                            WHERE r.thread_id = ? AND r.id > ? AND r.siswa_id != ? 
                            ORDER BY r.created_at ASC
                        ";
                        $newRepliesInfo = DB::connection('mysql_apps')->select($sql, [$active_thread_id, $last_reply_id, $siswa_id]);
                        $newReplies = array_map(function ($r) {
                            return (array) $r; }, $newRepliesInfo);

                        foreach ($newReplies as &$r) {
                            if ($r['role_pembuat'] === 'guru') {
                                $r['nama_penulis'] = $r['user_full_name'] ?? $guru_nama;
                                $r['is_guru'] = true;
                            } else {
                                
                                $namaSiswa = $r['nama_siswa_db'] ?? $r['user_full_name'] ?? 'Siswa';
                                $nisSiswa  = $r['nis_siswa'] ?? '-';
                                $r['nama_penulis'] = $namaSiswa;
                                $r['nis_siswa'] = $nisSiswa;
                                $r['is_guru'] = false;
                            }

                            
                            $photoToUse = $r['user_photo'];
                            if ($r['role_pembuat'] === 'guru' && !$photoToUse) {
                                $photoToUse = $guru_photo;
                            }

                            if ($photoToUse && Storage::disk('public')->exists('profile_photos/' . $photoToUse)) {
                                $r['avatar'] = asset('storage/profile_photos/' . $photoToUse);
                            } else {
                                $r['avatar'] = "https://api.dicebear.com/9.x/fun-emoji/svg?seed=" . urlencode($r['nama_penulis']);
                            }

                            $r['waktu_relatif'] = $this->formatWaktuRelatif($r['created_at']);
                            $r['is_me'] = false;
                        }
                        $response['new_replies'] = $newReplies;
                    }

                    return response()->json($response);

                default:
                    return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid']);
            }
        } catch (\Exception $e) {
            \Log::error('Diskusi API Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan pada sistem. Silakan coba lagi.']);
        }
    }

    private function formatWaktuRelatif($datetime)
    {
        if (empty($datetime)) {
            return '-';
        }
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 5) {
            return 'Baru saja';
        } elseif ($diff < 60) {
            return floor($diff) . 'd';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . 'm';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . 'j';
        } else {
            if (date('Y', $time) === date('Y')) {
                return date('d M', $time);
            } else {
                return date('d M Y', $time);
            }
        }
    }

    private function getConsistentAvatarUrl($name, $uploadedPhoto = null)
    {
        if ($uploadedPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists('profile_photos/' . $uploadedPhoto)) {
            return asset('storage/profile_photos/' . $uploadedPhoto) . '?v=' . time();
        }

        $colors = [
            '1abc9c', '2ecc71', '3498db', '9b59b6', '34495e',
            '16a085', '27ae60', '2980b9', '8e44ad', '2c3e50',
            'f1c40f', 'e67e22', 'e74c3c', '95a5a6', 'f39c12',
            'd35400', 'c0392b', '7f8c8d', '5c6bc0', 'ec407a'
        ];
        $hash = 0;
        $len = strlen($name);
        for ($i = 0; $i < $len; $i++) {
            $hash = ($hash + ord($name[$i])) % 100000;
        }
        $index = $hash % count($colors);
        $bg = $colors[$index];
        return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=" . $bg . "&color=fff&size=128&bold=true";
    }
}
