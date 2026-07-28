<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;







class MobileDataController extends Controller
{
    




    public function getMapel(Request $request): JsonResponse
    {
        $user = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $authDb = config('database.connections.mysql_auth.database');
        
        $mapelList = DB::connection('mysql_apps')->table('class_subjects as cs')
            ->join("{$authDb}.mata_pelajaran as m", 'cs.subject_id', '=', 'm.id')
            ->where('cs.class_id', $siswa->kelas_id ?? $siswa->class_id)
            ->select('m.id', 'm.nama_mapel')
            ->orderBy('m.nama_mapel', 'asc')
            ->get();

        $kelas_id = $siswa->kelas_id ?? $siswa->class_id;

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
                    $jadwalMap[$mapelId][] = [
                        'hari' => $b['hari'],
                        'jam_mulai' => $b['start'],
                        'jam_selesai' => $b['end']
                    ];
                }
            }
        }

        $formatted = $mapelList->map(function($item) use ($jadwalMap) {
            return [
                'id'         => $item->id,
                'nama_mapel' => $item->nama_mapel,
                'jadwal'     => $jadwalMap[$item->id] ?? []
            ];
        });

        
        $gateUjianOpen = DB::connection('asesmen_gara')->table('asesmen_config')
            ->value('status_pintu_siswa') == 1;

        
        $sekolahNama = DB::connection('mysql_apps')->table('app_settings')
            ->where('setting_key', 'sekolah_nama')
            ->value('setting_value') ?? 'Garuda Akademi';

        return response()->json([
          'status'          => 'success',
          'nama_siswa'      => $siswa->nama,
          'kelas_siswa'     => $this->getNamaKelas($siswa->kelas_id ?? $siswa->class_id),
          'sekolah_nama'    => $sekolahNama,
          'gate_ujian_open' => $gateUjianOpen,
          'mapel_list'      => $formatted,
        ]);
    }

    




    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Profil tidak tersedia.'], 404);
        }

        
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'nama'          => $siswa->nama,
                'nis'           => $siswa->nis,
                'kelas'         => $this->getNamaKelas($siswa->kelas_id ?? $siswa->class_id),
                'poin'          => 0, 
                'absensi_persen'=> 100,
                'tugas_pending' => 0,
                'foto'          => $user->profile_photo_url,
            ]
        ]);
    }

    


    private function getNamaKelas($kelasId)
    {
        if (!$kelasId) return '-';
        return DB::connection('mysql_auth')->table('kelas')
            ->where('id', $kelasId)
            ->value('nama_kelas') ?? '-';
    }

    







    public function getAccountDetail(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        
        $isDefaultPassword = \Illuminate\Support\Facades\Hash::check(
            $siswa->nis ?? $user->username,
            $user->password
        );

        
        $settings = DB::connection('mysql_apps')->table('app_settings')
            ->whereIn('setting_key', ['sekolah_nama', 'tahun_ajaran', 'semester'])
            ->pluck('setting_value', 'setting_key');

        $namaSekolah = $settings['sekolah_nama'] ?? 'Garuda Akademi';
        $tahunAjaran = $settings['tahun_ajaran'] ?? '';
        $semester    = $settings['semester']     ?? '';

        return response()->json([
            'status' => 'success',
            'data'   => [
                'nama'                => $siswa->nama,
                'nis'                 => $siswa->nis ?? '',
                'kelas'               => $this->getNamaKelas($siswa->kelas_id ?? $siswa->class_id),
                'is_default_password' => $isDefaultPassword,
                'nama_sekolah'        => $namaSekolah,
                'tahun_ajaran'        => $tahunAjaran,
                'semester'            => $semester,
            ],
        ]);
    }

    public function getPengumuman(Request $request): JsonResponse
    {
        $user = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $kelasId = $siswa->kelas_id ?? $siswa->class_id;

        $pengumuman = DB::connection('mysql_apps')->table('pengumuman')
            ->where(function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId)
                    ->orWhere('kelas_id', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $pengumuman->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'judul'      => $item->judul,
                    'isi'        => $item->isi,
                    'updated_at' => $item->updated_at ?? null,
                ];
            }),
        ]);
    }
}
