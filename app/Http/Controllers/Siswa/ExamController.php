<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;






class ExamController extends Controller
{
    



    public function login()
    {
        
        try {
            $gateQuery = DB::connection('mysql_asesmen')
                ->selectOne("SELECT status_pintu_siswa FROM asesmen_config LIMIT 1");
            if (!$gateQuery || (int) $gateQuery->status_pintu_siswa !== 1) {
                abort(404);
            }
        } catch (\Exception $e) {
            abort(404);
        }

        
        $nisPreFill = '';
        if (auth()->check()) {
            try {
                $user = DB::connection('mysql_auth')
                    ->selectOne("SELECT nis FROM siswa WHERE user_id = ? LIMIT 1", [auth()->id()]);
                if ($user)
                    $nisPreFill = $user->nis;
            } catch (\Exception $e) {
                
            }
        }

        
        $sekolahNama = '';
        try {
            $setting = DB::connection('mysql_apps')
                ->selectOne("SELECT setting_value FROM app_settings WHERE setting_key = 'sekolah_nama' LIMIT 1");
            if ($setting)
                $sekolahNama = $setting->setting_value;
        } catch (\Exception $e) {
            
        }

        return view('exam.login', compact('nisPreFill', 'sekolahNama'));
    }

    



    public function summary()
    {
        return view('exam.summary');
    }

    



    public function ujian()
    {
        return view('exam.ujian');
    }

    



    public function hasil()
    {
        $config = [];
        try {
            $rows = DB::connection('mysql_apps')
                ->select(
                    "SELECT setting_key, setting_value FROM app_settings
                     WHERE setting_key IN ('sekolah_nama','sekolah_akreditasi','sekolah_nss','sekolah_npsn','sekolah_alamat','tahun_ajaran')"
                );
            foreach ($rows as $row) {
                $config[$row->setting_key] = $row->setting_value;
            }
        } catch (\Exception $e) {
            
        }

        $sekolahNama = $config['sekolah_nama'] ?? '';
        $sekolahAkreditasi = $config['sekolah_akreditasi'] ?? '';
        $sekolahNss = $config['sekolah_nss'] ?? '';
        $sekolahNpsn = $config['sekolah_npsn'] ?? '';
        $sekolahAlamat = $config['sekolah_alamat'] ?? '';

        return view('exam.hasil', compact(
            'sekolahNama',
            'sekolahAkreditasi',
            'sekolahNss',
            'sekolahNpsn',
            'sekolahAlamat'
        ));
    }
}
