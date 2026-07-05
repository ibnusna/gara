<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TugasController extends Controller
{
    



    public function create(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        if (!$mapelId || !$kelasId) {
            return redirect()->route('guru.pilih-sesi')->with('error', 'Silakan pilih sesi kelas dan mata pelajaran terlebih dahulu.');
        }

        
        $namaMapel = DB::connection('mysql_auth')->table('mata_pelajaran')->where('id', $mapelId)->value('nama_mapel');
        $namaKelas = DB::connection('mysql_auth')->table('kelas')->where('id', $kelasId)->value('nama_kelas');

        
        $maxTugas = DB::connection('mysql_apps')->table('tugas')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->max('tugas_ke');

        $nextTugasKe = $maxTugas ? $maxTugas + 1 : 1;

        
        $siswaList = DB::connection('mysql_auth')->table('siswa')
            ->where('kelas_id', $kelasId)
            ->orderBy('nama', 'asc')
            ->get(['id', 'nis', 'nama']);

        return view('guru.tugas.create', compact(
            'nextTugasKe',
            'siswaList',
            'namaMapel',
            'namaKelas'
        ));
    }

    



    public function getRppData(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        $rppData = DB::connection('mysql_apps')->table('rpp_materi')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->orderBy('bab', 'asc')
            ->get(['id', 'bab', 'judul_materi']);

        return response()->json([
            'status' => 'success',
            'data' => $rppData
        ]);
    }

    



    public function store(Request $request)
    {
        $mapelId = session('mapel_id');
        $kelasId = session('kelas_id');

        if (!$mapelId || !$kelasId) {
            return redirect()->route('guru.pilih-sesi')->with('error', 'Sesi tidak valid.');
        }

        $request->validate([
            'tugas_ke' => 'required|integer',
            'tanggal' => 'required|date',
            'tipe_tugas' => 'required|string',
            'kategori_asesmen' => 'required|string',
            'teknik_penilaian' => 'required|string',
            'pokok_bahasan' => 'required|string',
            'nilai' => 'array'
        ]);

        try {
            DB::connection('mysql_apps')->beginTransaction();

            
            
            $existingTugas = DB::connection('mysql_apps')->table('tugas')
                ->where('mapel_id', $mapelId)
                ->where('kelas_id', $kelasId)
                ->where('tugas_ke', $request->tugas_ke)
                ->first();

            if ($existingTugas) {
                $tugasId = $existingTugas->id;
                
                DB::connection('mysql_apps')->table('tugas')
                    ->where('id', $tugasId)
                    ->update([
                        'tanggal' => $request->tanggal,
                        'pokok_bahasan' => $request->pokok_bahasan,
                        'judul' => $request->pokok_bahasan,
                        'rpp_id' => $request->rpp_id ?: null,
                        'tipe_tugas' => $request->tipe_tugas,
                        'kategori_asesmen' => $request->kategori_asesmen,
                        'teknik_penilaian' => $request->teknik_penilaian,
                        'updated_at' => now()
                    ]);
            } else {
                
                $tugasId = DB::connection('mysql_apps')->table('tugas')->insertGetId([
                    'mapel_id' => $mapelId,
                    'kelas_id' => $kelasId,
                    'tugas_ke' => $request->tugas_ke,
                    'tanggal' => $request->tanggal,
                    'pokok_bahasan' => $request->pokok_bahasan,
                    'judul' => $request->pokok_bahasan,
                    'rpp_id' => $request->rpp_id ?: null,
                    'tipe_tugas' => $request->tipe_tugas,
                    'kategori_asesmen' => $request->kategori_asesmen,
                    'teknik_penilaian' => $request->teknik_penilaian,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            
            if ($request->has('nilai') && is_array($request->nilai)) {
                $nilaiData = [];
                $now = now();
                foreach ($request->nilai as $siswaId => $nilaiStr) {
                    if ($nilaiStr === '' || $nilaiStr === null)
                        continue;

                    $nilaiNum = (float) $nilaiStr;
                    
                    

                    
                    DB::connection('mysql_apps')->table('nilai_tugas')
                        ->where('tugas_id', $tugasId)
                        ->where('siswa_id', $siswaId)
                        ->delete();

                    $nilaiData[] = [
                        'tugas_id' => $tugasId,
                        'siswa_id' => $siswaId,
                        'nilai' => $nilaiNum,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }

                if (!empty($nilaiData)) {
                    DB::connection('mysql_apps')->table('nilai_tugas')->insert($nilaiData);
                }
            }

            DB::connection('mysql_apps')->commit();

            return redirect()->route('guru.tugas.create')->with('success', 'Data Tugas dan Nilai Berhasil Disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_apps')->rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }
}
