<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    public function index()
    {
        return view('kepsek.ujian.index');
    }

    public function data()
    {
        try {
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');

            
            
            $jadwals = DB::connection('asesmen_gara')
                ->table('jadwal_ujian')
                ->select(
                    'id_jadwal',
                    'mapel',
                    'kelas',
                    'jenis_asesmen',
                    'tanggal_ujian',
                    'jam_mulai',
                    'jam_selesai',
                    'durasi',
                    'mode_submit'
                )
                ->where('tanggal_ujian', '>=', DB::raw("DATE_SUB(CURDATE(), INTERVAL 90 DAY)"))
                ->orderBy('tanggal_ujian', 'desc')
                ->orderBy('jam_mulai', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($j) use ($today, $now) {
                    $j = (array) $j;
                    $start = $j['tanggal_ujian'] . ' ' . $j['jam_mulai'];
                    $end = $j['tanggal_ujian'] . ' ' . $j['jam_selesai'];

                    if ($now >= $start && $now <= $end) {
                        $j['status_ujian'] = 'berlangsung';
                    } elseif ($j['tanggal_ujian'] < $today) {
                        $j['status_ujian'] = 'selesai';
                    } elseif ($now > $end && $j['tanggal_ujian'] === $today) {
                        $j['status_ujian'] = 'selesai_hari_ini';
                    } else {
                        $j['status_ujian'] = 'mendatang';
                    }
                    return $j;
                })
                ->toArray();

            
            $distribusi = DB::connection('asesmen_gara')
                ->table('hasil_ujian as h')
                ->join('jadwal_ujian as j', 'h.id_jadwal', '=', 'j.id_jadwal')
                ->select(
                    'j.mapel',
                    'j.kelas',
                    'j.jenis_asesmen',
                    DB::raw('COUNT(h.id_hasil) as jumlah_peserta'),
                    DB::raw('ROUND(AVG(h.skor_akhir), 1) as rata_rata'),
                    DB::raw('MAX(h.skor_akhir) as tertinggi'),
                    DB::raw('MIN(h.skor_akhir) as terendah')
                )
                ->groupBy('j.mapel', 'j.kelas', 'j.jenis_asesmen')
                ->orderBy('rata_rata', 'desc')
                ->get();

            
            $lockStatus = DB::connection('asesmen_gara')
                ->table('asesmen_config')
                ->select('grade_lock', 'grade_lock_date', 'jenis_asesmen', 'status_pintu', 'status_pintu_siswa')
                ->first();

            if (!$lockStatus) {
                $lockStatus = [
                    'grade_lock' => 0,
                    'grade_lock_date' => null,
                    'jenis_asesmen' => 'ASTS',
                    'status_pintu' => 0,
                    'status_pintu_siswa' => 0,
                ];
            } else {
                $lockStatus = (array) $lockStatus;
            }

            
            $totalJadwal = DB::connection('asesmen_gara')->table('jadwal_ujian')->count();
            $totalPeserta = DB::connection('asesmen_gara')->table('hasil_ujian')->count();
            $rataGlobal = DB::connection('asesmen_gara')->table('hasil_ujian')->avg('skor_akhir');
            $ujianHariIni = DB::connection('asesmen_gara')->table('jadwal_ujian')
                ->where('tanggal_ujian', DB::raw('CURDATE()'))
                ->count();

            return response()->json([
                'success' => true,
                'jadwals' => $jadwals,
                'distribusi' => $distribusi,
                'grade_lock' => $lockStatus,
                'summary' => [
                    'total_jadwal' => $totalJadwal,
                    'total_peserta' => $totalPeserta,
                    'rata_global' => round($rataGlobal ?? 0, 1),
                    'ujian_hari_ini' => $ujianHariIni,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleLock(Request $request)
    {
        $action = $request->input('action');

        if (!in_array($action, ['lock', 'unlock'])) {
            return response()->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        try {
            $newLock = ($action === 'lock') ? 1 : 0;
            $lockDate = $newLock ? now() : null;

            DB::connection('asesmen_gara')->table('asesmen_config')
                ->where('id_config', 1)
                ->update([
                    'grade_lock' => $newLock,
                    'grade_lock_date' => $lockDate,
                ]);

            $actionLabel = $newLock
                ? 'Mengunci Nilai Semester (Grade Lock AKTIF)'
                : 'Membuka Kunci Nilai Semester (Grade Lock DINONAKTIFKAN)';

            DB::connection('mysql_auth')->table('audit_logs')->insert([
                'user_id' => auth()->id() ?? 0,
                'user_type' => 'kepsek',
                'action' => $actionLabel,
                'module' => 'grade_lock',
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'grade_lock' => $newLock,
                'grade_lock_date' => $lockDate,
                'message' => $newLock
                    ? 'Nilai semester berhasil dikunci.'
                    : 'Kunci nilai berhasil dibuka.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    


    public function approveSoal(Request $request, $id)
    {
        try {
            $updated = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('id', $id)
                ->update(['status_soal' => 'VALIDATED', 'updated_at' => now()]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan.'], 404);
            }

            
            DB::connection('mysql_auth')->table('audit_logs')->insert([
                'user_id'    => auth()->id(),
                'user_type'  => 'kepsek',
                'action'     => 'APPROVE_BANK_SOAL',
                'module'     => 'bank_soal_qc',
                'detail'     => json_encode(['soal_id' => $id, 'status' => 'VALIDATED']),
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Soal berhasil disetujui dan divalidasi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    


    public function rejectSoal(Request $request, $id)
    {
        $request->validate(['alasan' => 'nullable|string|max:500']);

        try {
            $updated = DB::connection('asesmen_gara')->table('bank_soal')
                ->where('id', $id)
                ->update(['status_soal' => 'DRAFT', 'updated_at' => now()]);

            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan.'], 404);
            }

            
            DB::connection('mysql_auth')->table('audit_logs')->insert([
                'user_id'    => auth()->id(),
                'user_type'  => 'kepsek',
                'action'     => 'REJECT_BANK_SOAL',
                'module'     => 'bank_soal_qc',
                'detail'     => json_encode(['soal_id' => $id, 'alasan' => $request->alasan, 'status' => 'DRAFT']),
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Soal dikembalikan ke status Draft.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
