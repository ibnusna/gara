<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MateriController extends Controller
{
    


    public function index(Request $request)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('student.pilih-mapel')->with('error', 'Silakan pilih mata pelajaran terlebih dahulu.');
        }

        
        
        $sql = "SELECT 
                    r1.bab, 
                    r1.semester,
                    COUNT(r1.id) as total_topik,
                    (SELECT judul_materi 
                     FROM rpp_materi r2 
                     WHERE r2.mapel_id = r1.mapel_id 
                       AND r2.kelas_id = r1.kelas_id 
                       AND r2.bab = r1.bab 
                     ORDER BY bagian ASC LIMIT 1) as nama_bab_otomatis
                FROM rpp_materi r1
                WHERE r1.mapel_id = ? AND r1.kelas_id = ?
                GROUP BY r1.bab, r1.semester, r1.mapel_id, r1.kelas_id
                ORDER BY r1.bab ASC";

        try {
            $list_bab = DB::connection('mysql_apps')->select($sql, [$mapel_id, $kelas_id]);
        } catch (\Exception $e) {
            $list_bab = [];
        }

        
        $formatNomorBab = function ($nomor) {
            return str_pad(intval($nomor), 2, '0', STR_PAD_LEFT);
        };

        
        $guru = $this->getGuruInfo($mapel_id, $kelas_id);

        return view('siswa.materi.index', compact('list_bab', 'formatNomorBab', 'guru'));
    }

    


    public function bab(Request $request, $bab)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('student.pilih-mapel')->with('error', 'Silakan pilih mata pelajaran terlebih dahulu.');
        }

        if (empty($bab)) {
            return redirect()->route('student.materi.index');
        }

        $bab_ke = intval($bab);

        try {
            $materi_list = DB::connection('mysql_apps')->table('rpp_materi')
                ->where('mapel_id', $mapel_id)
                ->where('kelas_id', $kelas_id)
                ->where('bab', $bab_ke)
                ->orderBy('bagian', 'asc')
                ->get();
        } catch (\Exception $e) {
            $materi_list = [];
        }

        
        $judul_bab = count($materi_list) > 0 ? $materi_list[0]->judul_materi : 'Materi Bab ' . $bab_ke;

        $formatNomorBab = function ($nomor) {
            return str_pad(intval($nomor), 2, '0', STR_PAD_LEFT);
        };

        
        $isMateriCompleted = function ($id_materi) {
            if (session()->has('completed_materi')) {
                return in_array($id_materi, session('completed_materi'));
            }
            return false;
        };

        
        $guru = $this->getGuruInfo($mapel_id, $kelas_id);

        return view('siswa.materi.bab', compact('materi_list', 'bab_ke', 'judul_bab', 'formatNomorBab', 'isMateriCompleted', 'guru'));
    }

    


    public function detail(Request $request, $id)
    {
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');

        if (!$mapel_id || !$kelas_id) {
            return redirect()->route('student.pilih-mapel');
        }

        $materi = (array) DB::connection('mysql_apps')->table('rpp_materi')
            ->where('id', $id)
            ->where('mapel_id', $mapel_id)
            ->where('kelas_id', $kelas_id)
            ->first();

        if (empty($materi)) {
            return redirect()->route('student.materi.index')->with('error', 'Materi tidak ditemukan atau Anda tidak memiliki akses.');
        }

        
        $this->markMateriAsCompleted($materi['id_materi'] ?? $materi['id']);

        
        $formatNomorBab = function ($nomor) {
            return str_pad(intval($nomor), 2, '0', STR_PAD_LEFT);
        };

        $youtube_id = $this->getYoutubeEmbedId($materi['link_youtube']);
        $has_video = !empty($youtube_id);

        $isGoogleDriveLink = function ($url) {
            return (strpos($url, 'drive.google.com') !== false || strpos($url, 'docs.google.com') !== false);
        };

        
        $guru = $this->getGuruInfo($mapel_id, $kelas_id);

        return view('siswa.materi.detail', compact(
            'materi',
            'youtube_id',
            'has_video',
            'formatNomorBab',
            'isGoogleDriveLink',
            'guru'
        ));
    }

    

    private function getYoutubeEmbedId($url)
    {
        if (is_null($url))
            return null;
        $url = trim($url);
        if (empty($url))
            return null;

        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        if (preg_match($pattern, $url, $match)) {
            return $match[1];
        }
        return null;
    }

    private function getGuruInfo($mapel_id, $kelas_id)
    {
        $guru = DB::connection('mysql_apps')->table('teaching_assignments as ta')
            ->join(config('database.connections.mysql_auth.database').'.users as u', 'ta.teacher_id', '=', 'u.id')
            ->where('ta.class_id', $kelas_id)
            ->where('ta.subject_id', $mapel_id)
            ->select('u.nama_lengkap', 'u.profile_photo', 'u.id as user_id')
            ->first();

        if ($guru) {
            $photo = $guru->profile_photo;
            if ($photo && \Illuminate\Support\Facades\Storage::disk('public')->exists('profile_photos/' . $photo)) {
                $guru->profile_photo_url = asset('storage/profile_photos/' . $photo);
            } else {
                $guru->profile_photo_url = "https://api.dicebear.com/9.x/fun-emoji/svg?seed=" . urlencode($guru->nama_lengkap);
            }
        }
        return $guru;
    }

    private function markMateriAsCompleted($id_materi)
    {
        $completed = session('completed_materi', []);
        if (!in_array($id_materi, $completed)) {
            $completed[] = $id_materi;
            session(['completed_materi' => $completed]);
        }
    }
}
