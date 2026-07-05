<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;











class NotificationController extends Controller
{
    



    public function stream(Request $request)
    {
        
        return response('SSE Disabled', 204);
    }

    



    private function fetchRecentNotifications(int $kelasId, ?int $mapelId, int $siswaId): array
    {
        $notifications = [];
        $since = now()->subHours(24)->toDateTimeString();

        try {
            
            $tugasBaru = DB::connection('mysql_apps')
                ->table('tugas')
                ->where('kelas_id', $kelasId)
                ->when($mapelId, fn($q) => $q->where('mapel_id', $mapelId))
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['id', 'judul', 'created_at']);

            foreach ($tugasBaru as $t) {
                $notifications[] = [
                    'type'    => 'tugas',
                    'icon'    => '<i class="fas fa-clipboard-list text-primary"></i>',
                    'title'   => 'Tugas Baru',
                    'message' => $t->judul,
                    'time'    => $t->created_at,
                ];
            }

            
            $materiBaru = DB::connection('mysql_apps')
                ->table('rpp_materi')
                ->where('kelas_id', $kelasId)
                ->when($mapelId, fn($q) => $q->where('mapel_id', $mapelId))
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get(['id', 'judul_materi', 'created_at']);

            foreach ($materiBaru as $m) {
                $notifications[] = [
                    'type'    => 'materi',
                    'icon'    => '<i class="fas fa-book-reader text-success"></i>',
                    'title'   => 'Materi Baru',
                    'message' => $m->judul_materi,
                    'time'    => $m->created_at,
                ];
            }

            
            $pengumuman = DB::connection('mysql_apps')
                ->table('pengumuman')
                ->where(function ($q) use ($kelasId, $mapelId) {
                    $q->where('kelas_id', $kelasId)
                      ->orWhereNull('kelas_id');
                })
                ->where('created_at', '>=', $since)
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get(['id', 'judul', 'created_at']);

            foreach ($pengumuman as $p) {
                $notifications[] = [
                    'type'    => 'pengumuman',
                    'icon'    => '<i class="fas fa-bullhorn text-warning"></i>',
                    'title'   => 'Pengumuman',
                    'message' => $p->judul,
                    'time'    => $p->created_at,
                ];
            }

        } catch (\Exception $e) {
            
            
            \Log::warning('SSE Notification fetch error: ' . $e->getMessage());
        }

        return $notifications;
    }
}
