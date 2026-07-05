<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    


    public function index()
    {
        

        
        

        $page_title = 'Dashboard Siswa | GARA';

        
        $additional_css = [
            'css/root.css',
            'css/dashboard_v2.css'
        ];

        
        date_default_timezone_set('Asia/Jakarta'); 
        $jam = date('H');
        $sapaan = "Selamat Datang";

        if ($jam >= 4 && $jam < 11) {
            $sapaan = "Selamat Pagi";
        } elseif ($jam >= 11 && $jam < 15) {
            $sapaan = "Selamat Siang";
        } elseif ($jam >= 15 && $jam < 18) {
            $sapaan = "Selamat Sore";
        } else {
            $sapaan = "Selamat Malam";
        }

        
        $quotes = [
            "Pendidikan adalah senjata paling ampuh untuk mengubah dunia.",
            "Jangan pernah berhenti belajar, karena hidup tak pernah berhenti mengajarkan.",
            "Masa depan adalah milik mereka yang menyiapkannya hari ini.",
            "Ilmu tanpa amal bagaikan pohon tanpa buah.",
            "Barang siapa bersungguh-sungguh, maka dia akan mendapatkannya (Man Jadda Wajada).",
            "Kegagalan adalah guru terbaikmu. Belajarlah darinya.",
            "Satu-satunya cara untuk melakukan pekerjaan hebat adalah dengan mencintai apa yang kamu lakukan."
        ];

        
        $quote_harian = $quotes[array_rand($quotes)];

        
        $mapel_id = session('mapel_id');
        $kelas_id = session('kelas_id');
        $guru = null;

        if ($mapel_id && $kelas_id) {
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
        }

        // Ambil pengaturan sekolah dari app_settings
        $settings     = DB::connection('mysql_apps')->table('app_settings')->pluck('setting_value', 'setting_key');
        $sekolah_nama = $settings['sekolah_nama'] ?? 'Garuda Akademi';
        $tahun_ajaran = $settings['tahun_ajaran'] ?? '2024/2025';

        return view('siswa.dashboard.index', compact('page_title', 'additional_css', 'sapaan', 'quote_harian', 'guru', 'sekolah_nama', 'tahun_ajaran'));
    }

    



    public function apiPengumuman()
    {
        $user = auth()->user();

        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();

        
        if (!$siswa) {
            return response()->json(['found' => false, 'message' => 'Siswa not found']);
        }

        $kelasId = $siswa->kelas_id;

        
        
        $mapelId = session('mapel_id');

        $query = DB::connection('mysql_apps')->table('pengumuman')
            ->where(function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)
                    ->orWhere('kelas_id', 0);
            });

        
        if ($mapelId) {
            $query->where(function ($q) use ($mapelId) {
                $q->where('mapel_id', $mapelId)
                  ->orWhere('mapel_id', 0);
            });
        }

        $pengumuman = $query->orderBy('updated_at', 'desc')->first();

        if ($pengumuman) {
            return response()->json([
                'found' => true,
                'data' => [
                    'judul' => $pengumuman->judul,
                    'isi' => $pengumuman->isi
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }
}
