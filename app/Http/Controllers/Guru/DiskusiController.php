<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiskusiController extends Controller
{
    public function index()
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');
        $namaMapel = session('nama_mapel');

        $guruInfo = DB::connection('mysql_auth')->table('users')
            ->where('id', auth()->id())
            ->first();
        $guruPhoto = $guruInfo ? $guruInfo->profile_photo : null;

        $threads = DB::connection('mysql_apps')->table('diskusi_threads as t')
            ->leftJoin(config('database.connections.mysql_auth.database') . '.siswa as s', 't.siswa_id', '=', 's.user_id')
            ->leftJoin(config('database.connections.mysql_auth.database') . '.users as u', 't.siswa_id', '=', 'u.id')
            ->select('t.*', 's.nama as nama_siswa', 's.nis as nis_siswa', 'u.profile_photo as user_photo')
            ->where('t.mapel_id', $mapelId)
            ->where('t.kelas_id', $kelasId)
            ->where('t.status', 'aktif')
            ->orderBy('t.is_pinned', 'desc')
            ->orderBy('t.created_at', 'desc')
            ->get();

        foreach ($threads as $t) {
            $t->replies = DB::connection('mysql_apps')->table('diskusi_replies as r')
                ->leftJoin(config('database.connections.mysql_auth.database') . '.siswa as s', 'r.siswa_id', '=', 's.user_id')
                ->leftJoin(config('database.connections.mysql_auth.database') . '.users as u', 'r.siswa_id', '=', 'u.id')
                ->select('r.*', 's.nama as nama_siswa', 's.nis as nis_siswa', 'u.profile_photo as user_photo')
                ->where('r.thread_id', $t->id)
                ->orderBy('r.created_at', 'asc')
                ->get();
        }

        return view('guru.diskusi.index', compact('threads', 'namaMapel', 'guruPhoto'));
    }

    public function arsip()
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');
        $namaMapel = session('nama_mapel');

        $archives = DB::connection('mysql_apps')->table('diskusi_threads as t')
            ->leftJoin(config('database.connections.mysql_auth.database') . '.siswa as s', 't.siswa_id', '=', 's.user_id')
            ->leftJoin(config('database.connections.mysql_auth.database') . '.users as u', 't.siswa_id', '=', 'u.id')
            ->select('t.*', 's.nama as nama_siswa', 's.nis as nis_siswa', 'u.profile_photo as user_photo', DB::raw('(SELECT COUNT(*) FROM ' . config('database.connections.mysql_apps.database') . '.diskusi_replies r WHERE r.thread_id = t.id) as jumlah_balasan'))
            ->where('t.mapel_id', $mapelId)
            ->where('t.kelas_id', $kelasId)
            ->where('t.status', '!=', 'aktif')
            ->orderBy('t.created_at', 'desc')
            ->get();

        return view('guru.diskusi.arsip', compact('archives', 'namaMapel'));
    }

    public function store(Request $request)
    {
        $isi = trim($request->input('isi_konten'));
        $isPinned = $request->has('is_pinned') ? 1 : 0;

        $mediaPath = null;
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lms/uploads/diskusi'), $filename);
            $mediaPath = 'uploads/diskusi/' . $filename;
        } elseif ($request->filled('google_image_url')) {
            $mediaPath = $request->input('google_image_url');
        }

        if (!empty($isi) || $mediaPath) {
            
            $judul = !empty($isi) ? \Illuminate\Support\Str::limit($isi, 80, '') : 'Lampiran Media';

            DB::connection('mysql_apps')->table('diskusi_threads')->insert([
                'mapel_id' => session('mapel_id'),
                'kelas_id' => session('kelas_id'),
                'judul' => $judul,
                'role_pembuat' => 'guru',
                'isi_konten' => $isi,
                'media_path' => $mediaPath,
                'status' => 'aktif',
                'is_pinned' => $isPinned,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Topik diskusi berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $isi = trim($request->input('isi_konten'));
        $judul = !empty($isi) ? \Illuminate\Support\Str::limit($isi, 80, '') : 'Topik Diskusi';

        $thread = DB::connection('mysql_apps')->table('diskusi_threads')
            ->where('id', $id)
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->first();

        if (!$thread) {
            return back()->with('error', 'Topik tidak ditemukan.');
        }

        $mediaPath = $thread->media_path;

        if ($request->input('remove_media') == '1') {
            if ($mediaPath && file_exists(public_path('lms/' . $mediaPath))) {
                @unlink(public_path('lms/' . $mediaPath));
            }
            $mediaPath = null;
        }

        if ($request->hasFile('media')) {
            if ($mediaPath && file_exists(public_path('lms/' . $mediaPath))) {
                @unlink(public_path('lms/' . $mediaPath));
            }
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('lms/uploads/diskusi'), $filename);
            $mediaPath = 'uploads/diskusi/' . $filename;
        } elseif ($request->filled('google_image_url')) {
            if ($mediaPath && $mediaPath != $request->input('google_image_url') && file_exists(public_path('lms/' . $mediaPath))) {
                @unlink(public_path('lms/' . $mediaPath));
            }
            $mediaPath = $request->input('google_image_url');
        }

        if (!empty($isi) || $mediaPath) {
            if (empty($judul)) $judul = 'Lampiran Media';
            
            DB::connection('mysql_apps')->table('diskusi_threads')
                ->where('id', $id)
                ->update([
                    'judul' => $judul,
                    'isi_konten' => $isi,
                    'media_path' => $mediaPath,
                ]);
        }

        return back()->with('success', 'Topik diskusi berhasil diperbarui.');
    }

    public function reply(Request $request, $threadId)
    {
        $isi = trim($request->input('isi_balasan'));

        if (!empty($isi)) {
            DB::connection('mysql_apps')->table('diskusi_replies')->insert([
                'thread_id' => $threadId,
                'role_pembuat' => 'guru',
                'isi_balasan' => $isi,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function togglePin(Request $request, $id)
    {
        $currentPin = $request->input('current_pin');
        $newPin = $currentPin == 1 ? 0 : 1;

        DB::connection('mysql_apps')->table('diskusi_threads')
            ->where('id', $id)
            ->update(['is_pinned' => $newPin]);

        $msg = $newPin ? 'Topik disematkan.' : 'Semat dilepas.';
        return back()->with('success', $msg);
    }

    public function toggleStatus(Request $request, $id)
    {
        $currentStatus = $request->input('current_status');
        $newStatus = $currentStatus === 'aktif' ? 'draft' : 'aktif';

        DB::connection('mysql_apps')->table('diskusi_threads')
            ->where('id', $id)
            ->update(['status' => $newStatus]);

        $msg = $newStatus === 'aktif' ? 'Topik dipulihkan.' : 'Topik diarsipkan.';
        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        
        $thread = DB::connection('mysql_apps')->table('diskusi_threads')->where('id', $id)->first();
        if ($thread && $thread->media_path) {
            $path = public_path('lms/' . $thread->media_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        DB::connection('mysql_apps')->table('diskusi_replies')->where('thread_id', $id)->delete();
        DB::connection('mysql_apps')->table('diskusi_threads')->where('id', $id)->delete();

        return back()->with('success', 'Topik beserta balasan dihapus permanen.');
    }

    public function destroyReply($id)
    {
        $reply = DB::connection('mysql_apps')->table('diskusi_replies')->where('id', $id)->first();
        if ($reply && $reply->media_path) {
            $path = public_path('lms/' . $reply->media_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        DB::connection('mysql_apps')->table('diskusi_replies')->where('id', $id)->delete();

        return back()->with('success', 'Komentar/balasan berhasil dihapus.');
    }

    public function downloadMedia(Request $request)
    {
        $fileId = $request->input('file_id');
        $fileName = $request->input('file_name');

        $tokenManager = new \App\Integrations\Google\GoogleTokenManager();
        $accessToken = $tokenManager->getValidAccessToken(auth()->user()->id);

        if (!$accessToken) {
            return response()->json(['status' => 'error', 'message' => 'Token tidak ditemukan. Sambungkan akun Google Anda di Profil terlebih dahulu.']);
        }

        $url = "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media";
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}"
            ])->timeout(30)->get($url);

            $httpCode = $response->status();
            $fileContent = $response->body();
        } catch (\Exception $e) {
            $httpCode = 500;
            $fileContent = null;
        }

        if ($httpCode === 200 && !empty($fileContent)) {
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '', $fileName);
            $destination = public_path('lms/uploads/diskusi/' . $filename);

            if (!file_exists(public_path('lms/uploads/diskusi'))) {
                mkdir(public_path('lms/uploads/diskusi'), 0755, true);
            }

            file_put_contents($destination, $fileContent);
            return response()->json([
                'status' => 'success',
                'file_name' => $fileName,
                'media_path' => 'uploads/diskusi/' . $filename,
                'url' => asset('lms/uploads/diskusi/' . $filename)
            ]);
        }

        $errorMsg = 'Gagal mengunduh file dari Google Drive. (HTTP ' . $httpCode . ')';
        if ($httpCode === 401)
            $errorMsg = 'Sesi Google Drive berakhir. Silakan sambungkan ulang di Profil.';

        return response()->json(['status' => 'error', 'message' => $errorMsg]);
    }

    public static function formatWaktuRelatif($datetime)
    {
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
}

