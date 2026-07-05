<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $totalMateri = DB::connection('mysql_apps')->table('rpp_materi')->where('mapel_id', $mapelId)->where('kelas_id', $kelasId)->count();
        $totalSiswa = DB::connection('mysql_auth')->table('siswa')->where('kelas_id', $kelasId)->count();
        $totalTugas = DB::connection('mysql_apps')->table('tugas')->where('mapel_id', $mapelId)->where('kelas_id', $kelasId)->count();
        $totalDiskusi = DB::connection('mysql_apps')->table('diskusi_threads')->where('mapel_id', $mapelId)->where('kelas_id', $kelasId)->where('status', 'aktif')->count();

        $pengumuman = DB::connection('mysql_apps')->table('pengumuman')->where('mapel_id', $mapelId)->where('kelas_id', $kelasId)->first();

        
        $absensiRaw = DB::connection('mysql_apps')->table('absensi_detail as d')
            ->join(config('database.connections.mysql_apps.database').'.absensi as a', 'd.absensi_id', '=', 'a.id')
            ->where('a.mapel_id', $mapelId)
            ->where('a.kelas_id', $kelasId)
            ->select('d.status', DB::raw('count(d.id) as count'))
            ->groupBy('d.status')
            ->get();

        $absensiStats = [
            'H' => 0,
            'S' => 0,
            'I' => 0,
            'A' => 0
        ];
        foreach ($absensiRaw as $row) {
            if (isset($absensiStats[$row->status])) {
                $absensiStats[$row->status] = $row->count;
            }
        }

        
        $tugasStats = DB::connection('mysql_apps')->table('tugas as t')
            ->leftJoin(config('database.connections.mysql_apps.database').'.nilai_tugas as n', 't.id', '=', 'n.tugas_id')
            ->where('t.mapel_id', $mapelId)
            ->where('t.kelas_id', $kelasId)
            ->select('t.tugas_ke', 't.pokok_bahasan', DB::raw('COALESCE(AVG(n.nilai), 0) as rata_rata'))
            ->groupBy('t.id', 't.tugas_ke', 't.pokok_bahasan')
            ->orderBy('t.tugas_ke', 'asc')
            ->take(10)
            ->get();

        return view('guru.dashboard.index', compact(
            'totalMateri',
            'totalSiswa',
            'totalTugas',
            'totalDiskusi',
            'pengumuman',
            'absensiStats',
            'tugasStats'
        ));
    }

    public function storePengumuman(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi'   => 'required|string',
        ]);

        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        
        
        $mapelExists = DB::connection('mysql_apps')
            ->table('mata_pelajaran')
            ->where('id', $mapelId)
            ->exists();

        if (!$mapelExists) {
            return back()->with('error', 'Mata pelajaran pada sesi aktif tidak ditemukan. Silakan pilih ulang sesi mengajar terlebih dahulu.');
        }

        DB::connection('mysql_apps')->table('pengumuman')->updateOrInsert(
            ['mapel_id' => $mapelId, 'kelas_id' => $kelasId],
            [
                'judul' => $request->judul,
                'isi'   => $request->isi,
            ]
        );

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroyPengumuman()
    {
        DB::connection('mysql_apps')->table('pengumuman')->where('mapel_id', session('mapel_id'))->where('kelas_id', session('kelas_id'))->delete();
        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
