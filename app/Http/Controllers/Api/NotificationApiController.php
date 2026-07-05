<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;







class NotificationApiController extends Controller
{
    




    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa || !$siswa->kelas_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data siswa tidak ditemukan atau kelas belum diatur.',
            ], 404);
        }

        $kelasId = $siswa->kelas_id;
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

            
            $merged = array_merge((array) $tugas, (array) $diskusi, (array) $materi, (array) $kompetensi);
            usort($merged, function ($a, $b) {
                return strtotime($b->created_at) - strtotime($a->created_at);
            });
            $merged = array_slice($merged, 0, 20);

            
            foreach ($merged as $item) {
                
                $isUnread = strtotime($item->created_at) > strtotime('-1 day');
                
                $text = 'Pemberitahuan';
                if ($item->tipe == 'tugas') $text = 'Tugas baru diberikan';
                elseif ($item->tipe == 'diskusi') $text = 'Topik diskusi baru';
                elseif ($item->tipe == 'materi') $text = 'Materi baru ditambahkan';
                elseif ($item->tipe == 'kompetensi') $text = 'Ujian / Evaluasi baru';

                $notifikasi[] = [
                    'id' => $item->id,
                    'type' => $item->tipe,
                    'title' => $item->judul,
                    'body' => $text . ($item->nama_mapel ? " pada mapel $item->nama_mapel" : ''),
                    'created_at' => date('d M Y, H:i', strtotime($item->created_at)),
                    'is_read' => $isUnread ? 0 : 1, 
                    'mapel_id' => $item->mapel_id
                ];
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $notifikasi
        ]);
    }

    


    public function markAsRead(Request $request, $id): JsonResponse
    {
        
        
        
        return response()->json([
            'status'  => 'success',
            'message' => 'Notifikasi berhasil ditandai telah dibaca.'
        ]);
    }

    


    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa || !$siswa->kelas_id) {
            return response()->json(['status' => 'success', 'count' => 0]);
        }

        $kelasId = $siswa->kelas_id;
        $oneDayAgo = date('Y-m-d H:i:s', strtotime('-1 day'));
        $count = 0;

        try {
            $tugas = DB::connection('mysql_apps')->table('tugas')
                ->where('kelas_id', $kelasId)->where('status', 'aktif')
                ->where('created_at', '>', $oneDayAgo)->count();

            $diskusi = DB::connection('mysql_apps')->table('diskusi_threads')
                ->where('kelas_id', $kelasId)->where('status', 'aktif')
                ->where('created_at', '>', $oneDayAgo)->count();

            $materi = DB::connection('mysql_apps')->table('rpp_materi')
                ->where('kelas_id', $kelasId)
                ->where('created_at', '>', $oneDayAgo)->count();

            $kompetensi = \App\Models\RuangKompetensi::where('status', 'aktif')
                ->where('created_at', '>', $oneDayAgo)->count();

            $count = $tugas + $diskusi + $materi + $kompetensi;
        } catch (\Exception $e) {
            
        }

        return response()->json([
            'status' => 'success',
            'count'  => $count
        ]);
    }
}
