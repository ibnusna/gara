<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KinerjaController extends Controller
{
    public function index()
    {
        return view('kepsek.kinerja.index');
    }

    public function data()
    {
        try {
            
            $gurus = DB::connection('mysql_auth')->table('guru')
                ->select('id', 'nip', 'nama_lengkap')
                ->orderBy('nama_lengkap', 'asc')
                ->get();

            if ($gurus->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            
            $taRows = DB::connection('mysql_apps')->table('teaching_assignments')
                ->select('teacher_id', DB::raw('COUNT(DISTINCT class_id) as jumlah_kelas'))
                ->groupBy('teacher_id')
                ->get();
            $taMap = $taRows->pluck('jumlah_kelas', 'teacher_id')->toArray();

            
            $rppRows = DB::connection('mysql_apps')->table('teaching_assignments as ta')
                ->leftJoin(config('database.connections.mysql_apps.database') . '.rpp_materi as rm', function ($join) {
                    $join->on('rm.mapel_id', '=', 'ta.subject_id')
                        ->on('rm.kelas_id', '=', 'ta.class_id');
                })
                ->select('ta.teacher_id', DB::raw('COUNT(rm.id) as jumlah_materi'))
                ->groupBy('ta.teacher_id')
                ->get();
            $rppMap = $rppRows->pluck('jumlah_materi', 'teacher_id')->toArray();

            
            $tugasRows = DB::connection('mysql_apps')->table('teaching_assignments as ta')
                ->leftJoin(config('database.connections.mysql_apps.database') . '.tugas as t', function ($join) {
                    $join->on('t.mapel_id', '=', 'ta.subject_id')
                        ->on('t.kelas_id', '=', 'ta.class_id');
                })
                ->select('ta.teacher_id', DB::raw('COUNT(t.id) as jumlah_tugas'))
                ->groupBy('ta.teacher_id')
                ->get();
            $tugasMap = $tugasRows->pluck('jumlah_tugas', 'teacher_id')->toArray();

            
            $forumRows = DB::connection('mysql_apps')->table('teaching_assignments as ta')
                ->leftJoin(config('database.connections.mysql_apps.database') . '.diskusi_threads as dt', function ($join) {
                    $join->on('dt.mapel_id', '=', 'ta.subject_id')
                        ->on('dt.kelas_id', '=', 'ta.class_id')
                        ->where('dt.role_pembuat', '=', 'guru');
                })
                ->select('ta.teacher_id', DB::raw('COUNT(dt.id) as jumlah_thread'))
                ->groupBy('ta.teacher_id')
                ->get();
            $forumMap = $forumRows->pluck('jumlah_thread', 'teacher_id')->toArray();

            
            $soalRows = DB::connection('asesmen_gara')->table('bank_soal')
                ->select(
                    'id_guru',
                    DB::raw("SUM(CASE WHEN status_soal = 'VALIDATED' THEN 1 ELSE 0 END) as jumlah_validated"),
                    DB::raw("SUM(CASE WHEN status_soal = 'SUBMITTED' THEN 1 ELSE 0 END) as jumlah_submitted"),
                    DB::raw("COUNT(*) as total_soal")
                )
                ->groupBy('id_guru')
                ->get();

            $soalMap = [];
            foreach ($soalRows as $s) {
                $soalMap[$s->id_guru] = [
                    'validated' => (int) $s->jumlah_validated,
                    'submitted' => (int) $s->jumlah_submitted,
                    'total' => (int) $s->total_soal,
                ];
            }

            
            $result = [];
            foreach ($gurus as $g) {
                $guruId = $g->id;
                $result[] = [
                    'guru_id' => $guruId,
                    'nip' => $g->nip ?: '-',
                    'nama_lengkap' => $g->nama_lengkap,
                    'jumlah_kelas' => (int) ($taMap[$guruId] ?? 0),
                    'jumlah_materi' => (int) ($rppMap[$guruId] ?? 0),
                    'jumlah_tugas' => (int) ($tugasMap[$guruId] ?? 0),
                    'jumlah_forum' => (int) ($forumMap[$guruId] ?? 0),
                    'soal_validated' => $soalMap[$guruId]['validated'] ?? 0,
                    'soal_submitted' => $soalMap[$guruId]['submitted'] ?? 0,
                    'soal_total' => $soalMap[$guruId]['total'] ?? 0,
                ];
            }

            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
