<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
    public function index()
    {
        $kelasId = session('kelas_id');

        if (!$kelasId) {
            return redirect()->route('student.pilih-mapel');
        }

        $notifikasi = [];

        try {
            
            $tugas = DB::connection('mysql_apps')->table('tugas')
                ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'tugas.mapel_id', '=', 'm.id')
                ->select('tugas.id', 'tugas.judul', 'tugas.created_at', DB::raw("'tugas' as tipe"), 'm.nama_mapel', 'tugas.mapel_id')
                ->where('tugas.kelas_id', $kelasId)
                ->where('tugas.status', 'aktif')
                ->orderBy('tugas.created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            
            $diskusi = DB::connection('mysql_apps')->table('diskusi_threads')
                ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'diskusi_threads.mapel_id', '=', 'm.id')
                ->select('diskusi_threads.id', 'diskusi_threads.judul', 'diskusi_threads.created_at', DB::raw("'diskusi' as tipe"), 'm.nama_mapel', 'diskusi_threads.mapel_id')
                ->where('diskusi_threads.kelas_id', $kelasId)
                ->where('diskusi_threads.status', 'aktif')
                ->orderBy('diskusi_threads.created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            
            $materi = DB::connection('mysql_apps')->table('rpp_materi')
                ->join(config('database.connections.mysql_auth.database').'.mata_pelajaran as m', 'rpp_materi.mapel_id', '=', 'm.id')
                ->select('rpp_materi.id', 'rpp_materi.judul_materi as judul', 'rpp_materi.created_at', DB::raw("'materi' as tipe"), 'm.nama_mapel', 'rpp_materi.mapel_id')
                ->where('rpp_materi.kelas_id', $kelasId)
                ->orderBy('rpp_materi.created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            
            $kompetensi = \App\Models\RuangKompetensi::where('status', 'aktif')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return (object) [
                        'id' => $item->id,
                        'judul' => $item->judul,
                        'created_at' => $item->created_at,
                        'tipe' => 'kompetensi',
                        'nama_mapel' => 'Ujian & Kompetensi',
                        'mapel_id' => null
                    ];
                })
                ->toArray();

            
            $notifikasi = array_merge((array) $tugas, (array) $diskusi, (array) $materi, (array) $kompetensi);

            usort($notifikasi, function ($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            });

            
            $notifikasi = array_slice($notifikasi, 0, 20);

        } catch (\Exception $e) {
            
        }

        return view('siswa.notifikasi.index', compact('notifikasi'));
    }

    public function redirect($type, $id, Request $request)
    {
        $mapelId = $request->query('mapel_id');

        
        if ($mapelId) {
            $mapel = DB::connection('mysql_auth')->table('mata_pelajaran')->where('id', $mapelId)->first();
            if ($mapel) {
                session([
                    'mapel_id' => $mapel->id,
                    'nama_mapel' => $mapel->nama_mapel,
                ]);
            }
        }

        
        switch ($type) {
            case 'tugas':
                return redirect()->route('student.tugas.index');
            case 'diskusi':
                return redirect()->route('student.diskusi.index');
            case 'materi':
                return redirect()->route('student.materi.detail', ['id' => $id]);
            case 'kompetensi':
                return redirect()->route('student.ruang_kompetensi.ujian', ['id' => $id]);
            default:
                return redirect()->route('student.dashboard');
        }
    }
}
