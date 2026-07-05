<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PilihMapelController extends Controller
{
    


    public function index()
    {
        $user = auth()->user();

        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();

        if (!$siswa) {
            abort(403, 'Profil Siswa tidak ditemukan.');
        }

        $nama_siswa = $siswa->nama;
        $kelas_id = $siswa->kelas_id;

        
        $kelas = DB::connection('mysql_auth')->table('kelas')->where('id', $kelas_id)->first();
        $kelas_siswa = $kelas ? $kelas->nama_kelas : 'Belum Ada Kelas';

        
        session(['nama_kelas' => $kelas_siswa]);
        session(['nama' => $nama_siswa]);
        session(['kelas_id' => $kelas_id]);
        session(['siswa_id' => $siswa->id]);

        
        $gateQuery = DB::connection('asesmen_gara')->table('asesmen_config')->first();
        $gateSiswaOpen = $gateQuery && (int) $gateQuery->status_pintu_siswa === 1;

        
        $setting = DB::connection('mysql_apps')->table('app_settings')->where('setting_key', 'school_name')->first();
        $sekolah_nama = $setting ? $setting->setting_value : 'Garuda Akademi';

        
        try {
            $mapel_list = DB::connection('mysql_apps')->table('class_subjects as cs')
                ->join(DB::raw(config('database.connections.mysql_auth.database').'.mata_pelajaran as m'), 'm.id', '=', 'cs.subject_id')
                ->where('cs.class_id', $kelas_id)
                ->orderBy('m.nama_mapel', 'asc')
                ->select('m.*')
                ->get();
        } catch (\Exception $e) {
            $mapel_list = collect([]);
        }

        $jadwalList = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
            ->join('master_jam_pelajaran as mjp', 'jp.jam_pelajaran_id', '=', 'mjp.id')
            ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
            ->where('ta.class_id', $kelas_id)
            ->select('ta.subject_id as mapel_id', 'mjp.hari', 'mjp.jam_mulai', 'mjp.jam_selesai', 'mjp.urutan_jam')
            ->get();
            
        $jadwalRawMap = [];
        foreach ($jadwalList as $j) {
            $jadwalRawMap[$j->mapel_id][$j->hari][] = $j;
        }

        $jadwalMap = [];
        foreach ($jadwalRawMap as $mapelId => $haris) {
            foreach ($haris as $hari => $slots) {
                usort($slots, function($a, $b) { return strcmp($a->jam_mulai, $b->jam_mulai); });
                $blocks = [];
                $currentBlock = null;
                foreach ($slots as $slot) {
                    if (!$currentBlock) {
                        $currentBlock = ['hari' => $hari, 'start' => $slot->jam_mulai, 'end' => $slot->jam_selesai];
                    } else {
                        $end = strtotime($currentBlock['end']);
                        $nextStart = strtotime($slot->jam_mulai);
                        if (($nextStart - $end) <= (45 * 60)) {
                            $currentBlock['end'] = $slot->jam_selesai;
                        } else {
                            $blocks[] = $currentBlock;
                            $currentBlock = ['hari' => $hari, 'start' => $slot->jam_mulai, 'end' => $slot->jam_selesai];
                        }
                    }
                }
                if ($currentBlock) $blocks[] = $currentBlock;

                foreach ($blocks as $b) {
                    $jadwalMap[$mapelId][] = (object) [
                        'hari' => $b['hari'],
                        'jam_mulai' => $b['start'],
                        'jam_selesai' => $b['end']
                    ];
                }
            }
        }

        date_default_timezone_set('Asia/Jakarta');
        $daysMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $hariIni = $daysMap[date('l')];
        $waktuSekarang = date('H:i:s');

        return view('siswa.auth.pilih_mapel', compact(
            'nama_siswa',
            'kelas_siswa',
            'sekolah_nama',
            'gateSiswaOpen',
            'mapel_list',
            'jadwalMap',
            'hariIni',
            'waktuSekarang'
        ));
    }

    


    public function setMapel(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required|integer'
        ]);

        $mapel_id = $request->mapel_id;
        $user = auth()->user();
        $siswa = DB::connection('mysql_auth')->table('siswa')->where('user_id', $user->id)->first();

        
        $mapel = DB::connection('mysql_auth')->table('mata_pelajaran')->where('id', $mapel_id)->first();

        if ($mapel && $siswa) {
            $kelas = DB::connection('mysql_auth')->table('kelas')->where('id', $siswa->kelas_id)->first();

            session([
                'mapel_id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel,
                'kelas_id' => $siswa->kelas_id,
                'siswa_id' => $siswa->id,
                'nama' => $siswa->nama,
                'nama_kelas' => $kelas ? $kelas->nama_kelas : 'Belum Ada Kelas'
            ]);

            return redirect()->route('student.dashboard');
        }

        return redirect()->back()->with('error', 'Mata Pelajaran atau Siswa tidak ditemukan.');
    }
}
