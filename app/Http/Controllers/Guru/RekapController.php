<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    


    public function index(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        if (!$mapelId || !$kelasId) {
            return redirect()->route('guru.pilih-sesi')->with('error', 'Silakan pilih kelas terlebih dahulu.');
        }

        
        $tugasList = DB::connection('mysql_apps')->table('tugas')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->whereNotNull('tugas_ke')
            ->orderBy('tanggal', 'desc')
            ->orderBy('tugas_ke', 'desc')
            ->get();

        
        $siswaList = DB::connection('mysql_auth')->table('siswa')
            ->where('kelas_id', $kelasId)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nis', 'nama']);

        
        $tugasHeaders = DB::connection('mysql_apps')->table('tugas')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->whereNotNull('tugas_ke')
            ->distinct()
            ->orderBy('tugas_ke', 'asc')
            ->pluck('tugas_ke');

        
        $nilaiRaw = DB::connection('mysql_apps')->table('nilai_tugas as nt')
            ->join(config('database.connections.mysql_apps.database').'.tugas as t', 'nt.tugas_id', '=', 't.id')
            ->where('t.mapel_id', $mapelId)
            ->where('t.kelas_id', $kelasId)
            ->get(['nt.siswa_id', 't.tugas_ke', 'nt.nilai']);

        $nilaiMap = [];
        foreach ($nilaiRaw as $row) {
            $nilaiMap[$row->siswa_id][$row->tugas_ke] = $row->nilai;
        }

        return view('guru.rekap.index', compact(
            'tugasList',
            'siswaList',
            'tugasHeaders',
            'nilaiMap'
        ))->with(['active_menu' => 'rekap', 'use_datatables' => true]);
    }

    


    public function katrolPreview(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $threshold = $request->input('threshold');
        $tugasIdFilter = $request->input('tugas_id'); 

        if (!$threshold) {
            return response()->json(['status' => 'error', 'message' => 'Threshold required']);
        }

        $query = DB::connection('mysql_apps')->table('nilai_tugas as nt')
            ->join(config('database.connections.mysql_apps.database').'.tugas as t', 'nt.tugas_id', '=', 't.id')
            ->join(config('database.connections.mysql_auth.database').'.siswa as s', 'nt.siswa_id', '=', 's.id')
            ->where('t.mapel_id', $mapelId)
            ->where('t.kelas_id', $kelasId)
            ->where('nt.nilai', '<', $threshold);

        if ($tugasIdFilter) {
            $query->where('t.id', $tugasIdFilter);
        }

        $query->orderBy('s.nama', 'asc')->orderBy('t.tugas_ke', 'asc');

        $results = $query->get([
            's.nama as nama_siswa',
            's.nis',
            't.tugas_ke',
            't.pokok_bahasan',
            'nt.nilai as nilai_lama'
        ]);

        return response()->json([
            'status' => 'success',
            'count' => $results->count(),
            'data' => $results
        ]);
    }

    


    public function katrolSave(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $threshold = $request->input('threshold');
        $targetNilai = $request->input('target_nilai');
        $tugasIdFilter = $request->input('tugas_id');

        if (!$threshold || !$targetNilai) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        try {
            DB::connection('mysql_apps')->beginTransaction();

            $query = DB::connection('mysql_apps')->table('nilai_tugas as nt')
                ->join(config('database.connections.mysql_apps.database').'.tugas as t', 'nt.tugas_id', '=', 't.id')
                ->where('t.mapel_id', $mapelId)
                ->where('t.kelas_id', $kelasId)
                ->where('nt.nilai', '<', $threshold);

            if ($tugasIdFilter) {
                $query->where('nt.tugas_id', $tugasIdFilter);
            }

            
            
            $affectedValues = $query->pluck('nt.id');

            if ($affectedValues->count() > 0) {
                DB::connection('mysql_apps')->table('nilai_tugas')
                    ->whereIn('id', $affectedValues)
                    ->update([
                        'nilai' => $targetNilai,
                        'updated_at' => now()
                    ]);
            }

            DB::connection('mysql_apps')->commit();

            return response()->json([
                'status' => 'success',
                'message' => $affectedValues->count() . ' nilai siswa berhasil di-katrol menjadi ' . $targetNilai
            ]);
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    


    public function updateTugas(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required|integer',
            'tanggal' => 'required|date',
            'tugas_ke' => 'required|integer',
            'pokok_bahasan' => 'required|string',
        ]);

        try {
            DB::connection('mysql_apps')->beginTransaction();

            DB::connection('mysql_apps')->table('tugas')
                ->where('id', $request->tugas_id)
                ->update([
                    'tanggal' => $request->tanggal,
                    'tugas_ke' => $request->tugas_ke,
                    'pokok_bahasan' => $request->pokok_bahasan,
                    'updated_at' => now()
                ]);

            if ($request->has('nilai') && is_array($request->nilai)) {
                $now = now();
                foreach ($request->nilai as $siswaId => $nilai) {
                    if ($nilai === '' || $nilai === null) {
                        DB::connection('mysql_apps')->table('nilai_tugas')
                            ->where('tugas_id', $request->tugas_id)
                            ->where('siswa_id', $siswaId)
                            ->delete();
                    } else {
                        DB::connection('mysql_apps')->table('nilai_tugas')->updateOrInsert(
                            ['tugas_id' => $request->tugas_id, 'siswa_id' => $siswaId],
                            ['nilai' => $nilai, 'updated_at' => $now]
                        );
                    }
                }
            }

            DB::connection('mysql_apps')->commit();
            return redirect()->route('guru.rekap.index')->with('success', 'Tugas berhasil di-update.');
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    


    public function destroyTugas($id)
    {
        try {
            DB::connection('mysql_apps')->table('tugas')->where('id', $id)->delete();
            return redirect()->route('guru.rekap.index')->with('success', 'Tugas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus tugas: ' . $e->getMessage());
        }
    }

    public function getNilaiSiswa($siswaId)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $tugas = DB::connection('mysql_apps')->table('tugas')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->orderBy('tugas_ke', 'asc')
            ->get(['id', 'tugas_ke', 'pokok_bahasan']);

        $nilai = DB::connection('mysql_apps')->table('nilai_tugas')
            ->where('siswa_id', $siswaId)
            ->get()->keyBy('tugas_id');

        $data = [];
        foreach ($tugas as $t) {
            $data[] = [
                'tugas_id' => $t->id,
                'tugas_ke' => $t->tugas_ke,
                'pokok_bahasan' => $t->pokok_bahasan,
                'nilai' => isset($nilai[$t->id]) ? $nilai[$t->id]->nilai : ''
            ];
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function saveNilaiSiswa(Request $request, $siswaId)
    {
        if (!$request->has('nilai')) {
            return response()->json(['status' => 'success', 'message' => 'Tidak ada perubahan.']);
        }

        DB::connection('mysql_apps')->beginTransaction();
        try {
            foreach ($request->nilai as $tugasId => $nilai) {
                if ($nilai === '' || $nilai === null) {
                    DB::connection('mysql_apps')->table('nilai_tugas')
                        ->where('tugas_id', $tugasId)
                        ->where('siswa_id', $siswaId)
                        ->delete();
                } else {
                    DB::connection('mysql_apps')->table('nilai_tugas')
                        ->updateOrInsert(
                            ['tugas_id' => $tugasId, 'siswa_id' => $siswaId],
                            ['nilai' => $nilai, 'updated_at' => now()]
                        );
                }
            }
            DB::connection('mysql_apps')->commit();
            return response()->json(['status' => 'success', 'message' => 'Nilai siswa berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
