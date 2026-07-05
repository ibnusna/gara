<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('kepsek.dashboard.index');
    }

    public function stats()
    {
        try {
            
            $totalGuru  = DB::connection('mysql_auth')->table('guru')->count();
            $totalSiswa = DB::connection('mysql_auth')->table('siswa')->count();

            
            $totalPendingSoal = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('status_soal', 'SUBMITTED')
                ->count();

            
            $asesmenConfig = DB::connection('asesmen_gara')->table('asesmen_config')->first();
            if (!$asesmenConfig) {
                $asesmenConfig = [
                    'status_pintu'       => 0,
                    'status_pintu_siswa' => 0,
                    'jenis_asesmen'      => 'ASTS',
                    'grade_lock'         => 0,
                    'grade_lock_date'    => null,
                ];
            } else {
                $asesmenConfig = (array) $asesmenConfig;
            }

            
            $rekapChart = $this->buildActivityChart();

            
            $ujianHariIni = DB::connection('asesmen_gara')->table('jadwal_ujian')
                ->where('tanggal_ujian', DB::raw('CURDATE()'))
                ->count();

            
            
            $guruIds    = DB::connection('mysql_auth')->table('guru')->pluck('id');
            $rppMap     = DB::connection('mysql_apps')->table('rpp_materi')
                ->select(DB::raw('COUNT(*) as cnt, mapel_id'))
                ->groupBy('mapel_id')
                ->pluck('cnt', 'mapel_id');

            $soalValid  = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('status_soal', 'VALIDATED')
                ->pluck('id_guru')
                ->toArray();

            $assignMap = DB::connection('mysql_apps')->table('teaching_assignments')
                ->pluck('subject_id', 'teacher_id');

            $lengkap = 0; $kurang = 0;
            foreach ($guruIds as $gid) {
                $hasRpp   = $assignMap->has($gid) && isset($rppMap[$assignMap[$gid]]);
                $hasSoal  = in_array($gid, $soalValid);
                ($hasRpp && $hasSoal) ? $lengkap++ : $kurang++;
            }

            return response()->json([
                'success'        => true,
                'total_guru'     => $totalGuru,
                'total_siswa'    => $totalSiswa,
                'pending_soal'   => $totalPendingSoal,
                'ujian_hari_ini' => $ujianHariIni,
                'asesmen_config' => $asesmenConfig,
                'rekap_chart'    => $rekapChart,
                'perangkat_pie'  => ['lengkap' => $lengkap, 'kurang' => $kurang],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    







    private function buildActivityChart(): array
    {
        
        $recentRows = DB::connection('mysql_apps')
            ->table('rekap_harian')
            ->where('tanggal', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 6 DAY)'))
            ->select('tanggal', DB::raw('SUM(jumlah_aksi) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($recentRows->count() > 0) {
            return $this->fillDateRange(now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d'), $recentRows, 7);
        }

        
        $month30Rows = DB::connection('mysql_apps')
            ->table('rekap_harian')
            ->where('tanggal', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 29 DAY)'))
            ->select('tanggal', DB::raw('SUM(jumlah_aksi) as total'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($month30Rows->count() > 0) {
            return $this->fillDateRange(now()->subDays(29)->format('Y-m-d'), now()->format('Y-m-d'), $month30Rows, 30);
        }

        
        $latestDate = DB::connection('mysql_apps')->table('rekap_harian')->max('tanggal');

        if ($latestDate) {
            $startDate = date('Y-m-d', strtotime($latestDate . ' -6 days'));
            $fallbackRows = DB::connection('mysql_apps')
                ->table('rekap_harian')
                ->where('tanggal', '>=', $startDate)
                ->where('tanggal', '<=', $latestDate)
                ->select('tanggal', DB::raw('SUM(jumlah_aksi) as total'))
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();

            $result = $this->fillDateRange($startDate, $latestDate, $fallbackRows, 7);
            $result['note'] = 'Data per ' . date('d M Y', strtotime($latestDate)) . ' (data terbaru tersedia)';
            return $result;
        }

        
        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = date('d M', strtotime("-{$i} days"));
            $values[] = 0;
        }
        return ['labels' => $labels, 'values' => $values, 'note' => 'Belum ada data aktivitas'];
    }

    


    private function fillDateRange(string $startDate, string $endDate, $rows, int $maxPoints): array
    {
        $map = [];
        foreach ($rows as $r) {
            $map[$r->tanggal] = (int) $r->total;
        }

        $labels = [];
        $values = [];
        $current = $startDate;
        $count = 0;

        while ($current <= $endDate && $count < $maxPoints) {
            $labels[] = date('d M', strtotime($current));
            $values[] = $map[$current] ?? 0;
            $current  = date('Y-m-d', strtotime($current . ' +1 day'));
            $count++;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
