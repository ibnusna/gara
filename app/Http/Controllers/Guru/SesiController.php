<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SesiController extends Controller
{
    public function index()
    {
        $guruUser = \DB::connection('mysql_auth')->table('guru')->where('user_id', auth()->id())->first();
        $guruId = $guruUser ? $guruUser->id : 0;
        $guruName = $guruUser ? $guruUser->nama_lengkap : 'Guru';

        $daftarSesi = \DB::connection('mysql_apps')->table('teaching_assignments as ta')
            ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'ta.subject_id', '=', 'm.id')
            ->join(config('database.connections.mysql_auth.database').'.kelas as k', 'ta.class_id', '=', 'k.id')
            ->where('ta.teacher_id', $guruId)
            ->select('ta.subject_id as mapel_id', 'ta.class_id as kelas_id', 'm.nama_mapel', 'k.nama_kelas')
            ->orderBy('k.nama_kelas', 'asc')
            ->orderBy('m.nama_mapel', 'asc')
            ->get()
            ->map(function ($row) {
                $icon = 'fa-book';
                $bg = 'icon-primary';

                if (stripos($row->nama_mapel, 'IPA') !== false) {
                    $icon = 'fa-flask';
                    $bg = 'icon-success';
                } elseif (stripos($row->nama_mapel, 'Informatika') !== false) {
                    $icon = 'fa-laptop-code';
                    $bg = 'icon-info';
                } elseif (stripos($row->nama_mapel, 'Matematika') !== false) {
                    $icon = 'fa-calculator';
                    $bg = 'icon-warning';
                }

                return (object) [
                    'mapel_id' => $row->mapel_id,
                    'kelas_id' => $row->kelas_id,
                    'judul' => $row->nama_mapel . ' - ' . $row->nama_kelas,
                    'subjudul' => 'Kelas ' . $row->nama_kelas,
                    'icon' => $icon,
                    'bg' => $bg,
                ];
            });

        $jadwalList = \DB::connection('mysql_auth')->table('jadwal_pelajaran as jp')
            ->join('master_jam_pelajaran as mjp', 'jp.jam_pelajaran_id', '=', 'mjp.id')
            ->join(config('database.connections.mysql_apps.database').'.teaching_assignments as ta', 'jp.teaching_assignment_id', '=', 'ta.id')
            ->where('ta.teacher_id', $guruId)
            ->select('ta.subject_id as mapel_id', 'ta.class_id as kelas_id', 'mjp.hari', 'mjp.jam_mulai', 'mjp.jam_selesai', 'mjp.urutan_jam')
            ->get();
            
        $jadwalRawMap = [];
        foreach ($jadwalList as $j) {
            $jadwalRawMap[$j->kelas_id][$j->mapel_id][$j->hari][] = $j;
        }

        $jadwalMap = [];
        foreach ($jadwalRawMap as $kelasId => $mapels) {
            foreach ($mapels as $mapelId => $haris) {
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
                        $jadwalMap[$kelasId][$mapelId][] = (object) [
                            'hari' => $b['hari'],
                            'jam_mulai' => $b['start'],
                            'jam_selesai' => $b['end']
                        ];
                    }
                }
            }
        }

        date_default_timezone_set('Asia/Jakarta');
        $daysMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $hariIni = $daysMap[date('l')];
        $waktuSekarang = date('H:i:s');

        return view('guru.sesi.index', compact('daftarSesi', 'guruName', 'jadwalMap', 'hariIni', 'waktuSekarang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required|integer',
            'kelas_id' => 'required|integer',
        ]);

        $mapel = \DB::connection('mysql_auth')->table('mata_pelajaran')->where('id', $request->mapel_id)->first();
        $kelas = \DB::connection('mysql_auth')->table('kelas')->where('id', $request->kelas_id)->first();

        if ($mapel && $kelas) {
            session([
                'mapel_id' => $request->mapel_id,
                'kelas_id' => $request->kelas_id,
                'nama_mapel' => $mapel->nama_mapel,
                'nama_kelas' => $kelas->nama_kelas,
            ]);

            return redirect()->route('guru.dashboard')->with('success', 'Sesi berhasil diatur ke ' . $mapel->nama_mapel . ' - ' . $kelas->nama_kelas);
        }

        return back()->with('error', 'Sesi tidak valid.');
    }
}
