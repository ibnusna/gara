<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TentangSayaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();

        $nama_siswa = session('nama', $siswa->nama ?? 'Siswa');
        $nis_siswa  = session('nis', $siswa->nis ?? '-');
        $nama_kelas = session('nama_kelas', '-');

        
        $settings = DB::connection('mysql_apps')->table('app_settings')->pluck('setting_value', 'setting_key');

        $nama_sekolah = $settings['sekolah_nama'] ?? 'Garuda Akademi';
        $tahun_ajaran = $settings['tahun_ajaran'] ?? '2023/2024';
        $semester     = $settings['semester_aktif'] ?? '1';
        $semester     = ($semester == '1') ? 'Ganjil' : 'Genap';

        
        $is_default_password = Hash::check('siswa123', $user->getAuthPassword());

        return view('siswa.tentangsaya.index', compact(
            'nama_siswa', 'nis_siswa', 'nama_kelas',
            'nama_sekolah', 'tahun_ajaran', 'semester',
            'is_default_password'
        ));
    }
}
