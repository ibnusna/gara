<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    


    public function create()
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        
        $maxPertemuan = DB::connection('mysql_apps')->table('absensi')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->max('pertemuan_ke');

        $nextPertemuan = ($maxPertemuan !== null) ? $maxPertemuan + 1 : 1;

        
        $siswaList = DB::connection('mysql_auth')->table('siswa')
            ->where('kelas_id', $kelasId)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nis', 'nama']);

        
        $rppMateri = DB::connection('mysql_apps')->table('rpp_materi')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->orderBy('bab', 'asc')
            ->orderBy('bagian', 'asc')
            ->get(['bab', 'judul_materi']);

        return view('guru.absensi.create', compact(
            'nextPertemuan',
            'siswaList',
            'rppMateri'
        ))->with(['active_menu' => 'absensi', 'use_datatables' => false]);
    }

    


    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pertemuan_ke' => 'required|integer|min:1',
            'pokok_bahasan' => 'required|string|max:1000',
            'status' => 'required|array',
            'status.*' => 'in:H,S,I,A,T',
        ]);

        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');
        $tanggal = $request->tanggal;

        DB::transaction(function () use ($request, $mapelId, $kelasId, $tanggal) {
            
            $absensiId = DB::connection('mysql_apps')->table('absensi')->insertGetId([
                'mapel_id' => $mapelId,
                'kelas_id' => $kelasId,
                'tanggal' => $tanggal,
                'pertemuan_ke' => $request->pertemuan_ke,
                'pokok_bahasan' => $request->pokok_bahasan,
                'metode' => $request->metode ?? 'Tatap Muka',
                'created_at' => now(),
            ]);

            
            foreach ($request->status as $siswaId => $status) {
                DB::connection('mysql_apps')->table('absensi_detail')->insert([
                    'absensi_id' => $absensiId,
                    'siswa_id' => (int) $siswaId,
                    'status' => $status,
                    'created_at' => now(),
                ]);
            }

            
            $tidakHadirIds = collect($request->status)
                ->filter(fn($s) => $s !== 'H')
                ->keys()
                ->values()
                ->toArray();

            DB::connection('mysql_apps')->table('agenda_harian')->insert([
                'mapel_id' => $mapelId,
                'kelas_id' => $kelasId,
                'tanggal' => $tanggal,
                'jam' => now()->format('H:i:s'),
                'rencana_kegiatan' => $request->pokok_bahasan,
                'catatan_pelaksanaan' => 'Berjalan dengan lancar',
                'siswa_tidak_hadir' => json_encode($tidakHadirIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $bulan = date('n', strtotime($tanggal));
        $tahun = date('Y', strtotime($tanggal));

        return redirect()
            ->route('guru.absensi.rekap', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Absensi Pertemuan ke-' . $request->pertemuan_ke . ' berhasil disimpan. Agenda harian dibuat otomatis.');
    }

    


    public function rekap(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $bulan = $request->query('bulan', date('n'));
        $tahun = $request->query('tahun', date('Y'));

        
        
        $absensiQuery = DB::connection('mysql_apps')->table('absensi')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->whereYear('tanggal', $tahun);
            
        if ($bulan !== 'semua') {
            $absensiQuery->whereMonth('tanggal', $bulan);
        }

        $absensiList = $absensiQuery->orderBy('tanggal', 'desc')
            ->orderBy('pertemuan_ke', 'desc')
            ->get(['id', 'tanggal', 'pertemuan_ke', 'pokok_bahasan']);

        
        $siswaList = DB::connection('mysql_auth')->table('siswa')
            ->where('kelas_id', $kelasId)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nis', 'nama']);

        
        $pivotQuery = DB::connection('mysql_apps')->table('absensi_detail as d')
            ->join(config('database.connections.mysql_apps.database').'.absensi as a', 'a.id', '=', 'd.absensi_id')
            ->where('a.mapel_id', $mapelId)
            ->where('a.kelas_id', $kelasId)
            ->whereYear('a.tanggal', $tahun);
            
        if ($bulan !== 'semua') {
            $pivotQuery->whereMonth('a.tanggal', $bulan);
        }

        $pivotRaw = $pivotQuery->select('d.siswa_id', 'a.pertemuan_ke', 'd.status')
            ->get();

        
        $pivotMap = [];
        foreach ($pivotRaw as $row) {
            $pivotMap[$row->siswa_id][$row->pertemuan_ke] = $row->status;
        }

        
        $pertemuanCols = $absensiList->pluck('pertemuan_ke')->sortDesc()->values();
        return view('guru.absensi.rekap', compact(
            'absensiList',
            'siswaList',
            'pivotMap',
            'pertemuanCols',
            'bulan',
            'tahun'
        ))->with(['active_menu' => 'rekap_absensi', 'use_datatables' => true]);
    }

    


    public function getDetail(int $id)
    {
        $kelasId = session('kelas_id');

        $absensi = DB::connection('mysql_apps')->table('absensi')->where('id', $id)->first();

        if (!$absensi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $details = DB::connection('mysql_apps')->table('absensi_detail')
            ->where('absensi_id', $id)
            ->get(['siswa_id', 'status']);

        return response()->json([
            'info' => $absensi,
            'students' => $details,
        ]);
    }

    


    public function update(Request $request, int $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pertemuan_ke' => 'required|integer|min:1',
            'pokok_bahasan' => 'required|string|max:1000',
            'status' => 'required|array',
            'status.*' => 'in:H,S,I,A,T',
        ]);

        DB::transaction(function () use ($request, $id) {
            
            DB::connection('mysql_apps')->table('absensi')
                ->where('id', $id)
                ->update([
                    'tanggal' => $request->tanggal,
                    'pertemuan_ke' => $request->pertemuan_ke,
                    'pokok_bahasan' => $request->pokok_bahasan,
                ]);

            
            DB::connection('mysql_apps')->table('absensi_detail')
                ->where('absensi_id', $id)
                ->delete();

            foreach ($request->status as $siswaId => $status) {
                DB::connection('mysql_apps')->table('absensi_detail')->insert([
                    'absensi_id' => $id,
                    'siswa_id' => (int) $siswaId,
                    'status' => $status,
                    'created_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Data pertemuan berhasil diperbarui.');
    }

    


    public function destroy(int $id)
    {
        
        $absensi = DB::connection('mysql_apps')->table('absensi')->where('id', $id)->first();
        if ($absensi) {
            DB::connection('mysql_apps')->table('agenda_harian')
                ->where('mapel_id', $absensi->mapel_id)
                ->where('kelas_id', $absensi->kelas_id)
                ->whereDate('tanggal', $absensi->tanggal)
                ->delete();
        }

        DB::connection('mysql_apps')->table('absensi_detail')->where('absensi_id', $id)->delete();
        DB::connection('mysql_apps')->table('absensi')->where('id', $id)->delete();

        return back()->with('success', 'Data absensi pertemuan berhasil dihapus.');
    }

    


    public function quickUpdate(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|integer',
            'pertemuan_ke' => 'required|integer',
            'status' => 'required|in:H,S,I,A',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer'
        ]);

        $absensi = DB::connection('mysql_apps')->table('absensi')
            ->where('mapel_id', session('mapel_id'))
            ->where('kelas_id', session('kelas_id'))
            ->where('pertemuan_ke', $request->pertemuan_ke)
            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->first();

        if (!$absensi) {
            return response()->json(['status' => 'error', 'message' => 'Data pertemuan tidak ditemukan']);
        }

        $updated = DB::connection('mysql_apps')->table('absensi_detail')
            ->where('absensi_id', $absensi->id)
            ->where('siswa_id', $request->siswa_id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);
            
        if ($updated === 0) {
            
            DB::connection('mysql_apps')->table('absensi_detail')->insert([
                'absensi_id' => $absensi->id,
                'siswa_id' => $request->siswa_id,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Status absensi berhasil diubah']);
    }
}
